<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EjnApiService
{
    private string $baseUrl = 'https://open.ejn.gov.ba';
    private string $lang    = 'bs-latn-ba';

    private function get(string $endpoint, array $params = []): array
    {
        $params['ietfTag'] = $this->lang;
        $params = array_filter($params, fn($v) => $v !== null && $v !== '');

        $cacheKey = 'ejn_' . md5($endpoint . serialize($params));

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($endpoint, $params) {
            $url = $this->baseUrl . $endpoint;

            Log::info('EJN API poziv', ['endpoint' => $endpoint, 'params' => $params]);

            try {
                $response = Http::timeout(15)
                    ->withoutVerifying()
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get($url, $params);

                if ($response->successful()) {
                    $data = $response->json()['value'] ?? [];
                    Log::info('EJN API uspješno', ['endpoint' => $endpoint, 'count' => count($data)]);
                    return $data;
                }

                Log::warning('EJN API neuspješan odgovor', [
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'razlog'   => $response->reason(),
                    'body'     => $response->body(),
                ]);
            } catch (\Exception $e) {
                Log::error('EJN API greška', [
                    'endpoint' => $endpoint,
                    'poruka'   => $e->getMessage(),
                    'file'     => $e->getFile(),
                    'line'     => $e->getLine(),
                ]);
            }

            return [];
        });
    }

    private function buildSearchFilter(string $search, array $fields): string
    {
        return '(' . collect($fields)
            ->map(fn($f) => "contains(tolower({$f}),'" . strtolower(addslashes($search)) . "')")
            ->join(' or ') . ')';
    }

    // Aktivni/otvoreni tenderi
    public function getProcedures(array $cpvCodes = [], string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = ["Status eq 'Announced'"];

        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['Name', 'ContractingAuthorityName']);
        }

        return $this->get('/Procedures', [
            '$filter'  => implode(' and ', $filters),
            '$orderby' => 'Announced desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Dodjele — ko je dobio, po kojim cijenama
    public function getAwards(array $cpvCodes = [], string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];

        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['ProcedureName', 'ContractingAuthorityName']);
        }

        return $this->get('/Awards', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'ContractDate desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Lotovi uz procedure — imaju rokove
    public function getLots(int $procedureId): array
    {
        return $this->get('/Lots', [
            '$filter' => "ProcedureId eq {$procedureId}",
        ]);
    }

    // Planovi nabavki s CPV kodom
    public function getPlannedProcurements(array $cpvCodes = [], string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = ['IsAbandoned eq false'];

        if (!empty($cpvCodes)) {
            $cpvFilters = collect($cpvCodes)
                ->map(fn($code) => "startswith(MainCpvCodeName,'" . substr($code, 0, 2) . "')")
                ->join(' or ');
            $filters[] = "({$cpvFilters})";
        }

        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['Name', 'ContractingAuthorityName']);
        }

        return $this->get('/PlannedProcurements', [
            '$filter'  => implode(' and ', $filters),
            '$orderby' => 'EstimatedProcedureStartDate desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // PI Notices — najave
    public function getPiNotices(array $cpvCodes = [], string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];

        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['Name', 'ContractingAuthorityName']);
        }

        return $this->get('/PiNotices', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'Announced desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Award Notices — obavijesti o dodjeli
    public function getAwardNotices(array $cpvCodes = [], string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];

        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['ProcedureName', 'ContractingAuthorityName']);
        }

        return $this->get('/AwardNotices', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'Announced desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Godišnji planovi nabavki
    public function getAnnualNotices(string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = ['IsLatestVersion eq true'];

        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['AnnualNoticeName', 'ContractingAuthorityName']);
        }

        return $this->get('/AnnouncementAnnualNotices', [
            '$filter'  => implode(' and ', $filters),
            '$orderby' => 'Announced desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Odluke o dodjeli ugovora
    public function getDecisions(string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];

        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['ContractingAuthorityName']);
        }

        return $this->get('/AnnouncementDecisions', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'Announced desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // NPS nabavke (van javnog sektora)
    public function getNpsProcurements(string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = ['IsLatestVersion eq true'];

        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['NpsProcurementName', 'ContractingAuthorityName']);
        }

        return $this->get('/AnnouncementNpsProcurements', [
            '$filter'  => implode(' and ', $filters),
            '$orderby' => 'Announced desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Dobavljači — pretraga kompanija po imenu/djelatnosti
    public function getSuppliers(string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];
        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['Name', 'ShortDescription']);
        }
        return $this->get('/Suppliers', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'Name asc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // E-aukcije
    public function getAuctions(string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];
        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['ProcedureName', 'LotName', 'ContractingAuthorityName']);
        }
        return $this->get('/Auctions', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'ScheduledAt desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Poništene procedure
    public function getTerminations(string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];
        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['ProcedureName', 'ContractingAuthorityName']);
        }
        return $this->get('/Terminations', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'DecisionDate desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Izuzeci od primjene zakona
    public function getExemptions(string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];
        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['MainProcurementName', 'ContractingAuthorityName']);
        }
        return $this->get('/ExemptionNotices', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'ContractDate desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    // Pozivi za nadmetanje (ograničeni postupak)
    public function getBiddingInvitations(string $search = '', int $top = 15, int $skip = 0): array
    {
        $filters = [];
        if ($search) {
            $filters[] = $this->buildSearchFilter($search, ['ProcedureName', 'ContractingAuthorityName']);
        }
        return $this->get('/BiddingInvitations', [
            '$filter'  => implode(' and ', $filters) ?: null,
            '$orderby' => 'Announced desc',
            '$top'     => $top,
            '$skip'    => $skip,
        ]);
    }

    public function clearCache(): void
    {
        Cache::flush();
    }
}
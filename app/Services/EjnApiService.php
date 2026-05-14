<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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
            try {
                $response = Http::timeout(15)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get($this->baseUrl . $endpoint, $params);

                if ($response->successful()) {
                    return $response->json()['value'] ?? [];
                }
            } catch (\Exception $e) {
                \Log::error('EJN API error: ' . $e->getMessage());
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

    public function clearCache(): void
    {
        Cache::flush();
    }
}
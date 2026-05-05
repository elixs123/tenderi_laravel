<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\CpvCode;

#[Signature('app:sync-cpv-codes')]
#[Description('Command description')]
class SyncCpvCodes extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $skip = 0;
        $totalSynced = 0;
        $hasMore = true;

        $this->info("🚀 Započinjem kompletnu sinhronizaciju CPV kodova...");

        while ($hasMore) {
            $url = "https://open.ejn.gov.ba/CpvCodes?\$skip={$skip}&\$orderby=Id desc";
            
            $this->comment("Pokušavam skinuti paket od skip-a: {$skip}...");

            try {
                $response = Http::timeout(30)->get($url);

                if ($response->failed()) {
                    $this->error("❌ Greška pri spajanju na API.");
                    break;
                }

                $cpvData = $response->json('value');

                if (empty($cpvData)) {
                    $hasMore = false;
                    $this->info("✅ Nema više podataka za preuzimanje.");
                    break;
                }

                foreach ($cpvData as $item) {
                    DB::table('cpvcodes')->updateOrInsert(
                        ['id' => $item['Id']],
                        [
                            'code' => $item['Code'],
                            'description' => $item['Description'],
                            'root_id' => $item['RootId'],
                            'root_code' => $item['RootCode'],
                            'root_description' => $item['RootDescription'],
                            'last_updated' => $item['LastUpdated'] ?? now(),
                        ]
                    );
                }

                $totalSynced += count($cpvData);
                $this->line("Spremljeno: <info>{$totalSynced}</info> kodova...");

                $skip += 50;

                if ($skip > 20000) {
                    $this->warn("Dosegnut limit od 20.000 kodova.");
                    break;
                }

            } catch (\Exception $e) {
                $this->error("❌ Greška tokom synca: " . $e->getMessage());
                return 1;
            }
        }

        $this->info("🏁 Sinhronizacija završena. Ukupno: {$totalSynced} kodova.");
        return 0;
    }
}

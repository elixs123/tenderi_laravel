<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncSuppliersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenders:sync-suppliers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preuzima i sinhronizuje bazu svih dobavljača sa open.ejn.gov.ba API-ja';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Započinjem sinhronizaciju dobavljača sa EJN...");

        $url = 'https://open.ejn.gov.ba/SuppliersBase';
        $top = 1000; // Vučemo po 1000 komada odjednom
        $skip = 0;
        $totalSynced = 0;

        // Kreiramo progress bar u terminalu (čisto vizuelno, da vidimo da radi)
        $this->output->progressStart(0);

        while (true) {
            try {
                // Gađamo API sa paginacijom
                $response = Http::withoutVerifying()->timeout(30)->get($url, [
                    '$top' => $top,
                    '$skip' => $skip,
                    '$orderby' => 'Id asc' // Sortiramo po ID-u da paginacija bude stabilna
                ]);

                if (!$response->successful()) {
                    $this->error("\nGreška pri komunikaciji sa API-jem na skip: {$skip}. Status: " . $response->status());
                    break;
                }

                $data = $response->json('value') ?? [];

                // Ako nema više podataka, stigli smo do kraja baze
                if (empty($data)) {
                    $this->info("\nKraj! Svi podaci su povučeni.");
                    break;
                }

                $insertData = [];
                $now = now();

                foreach ($data as $item) {
                    $insertData[] = [
                        'supplier_id'      => $item['Id'],
                        'name'             => \Illuminate\Support\Str::upper($item['Name'] ?? 'NEPOZNAT NAZIV'),
                        'activity_type'    => $item['ActivityTypeName'] ?? null,
                        'is_main'          => true, // Default po tvojoj migraciji
                        'last_updated_api' => isset($item['LastUpdated']) ? Carbon::parse($item['LastUpdated'])->toDateTimeString() : null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }

                // Chunkamo insert na 500 da ne preopteretimo MySQL memoriju
                $chunks = array_chunk($insertData, 500);
                foreach ($chunks as $chunk) {
                    // Upsert: INSERT ako ne postoji (gleda supplier_id), UPDATE ako postoji
                    DB::table('suppliers')->upsert(
                        $chunk,
                        ['supplier_id'], // Kolona po kojoj prepoznaje duplikat (MORA biti UNIQUE u migraciji, što kod tebe jeste)
                        ['name', 'activity_type', 'last_updated_api', 'updated_at'] // Šta ažurirati ako dobavljač već postoji
                    );
                }

                $syncedInThisBatch = count($data);
                $totalSynced += $syncedInThisBatch;
                $skip += $top;

                // Pomjeramo progress bar naprijed
                $this->output->progressAdvance($syncedInThisBatch);

                // Ako nam je API vratio manje od onog što smo tražili, znači da je to zadnja stranica
                if ($syncedInThisBatch < $top) {
                    $this->info("\nDošli smo do zadnje stranice.");
                    break;
                }

            } catch (\Exception $e) {
                $this->error("\nDošlo je do greške: " . $e->getMessage());
                break;
            }
        }

        $this->output->progressFinish();
        $this->info("Sinhronizacija završena! Ukupno obrađeno: {$totalSynced} dobavljača.");
    }
}
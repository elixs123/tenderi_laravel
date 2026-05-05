<?php

namespace App\Livewire\User;

use App\Models\TenderWorkflow;
use App\Models\TenderTask;
use App\Models\AiResponseCache;
use App\Models\ArticleMapping;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Barryvdh\DomPDF\Facade\Pdf;

class TenderProgress extends Component
{
    use WithFileUploads;

    public $wf; 
    public $tenderFile;
    public $pdfFile; 
    public $parsedData = null; 
    
    public $nabavne = [];
    public $ponudbene = [];
    
    public $artikli_nabavne = [];
    public $artikli_ponudbene = [];

    public $progress = 0;

    public $purchasePrices = [];
    public $offerPrices = [];

    public $taskFiles = [];

    public $newTaskName = '';

    public array $sudjelujem = [];

    public array $lotPurchasePrices = [];
    public array $lotOfferPrices = [];

    public function mount($id)
    {
        $this->wf = TenderWorkflow::with('tasks')->findOrFail($id);

        if (!empty($this->wf->ai_parsed_data)) {
            $this->parsedData = $this->wf->ai_parsed_data;
            $this->initArrays();
        }
    }

    public function addCustomTask()
    {
        $this->validate(['newTaskName' => 'required|string|min:3']);

        TenderTask::create([
            'tender_workflow_id' => $this->wf->id,
            'naziv' => trim($this->newTaskName),
            'status' => 'nedostaje'
        ]);

        $this->newTaskName = '';
        $this->wf->load('tasks');
        $this->checkAndProcessStatus();
        
        $this->dispatch('notify', ['type' => 'success', 'message' => "Dodano novo polje za dokument!"]);
    }

    public function deleteTask($taskId)
    {
        $task = TenderTask::find($taskId);
        if ($task) {
            $task->delete();
            $this->wf->load('tasks');
            $this->checkAndProcessStatus();
            $this->dispatch('notify', ['type' => 'success', 'message' => "Zahtjev za dokumentom je obrisan."]);
        }
    }

    public function removeUploadedFile($taskId)
    {
        $task = TenderTask::find($taskId);
        if ($task && $task->status === 'pribavljeno') {
            $task->update([
                'status' => 'nedostaje',
                'file_name' => null,
                'file_path' => null,
                'completed_at' => null,
            ]);
            $this->wf->load('tasks');
            $this->checkAndProcessStatus();
        }
    }

    private function checkAndProcessStatus()
    {
        $total = $this->wf->tasks->count();
        $completed = $this->wf->tasks->where('status', 'pribavljeno')->count();

        if ($total > 0 && $total === $completed) {
            if (!in_array($this->wf->status, ['offer_submitted', 'completed'])) {
                $this->wf->update(['status' => 'documentation_uploaded']);
            }
        } else {
            if ($this->wf->status === 'documentation_uploaded') {
                $this->wf->update(['status' => 'accepted']);
            }
        }
        $this->wf->refresh();
    }

    public function toggleLot($index, $lotBroj)
    {
        $this->sudjelujem[$index] = !($this->sudjelujem[$index] ?? false);

        if ($this->wf) {
            $activeIndexes = array_keys(array_filter($this->sudjelujem));
            
            $this->wf->update([
                'accepted_lots' => $activeIndexes
            ]);
        }
    }

    public function generisiKatalogPdf()
    {
        ini_set('max_execution_time', 300); 
        set_time_limit(300);
        $katalogArtikli = [];

        $dodajArtikal = function($art) use (&$katalogArtikli) {
            if (isset($art['ai_match']['selected'])) {
                $ident = $art['ai_match']['selected']['acIdent'];
                $kol = max(1, floatval($art['kolicina'] ?? 1));
                
                // Pošto u bazu spašavamo UKUPNU ponudbenu cijenu za tu količinu,
                // za katalog nam treba POJEDINAČNA cijena (pa dijelimo sa količinom).
                $cijenaBroj = isset($art['ai_match']['ponudbena_cijena']) && $art['ai_match']['ponudbena_cijena'] > 0 
                              ? ($art['ai_match']['ponudbena_cijena'] / $kol) 
                              : ($art['ai_match']['selected']['anRTPrice'] ?? 0);
                              
                $katalogArtikli[] = [
                    'tender_opis' => $art['opis'],
                    'kolicina' => $art['kolicina'] . ' ' . $art['jm'],
                    'naziv' => $art['ai_match']['selected']['acName'],
                    'ident' => $ident,
                    'cijena' => number_format((float)$cijenaBroj, 2, ',', '.') . ' KM',
                    'slika_base64' => $this->resolveImageUrl($ident)
                ];
            }
        };
        
        if (!empty($this->parsedData['artikli_generalno'])) {
            foreach ($this->parsedData['artikli_generalno'] as $art) {
                $dodajArtikal($art);
            }
        }

        if (!empty($this->parsedData['lotovi'])) {
            foreach ($this->parsedData['lotovi'] as $lot) {
                foreach ($lot['artikli'] ?? [] as $art) {
                    $dodajArtikal($art);
                }
            }
        }

        if (empty($katalogArtikli)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Nema mapiranih artikala za generisanje kataloga!']);
            return;
        }

        $pdf = Pdf::loadView('pdf.katalog-premium', [
            'artikli' => $katalogArtikli,
            'tender_id' => $this->wf->procedure_id ?? 'Nepoznato'
        ])->setPaper('a4', 'portrait')->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']); 

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'PennyPlus_Katalog_Tender_' . ($this->wf->procedure_id ?? 'Export') . '.pdf');
    }

    private function getDefaultImage()
    {
        $localPath = public_path('images/penny-logo.png');
        
        if (file_exists($localPath)) {
            $type = pathinfo($localPath, PATHINFO_EXTENSION);
            $data = file_get_contents($localPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mMs/wQAAf8BQPw2fVAAAAAASUVORK5CYII=';
    }

    private function resolveImageUrl($ident)
    {
        $ident = trim($ident);
        $url = "https://pennyshop.ba/assets/photos/product/medium/{$ident}.jpg";
        
        try {
            $response = Http::timeout(2)->head($url);
            if ($response->successful()) {
                return $url; 
            }
        } catch (\Exception $e) {
        }

        return $this->getDefaultImage();
    }

    public function spasiUBazu()
    {
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        if (!in_array($this->wf->status, ['offer_submitted', 'completed'])) {
            $this->wf->update(['status' => 'documentation_uploaded']); 
        }

        if (!empty($this->parsedData['artikli_generalno'])) {
            foreach ($this->parsedData['artikli_generalno'] as $index => &$art) {
                if (isset($this->offerPrices[$index])) {
                    $art['ai_match']['ponudbena_cijena'] = floatval($this->offerPrices[$index]);
                }
            }
            unset($art);
        }

        if (!empty($this->parsedData['lotovi'])) {
            foreach ($this->parsedData['lotovi'] as $index => &$lot) {
                if (isset($this->ponudbene[$index])) {
                    $lot['ukupna_ponudbena'] = floatval($this->ponudbene[$index]);
                }

                if (isset($lot['artikli'])) {
                    foreach ($lot['artikli'] as $artIndex => &$art) {
                        if (isset($this->lotOfferPrices[$index][$artIndex])) {
                            $art['ai_match']['ponudbena_cijena'] = floatval($this->lotOfferPrices[$index][$artIndex]);
                        }
                    }
                }
                unset($art);
            }
            unset($lot);
        }

        $this->wf->update([
            'ai_parsed_data' => $this->parsedData
        ]);

        if ($this->pdfFile) {
            $fileHash = md5_file($this->pdfFile->getRealPath() ?? ''); 
            $cache = AiResponseCache::where('file_hash', $fileHash)->first();
            if ($cache) {
                $cache->update(['ai_response' => $this->parsedData]);
            }
        }

        $this->mount($this->wf->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => "Podaci su uspješno spašeni i ažurirani!"]);
    }

    public function processPdf()
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $this->validate(['pdfFile' => 'required|mimes:pdf|max:20480']);

        try {
            $this->progress = 10;
            $filePath = $this->pdfFile->getRealPath();
            $fileHash = md5_file($filePath);

            $cachedResponse = AiResponseCache::where('file_hash', $fileHash)->first();

            if ($cachedResponse) {
                $this->parsedData = $cachedResponse->ai_response;

                $this->matchArticlesWithDatabase();
                
                $this->wf->update(['ai_parsed_data' => $this->parsedData]);
                $cachedResponse->update(['ai_response' => $this->parsedData]);

                $this->initArrays();
                $this->kreirajTaskoveIzDokumentacije();
               
                $this->progress = 100;
                $this->dispatch('notify', ['type' => 'success', 'message' => "Učitano iz memorije trenutačno!"]);
                return; 
            }

            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            
            $text = "";
            foreach ($pdf->getPages() as $page) {
                $text .= $page->getText() . "\n";
            }

            if (empty(trim($text))) {
                throw new \Exception("PDF nema čitljiv tekst (potreban OCR).");
            }

            $this->progress = 30;

            $isLargeFile = (count($pdf->getPages()) > 15 || strlen($text) > 40000);
            $selectedModel = $isLargeFile ? 'gpt-4o' : 'gpt-4o-mini';
            $maxTokens = $isLargeFile ? 10000 : 4000;

            $prompt = "Analiziraj tender za Penny Plus d.o.o. Vrati ISKLJUČIVO JSON objekat.
            STRUKTURA:
            {
            \"ugovorni_organ\": \"\",
            \"is_lotovi\": false,
            \"artikli_generalno\": [ {\"opis\": \"\", \"jm\": \"\", \"kolicina\": 0} ],
            \"lotovi\": [
                {
                \"broj\": \"\",
                \"naziv\": \"\",
                \"vrijednost\": 0,
                \"artikli\": [ {\"opis\": \"\", \"jm\": \"\", \"kolicina\": 0} ]
                }
            ],
            \"potrebni_dokumenti\": [
                {\"naziv\": \"\"}
            ],
            \"ai_uprava\": { \"sazetak\": \"\", \"rizik_nivo\": \"NIZAK|SREDNJI|VISOK\", \"rizik_razlog\": \"\" }
            }
            PRAVILA: 
            1. SVE stavke iz tabela moraju biti izvučene bez preskakanja.
            2. U niz 'potrebni_dokumenti' OBAVEZNO izvuci SAMO dokumente koji čine sekciju 'Obavezan sadržaj ponude' i koje ponuđač mora dostaviti ODMAH u koverti. 
            3. STROGO ZABRANJENO: Ne smiješ u 'potrebni_dokumenti' ubacivati naknadna uvjerenja (iz Poreske uprave, PIO/MIO, Sudova) koja se spominju u tenderu.";

            $response = Http::timeout(240)
                ->withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $selectedModel,
                    'messages' => [['role' => 'user', 'content' => $prompt . "\n\nTEKST:\n" . str()->limit($text, 80000)]],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => $maxTokens,
                    'temperature' => 0
                ]);

            if ($response->successful()) {
                
                $raw = $response->json('choices.0.message.content');
                $this->parsedData = json_decode($raw, true);

                if ($this->parsedData === null) {
                    throw new \Exception("AI je vratio nekompletan JSON.");
                }

                $this->kreirajTaskoveIzDokumentacije();
                $this->progress = 70;
                
                $this->matchArticlesWithDatabase();

                AiResponseCache::create([
                    'file_hash' => $fileHash,
                    'ai_response' => $this->parsedData
                ]);

                $this->wf->update(['ai_parsed_data' => $this->parsedData]);
                
                $this->initArrays();

                $this->progress = 100;
                $this->dispatch('notify', ['type' => 'success', 'message' => "Analiza i mapiranje završeno!"]);
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => "OpenAI Error: " . $response->body()]);
            }

        } catch (\Exception $e) {
            $this->progress = 0;
            Log::error("Glavna greška: " . $e->getMessage());
            session()->flash('error', 'Greška pri obradi: ' . $e->getMessage());
        }
    }


    private function kreirajTaskoveIzDokumentacije()
    {
        if (!empty($this->parsedData['potrebni_dokumenti']) && is_array($this->parsedData['potrebni_dokumenti'])) {
            foreach ($this->parsedData['potrebni_dokumenti'] as $dokument) {
                $naziv = is_array($dokument) ? ($dokument['naziv'] ?? '') : (string) $dokument;
                $naziv = trim($naziv);
                if (empty($naziv)) continue;
                $naziv_skraceno = \Illuminate\Support\Str::limit($naziv, 250, '...');

                try {
                    TenderTask::firstOrCreate([
                        'tender_workflow_id' => $this->wf->id, 
                        'naziv' => $naziv_skraceno
                    ], [
                        'status' => 'nedostaje'
                    ]);
                } catch (\Exception $e) {
                    Log::error("Greška pri upisu taska: " . $e->getMessage());
                }
            }
            $this->wf->load('tasks'); 
        }
    }

    private function matchArticlesWithDatabase()
    {
        $allDescriptions = [];
        if (!empty($this->parsedData['artikli_generalno'])) {
            foreach($this->parsedData['artikli_generalno'] as $art) $allDescriptions[] = $art['opis'];
        }
        if (!empty($this->parsedData['lotovi'])) {
            foreach ($this->parsedData['lotovi'] as $lot) {
                if (isset($lot['artikli']) && is_array($lot['artikli'])) {
                    foreach ($lot['artikli'] as $art) {
                        $allDescriptions[] = $art['opis']; 
                    }
                }
            }
        }
    
        $allDescriptions = array_values(array_unique($allDescriptions));

        try {
            $response = Http::timeout(60)->post('http://172.16.199.43:5005/api/test', [
                'descriptions' => $allDescriptions
            ]);

            if ($response->successful()) {
                $batchData = $response->json();

                if (!empty($this->parsedData['artikli_generalno'])) {
                    foreach ($this->parsedData['artikli_generalno'] as $index => &$art) {
                        $art['ai_match'] = $this->calculateBestMatch($art['opis'], $batchData[$art['opis']] ?? []);
                    }
                    unset($art); 
                }

                if (!empty($this->parsedData['lotovi'])) {
                    foreach ($this->parsedData['lotovi'] as $lotIndex => &$lot) {
                        if (isset($lot['artikli']) && is_array($lot['artikli'])) {
                            foreach ($lot['artikli'] as $artIndex => &$art) {
                                $art['ai_match'] = $this->calculateBestMatch($art['opis'], $batchData[$art['opis']] ?? []);
                            }
                            unset($art); 
                        }
                    }
                    unset($lot);
                }
            } else {
               $this->dispatch('notify', ['type' => 'error', 'message' => "Greška pri povezivanju sa AI servisom."]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => "Greška: " . $e->getMessage()]);
        }
    }

    private function calculateBestMatch($tenderDesc, $dbItems)
    {
        $suggestions = [];
        $tenderDescLower = strtolower(trim($tenderDesc));
        $tenderWords = explode(' ', $tenderDescLower);

        if (!empty($dbItems)) {
            foreach ($dbItems as $item) {
                $dbName = trim($item['acName'] ?? '');
                if (empty($dbName)) continue;

                similar_text($tenderDescLower, strtolower($dbName), $percent);
                if (isset($tenderWords[0]) && str_contains(strtolower($dbName), $tenderWords[0])) {
                    $percent += 15;
                }

                $anRTPrice = isset($item['anRTPrice']) ? round(floatval($item['anRTPrice']), 2) : 0;
                $anPrice = isset($item['anPrice']) ? round(floatval($item['anPrice']), 2) : 0; // DODAN anPrice

                $suggestions[] = [
                    'acIdent' => $item['acIdent'] ?? uniqid(), 
                    'acName' => $dbName,
                    'percent' => round(min(100, $percent), 1),
                    'anRTPrice' => $anRTPrice,
                    'anPrice' => $anPrice,
                    'stock_total' => $item['stock_total'] ?? 0,       
                    'stock_details' => $item['stock_details'] ?? []  
                ];
            }
            usort($suggestions, fn($a, $b) => $b['percent'] <=> $a['percent']);
        }

        $topSuggestions = array_slice($suggestions, 0, 5);
        $learned = \App\Models\ArticleMapping::where('tender_description', trim($tenderDesc))->first();
        
        if ($learned) {
            $price = 0;
            $basePrice = 0; // DODAN anPrice
            $stockTotal = 0;      
            $stockDetails = [];   

            if (!empty($dbItems)) {
                foreach ($dbItems as $item) {
                    if (isset($item['acIdent']) && $item['acIdent'] === $learned->acIdent) {
                        $price = $item['anRTPrice'] ?? 0;
                        $basePrice = $item['anPrice'] ?? 0;
                        $stockTotal = $item['stock_total'] ?? 0;      
                        $stockDetails = $item['stock_details'] ?? []; 
                        break;
                    }
                }
            }

            if ($price == 0) {
                try {
                    $response = Http::timeout(5)->get('http://172.16.199.43:5005/api/test', [
                        'ident' => $learned->acIdent
                    ]);
                    if ($response->successful() && !empty($response->json())) {
                        $apiData = $response->json()[0]; 
                        $price = $apiData['anRTPrice'] ?? 0;
                        $basePrice = $apiData['anPrice'] ?? 0;
                        $stockTotal = $apiData['stock_total'] ?? 0;      
                        $stockDetails = $apiData['stock_details'] ?? []; 
                    }
                } catch (\Exception $e) {
                    Log::error("API Price Fetch Error: " . $e->getMessage());
                }
            }

            return [
                'selected' => [
                    'acIdent' => $learned->acIdent, 
                    'acName' => $learned->acName, 
                    'percent' => 100,
                    'anRTPrice' => round(floatval($price), 2),
                    'anPrice' => round(floatval($basePrice), 2),
                    'stock_total' => floatval($stockTotal),  
                    'stock_details' => $stockDetails         
                ],
                'suggestions' => $topSuggestions, 
                'is_manual' => true, 
                'is_learned' => true
            ];
        }

        if(empty($topSuggestions)) {
            return [
                'selected' => null,
                'suggestions' => [], 
                'is_manual' => false, 
                'is_learned' => false
            ];
        }

        return [
            'selected' => $topSuggestions[0] ?? null,
            'suggestions' => $topSuggestions, 
            'is_manual' => false, 
            'is_learned' => false
        ];
    }

    private function refreshLivePricesAndStock()
    {
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        $identsToFetch = [];

        if (!empty($this->parsedData['artikli_generalno'])) {
            foreach ($this->parsedData['artikli_generalno'] as $art) {
                if (isset($art['ai_match']['selected']['acIdent'])) {
                    $identsToFetch[] = $art['ai_match']['selected']['acIdent'];
                }
            }
        }
        
        if (!empty($this->parsedData['lotovi'])) {
            foreach ($this->parsedData['lotovi'] as $lot) {
                if (isset($lot['artikli']) && is_array($lot['artikli'])) {
                    foreach ($lot['artikli'] as $art) {
                        if (isset($art['ai_match']['selected']['acIdent'])) {
                            $identsToFetch[] = $art['ai_match']['selected']['acIdent'];
                        }
                    }
                }
            }
        }

        $identsToFetch = array_unique(array_filter($identsToFetch));

        if (empty($identsToFetch)) return;

        try {
            $response = Http::timeout(10)->post('http://172.16.199.43:5005/api/test', [
                'idents' => array_values($identsToFetch)
            ]);

            if ($response->successful()) {
                $liveData = $response->json();

                if (!empty($this->parsedData['artikli_generalno'])) {
                    foreach ($this->parsedData['artikli_generalno'] as &$art) {
                        $ident = $art['ai_match']['selected']['acIdent'] ?? null;
                        if ($ident && isset($liveData[$ident])) {
                            $art['ai_match']['selected']['anRTPrice'] = round(floatval($liveData[$ident]['anRTPrice'] ?? 0), 2);
                            $art['ai_match']['selected']['anPrice'] = round(floatval($liveData[$ident]['anPrice'] ?? 0), 2);
                            $art['ai_match']['selected']['stock_total'] = floatval($liveData[$ident]['stock_total'] ?? 0);
                            $art['ai_match']['selected']['stock_details'] = $liveData[$ident]['stock_details'] ?? [];
                        }
                    }
                    unset($art);
                }

                if (!empty($this->parsedData['lotovi'])) {
                    foreach ($this->parsedData['lotovi'] as &$lot) {
                        if (isset($lot['artikli']) && is_array($lot['artikli'])) {
                            foreach ($lot['artikli'] as &$art) {
                                $ident = $art['ai_match']['selected']['acIdent'] ?? null;
                                if ($ident && isset($liveData[$ident])) {
                                    $art['ai_match']['selected']['anRTPrice'] = round(floatval($liveData[$ident]['anRTPrice'] ?? 0), 2);
                                    $art['ai_match']['selected']['anPrice'] = round(floatval($liveData[$ident]['anPrice'] ?? 0), 2);
                                    $art['ai_match']['selected']['stock_total'] = floatval($liveData[$ident]['stock_total'] ?? 0);
                                    $art['ai_match']['selected']['stock_details'] = $liveData[$ident]['stock_details'] ?? [];
                                }
                            }
                            unset($art);
                        }
                    }
                    unset($lot);
                }
            }
        } catch (\Exception $e) {
            Log::error("Live Price/Stock Refresh Error: " . $e->getMessage());
        }
    }

    public function searchManual($query)
    {
        if (strlen($query) < 3) return [];
        try {
            $response = Http::timeout(5)->get('http://172.16.199.43:5005/api/test', ['q' => $query]);
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("Manual Search Error: " . $e->getMessage());
        }
        return [];
    }

    public function updateArticleMatch($type, $index1, $index2, $ident, $name, $percent, $tenderDesc, $anRTPrice = 0, $stockTotal = 0, $stockDetails = [], $anPrice = 0)
    {
        if (is_string($stockDetails)) {
            $stockDetails = json_decode($stockDetails, true) ?? [];
        }

        $newMatch = [
            'acIdent' => $ident, 
            'acName' => $name, 
            'percent' => $percent, 
            'anRTPrice' => round(floatval($anRTPrice), 2),
            'anPrice' => round(floatval($anPrice), 2), // SPAŠAVAMO ANPRICE
            'stock_total' => floatval($stockTotal),
            'stock_details' => $stockDetails
        ];

        if ($type === 'general') {
            $this->parsedData['artikli_generalno'][$index1]['ai_match']['selected'] = $newMatch;
            $this->parsedData['artikli_generalno'][$index1]['ai_match']['is_manual'] = true;
            
            $kolicina = floatval($this->parsedData['artikli_generalno'][$index1]['kolicina'] ?? 0);
            $this->purchasePrices[$index1] = round(floatval($anPrice) * $kolicina, 2);
            
        } else {
            $this->parsedData['lotovi'][$index1]['artikli'][$index2]['ai_match']['selected'] = $newMatch;
            $this->parsedData['lotovi'][$index1]['artikli'][$index2]['ai_match']['is_manual'] = true;
            
            $kolicina = floatval($this->parsedData['lotovi'][$index1]['artikli'][$index2]['kolicina'] ?? 0);
            $this->lotPurchasePrices[$index1][$index2] = round(floatval($anPrice) * $kolicina, 2);
            
            $ukupnaNabavnaLota = 0;
            foreach ($this->parsedData['lotovi'][$index1]['artikli'] as $artIdx => $art) {
                $kol = floatval($art['kolicina'] ?? 0);
                $pojedinacnaNabavna = floatval($art['ai_match']['selected']['anPrice'] ?? 0);
                $ukupnaNabavnaLota += ($pojedinacnaNabavna * $kol);
            }
            $this->nabavne[$index1] = round($ukupnaNabavnaLota, 2);
        }

        ArticleMapping::updateOrCreate(
            ['tender_description' => trim($tenderDesc)], 
            ['acIdent' => $ident, 'acName' => $name]     
        );

        $this->updateCache();
        $this->dispatch('notify', ['type' => 'success', 'message' => "Sistem je zapamtio mapiranje!"]);
    }

    private function updateCache()
    {
       if ($this->pdfFile) {
            $fileHash = md5_file($this->pdfFile->getRealPath() ?? ''); 
            $cache = AiResponseCache::where('file_hash', $fileHash)->first();
            if ($cache) {
                $cache->update(['ai_response' => $this->parsedData]);
            }
        }

        $this->wf->update(['ai_parsed_data' => $this->parsedData]);
    }

    private function initArrays()
    {
        $this->refreshLivePricesAndStock();

        $this->nabavne = []; 
        $this->ponudbene = []; 
        $this->sudjelujem = [];
        $this->lotPurchasePrices = [];
        $this->lotOfferPrices = [];
        $this->purchasePrices = []; 
        $this->offerPrices = [];   

        if (!empty($this->parsedData['lotovi'])) {
            foreach ($this->parsedData['lotovi'] as $i => $lot) {
                $this->sudjelujem[$i] = false;
                $ukupnaNabavnaLota = 0;
                $this->ponudbene[$i] = isset($lot['ukupna_ponudbena']) ? floatval($lot['ukupna_ponudbena']) : 0;

                if (isset($lot['artikli']) && is_array($lot['artikli'])) {
                    foreach ($lot['artikli'] as $artIndex => $art) {
                        
                        $pojedinacnaNabavna = round(floatval($art['ai_match']['selected']['anPrice'] ?? 0), 2);
                        $pojedinacnaProdajna = round(floatval($art['ai_match']['selected']['anRTPrice'] ?? 0), 2);
                        $kolicina = floatval($art['kolicina'] ?? 0); 

                        // Ukupna Nabavna (VPC) za stavku
                        $ukupnoNabavnaStavka = round($pojedinacnaNabavna * $kolicina, 2);
                        // Ukupna Defaultna Ponudbena (Prodajna) za stavku
                        $ukupnoProdajnaStavka = round($pojedinacnaProdajna * $kolicina, 2);

                        $this->lotPurchasePrices[$i][$artIndex] = $ukupnoNabavnaStavka;
                        
                        // Povlačimo ručni unos, a ako ga nema, stavljamo sistemsku prodajnu cijenu
                        $this->lotOfferPrices[$i][$artIndex] = isset($art['ai_match']['ponudbena_cijena']) && $art['ai_match']['ponudbena_cijena'] > 0 
                            ? floatval($art['ai_match']['ponudbena_cijena']) 
                            : $ukupnoProdajnaStavka;

                        $ukupnaNabavnaLota += $ukupnoNabavnaStavka;
                    }
                }

                $this->nabavne[$i] = round($ukupnaNabavnaLota, 2);
            }
        }

        if (!empty($this->wf->accepted_lots)) {
            foreach ($this->wf->accepted_lots as $lotIndex) {
                $this->sudjelujem[$lotIndex] = true;
            }
        }

        if (!empty($this->parsedData['artikli_generalno'])) {
            foreach ($this->parsedData['artikli_generalno'] as $i => $art) {
                $pojedinacnaNabavna = round(floatval($art['ai_match']['selected']['anPrice'] ?? 0), 2);
                $pojedinacnaProdajna = round(floatval($art['ai_match']['selected']['anRTPrice'] ?? 0), 2);
                $kolicina = floatval($art['kolicina'] ?? 0);

                $this->purchasePrices[$i] = round($pojedinacnaNabavna * $kolicina, 2);
                
                $this->offerPrices[$i] = isset($art['ai_match']['ponudbena_cijena']) && $art['ai_match']['ponudbena_cijena'] > 0 
                    ? floatval($art['ai_match']['ponudbena_cijena']) 
                    : round($pojedinacnaProdajna * $kolicina, 2);
            }
        }
    }

    public function updatedTaskFiles($value, $taskId)
    {
        $this->validate([
            "taskFiles.{$taskId}" => 'required|mimes:pdf|max:10240',
        ]);

        $task = TenderTask::find($taskId);
        
        if ($task && isset($this->taskFiles[$taskId])) {
            $file = $this->taskFiles[$taskId];
            $originalName = $file->getClientOriginalName();
            
            $path = $file->store('tender_docs', 'public'); 

            $task->update([
                'status' => 'pribavljeno',
                'file_name' => $originalName,
                'file_path' => $path,
                'completed_at' => now(), 
            ]);

            unset($this->taskFiles[$taskId]); 
            $this->wf->load('tasks');
            $this->checkAndProcessStatus();
            
            $this->dispatch('notify', ['type' => 'success', 'message' => "Dokument uspješno zakačen!"]);
        }
    }

    public function toggleTaskStatus($taskId)
    {
        $task = TenderTask::find($taskId);
        if ($task) {
            if ($task->status === 'pribavljeno') {
                $task->update([
                    'status' => 'nedostaje',
                    'file_name' => null,
                    'file_path' => null,
                    'completed_at' => null,
                ]);
                $this->mount($this->wf->id);
            } else {
                $this->dispatch('notify', ['type' => 'warning', 'message' => 'Kliknite na ikonicu i odaberite PDF da završite task.']);
            }
        }
    }

    public function submitOffer()
    {
        if ($this->wf) {
            $totalTasks = $this->wf->tasks->count();
            $completedTasks = $this->wf->tasks->where('status', 'pribavljeno')->count();

            if ($totalTasks > 0 && $totalTasks !== $completedTasks) {
                $this->dispatch('notify', [
                    'type' => 'error', 
                    'message' => "Zabranjeno! Nisu pribavljeni svi obavezni dokumenti iz liste."
                ]);
                return; 
            }

            $this->wf->update([
                'status' => 'offer_submitted'
            ]);
            
            $this->mount($this->wf->id); 
            $this->dispatch('notify', ['type' => 'success', 'message' => "Ponuda je uspješno zavedena kao poslana!"]);
        }
    }

    public function render()
    {
        return view('livewire.user.tender-progress')->layout('layouts.default');
    }
}
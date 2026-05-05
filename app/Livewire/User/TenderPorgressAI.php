<?php

namespace App\Livewire\User;

use App\Models\TenderWorkflow;
use App\Models\TenderTask;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class TenderProgress extends Component
{
    use WithFileUploads;

    public $wf; 
    public $tenderFile;

    public $pdfFile; 
    public $parsedData = null; 
    
    // VARIJABLE ZA LOTOVE
    public $nabavne = [];
    public $ponudbene = [];
    public $sudjelujem = []; 
    public $lotFajlovi = []; 

    // VARIJABLE ZA ARTIKLE (Kada nema lotova)
    public $artikli_nabavne = [];
    public $artikli_ponudbene = [];

    public $delayReasons = [];
    public $progress = 0;

    public function updateProgress($val) {
        $this->progress = $val;
    }

    public function getUkupnaNabavnaProperty()
    {
        return array_sum(array_map('floatval', $this->nabavne));
    }

    public function getUkupnaPonudbenaProperty()
    {
        return array_sum(array_map('floatval', $this->ponudbene));
    }

    public function getUkupnaZaradaProperty()
    {
        return $this->ukupna_ponudbena - $this->ukupna_nabavna;
    }

    public function getProsjecnaMarzaProperty()
    {
        if ($this->ukupna_ponudbena > 0) {
            return ($this->ukupna_zarada / $this->ukupna_ponudbena) * 100;
        }
        return 0;
    }

    public function mount($id)
    {
        $this->wf = TenderWorkflow::with('tasks')->findOrFail($id);
    }

    public function processPdf()
    {
        set_time_limit(300); 
        ini_set('memory_limit', '512M');

         dd("doslo");

        $this->validate([
            'pdfFile' => 'required|mimes:pdf|max:20480',
        ]);

        try {
            $this->progress = 10;

            // 1. KORISTIMO LOKALNI PARSER DA IZBJEGNEMO SKLEPOVE I UŠTEDIMO TOKENE
            $parser = new Parser();
            $pdf = $parser->parseFile($this->pdfFile->getRealPath());
            $pages = $pdf->getPages();
            
            $generalText = "";
            $tableText = "";

            // 2. PRED-FILTRIRANJE (Reže tekst za 80% i ostavlja samo bitno)
            foreach ($pages as $index => $page) {
                $pageText = $page->getText();
                
                // Prvih 5 stranica za opće podatke
                if ($index < 5) {
                    $generalText .= $pageText . "\n";
                }
                
                // Tražimo ključne riječi za tabele i lotove
                if (preg_match('/(ANEKS|OBRAZAC ZA CIJENU|LOT|Jedinica mjere|Količina|Jedinična|Red\.br|Predmet nabavke)/i', $pageText)) {
                    $tableText .= $pageText . "\n";
                }
            }

            $optimizedText = "--- OPŠTI PODACI ---\n" . $generalText . "\n--- TABELE I ARTIKLI ---\n" . $tableText;
            
            $this->progress = 40;

            $prompt = "
                Ti si AI Menadžer za javne nabavke u firmi Penny Plus d.o.o. Sarajevo. Analiziraj tekst tendera.
                Vrati SAMO validan JSON bez ikakvog dodatnog teksta prema ovoj tačnoj strukturi:
                
                {
                    \"ugovorni_organ\": \"...\",
                    \"rok_za_prijavu\": \"...\",
                    \"kontakt_osoba\": \"...\",
                    \"cpv_kod\": \"...\",
                    \"procijenjena_vrijednost\": 0,
                    \"is_lotovi\": false,
                    \"ai_uprava\": {
                        \"sazetak\": \"...\",
                        \"rizik_nivo\": \"VISOK | SREDNJI | NIZAK\",
                        \"rizik_razlog\": \"...\"
                    },
                    \"kljucni_parametri\": {
                        \"e_aukcija\": \"DA\",
                        \"kriterij\": \"...\",
                        \"mjesto_isporuke\": \"...\",
                        \"rok_placanja\": \"...\",
                        \"trajanje_ugovora\": \"...\",
                        \"podugovaranje\": \"...\"
                    },
                    \"potrebni_dokumenti\": [],
                    \"artikli_generalno\": [
                        { \"opis\": \"\", \"jm\": \"\", \"kolicina\": 0 }
                    ],
                    \"lotovi\": [
                        {
                            \"broj\": \"\",
                            \"naziv\": \"\",
                            \"vrijednost\": 0,
                            \"artikli\": [
                                { \"opis\": \"\", \"jm\": \"\", \"kolicina\": 0 }
                            ]
                        }
                    ]
                }

                Pravila:
                - Brojevi moraju biti goli float bez valute (npr. 50000.50).
                - Obavezno izvuci sve artikle iz tabela!
                - Ako tender NIJE podijeljen na lotove, stavi 'is_lotovi' na false, a sve artikle stavi u 'artikli_generalno'.
                - Ako tender JESTE podijeljen na lotove, stavi 'is_lotovi' na true, a artikle rasporedi unutar 'lotovi'.
                
                TEKST TENDERA:
                " . str()->limit($optimizedText, 45000); 

            $response = Http::timeout(180)
                ->withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini', // Vraćeno na najjeftiniji model
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 4000, 
                    'temperature' => 0.1,
                    'response_format' => ['type' => 'json_object'] 
                ]);

               

            if ($response->successful()) {

                $textResult = $response->json('choices.0.message.content');
                $this->parsedData = json_decode($textResult, true);

                if (!$this->parsedData) {
                    throw new \Exception("AI nije vratio validan JSON.");
                }

                dd([
                    'sirovi_json_od_ai' => $textResult,
                    'niz_u_php' => $this->parsedData
                ]);
                // =

                // INICIJALIZACIJA ZA LOTOVE
                if (isset($this->parsedData['lotovi']) && !empty($this->parsedData['lotovi'])) {
                    foreach ($this->parsedData['lotovi'] as $index => $lot) {
                        $this->nabavne[$index] = 0;
                        $this->ponudbene[$index] = 0;
                        $this->sudjelujem[$index] = false;
                        $this->lotFajlovi[$index] = null;
                    }
                }

                // INICIJALIZACIJA ZA ARTIKLE (KAD NEMA LOTOVA)
                if (isset($this->parsedData['artikli_generalno']) && !empty($this->parsedData['artikli_generalno'])) {
                    foreach ($this->parsedData['artikli_generalno'] as $index => $artikal) {
                        $this->artikli_nabavne[$index] = 0;
                        $this->artikli_ponudbene[$index] = 0;
                    }
                }

                // INICIJALIZACIJA DOKUMENATA
                if (!empty($this->parsedData['potrebni_dokumenti'])) {
                    $this->wf->tasks()->delete();
                    
                    foreach ($this->parsedData['potrebni_dokumenti'] as $doc) {
                        if (is_string($doc)) {
                            $naziv = $doc;
                            $kategorija = 'Opste';
                        } elseif (is_array($doc)) {
                            $naziv = $doc['naziv'] ?? 'Nepoznat dokument';
                            $kategorija = $doc['kategorija'] ?? 'Opste';
                        } else {
                            continue; 
                        }

                        $this->wf->tasks()->create([
                            'naziv' => str()->limit($naziv, 250),
                            'kategorija' => $kategorija,
                            'status' => 'na_cekanju'
                        ]);
                    }

                    $this->wf->load('tasks');
                }

                $this->progress = 100;

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'AI Analiza (Mini) zavrsena!'
                ]);


            } else {
                throw new \Exception('OpenAI greska: ' . $response->body());
            }

        } catch (\Exception $e) {
            $this->progress = 0;
            session()->flash('error', 'Greska: ' . $e->getMessage());
        }
    }

    public function toggleTaskStatus($taskId)
    {
        $task = TenderTask::findOrFail($taskId);

        if ($task->status === 'pribavljeno') {
            $task->update(['status' => 'na_cekanju', 'acquired_at' => null]);
        } else {
            $task->update([
                'status' => 'pribavljeno',
                'acquired_at' => now(),
                'razlog_kasnjenja' => null
            ]);
        }

        $this->wf->load('tasks');
    }

    public function markAsDelayed($taskId)
    {
        TenderTask::findOrFail($taskId)->update(['status' => 'kasni']);
        $this->wf->load('tasks');
    }

    public function saveDelayReason($taskId)
    {
        $reason = $this->delayReasons[$taskId] ?? '';

        if (!empty($reason)) {
            TenderTask::where('id', $taskId)->update([
                'razlog_kasnjenja' => $reason
            ]);

            unset($this->delayReasons[$taskId]);
            $this->wf->load('tasks');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Razlog zabiljezen.'
            ]);
        }
    }

    public function saveDocumentation()
    {
        $this->validate([
            'tenderFile' => 'required|file|mimes:pdf,zip,rar,doc,docx|max:20480'
        ]);

        $path = $this->tenderFile->store('tenders/' . $this->wf->id, 'public');

        $this->wf->update([
            'document_path' => $path,
            'status' => 'documentation_uploaded'
        ]);

        $this->wf->refresh();
        $this->tenderFile = null;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Glavni fajl spasen!'
        ]);
    }

    public function submitOffer()
    {
        if ($this->wf->tasks()->where('status', '!=', 'pribavljeno')->exists()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Prikupite svu dokumentaciju!'
            ]);
            return;
        }

        $this->wf->update(['status' => 'offer_submitted']);
        $this->wf->refresh();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Ponuda poslana!'
        ]);
    }

    public function finishWork()
    {
        $this->wf->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        $this->wf->refresh();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tender zavrsen!'
        ]);
    }

    public function spasiUBazu()
    {
        session()->flash('message', 'Podaci uspjesno spaseni u bazu!');
    }

    public function render()
    {
        return view('livewire.user.tender-progress')->layout('layouts.default');
    }
}
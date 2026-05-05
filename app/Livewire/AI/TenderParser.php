<?php 

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;

class TenderParser extends Component
{
    use WithFileUploads;

    public $pdfFile;
    public $parsedData = null;
    public $isParsing = false;

    public function processPdf()
    {
        $this->validate([
            'pdfFile' => 'required|mimes:pdf|max:5120', // Max 5MB
        ]);

        $this->isParsing = true;

        try {
            // 1. Čitamo sadržaj PDF-a i pretvaramo ga u Base64 da ga API razumije
            $pdfContent = base64_encode(file_get_contents($this->pdfFile->getRealPath()));

            // 2. Pravimo instrukciju (System Prompt) - OVO JE KLJUČ SVEGA!
            $prompt = "
                Ti si ekspert za javne nabavke u BiH. Analiziraj priloženi PDF dokument (Obavještenje o nabavci).
                Izvuci podatke i vrati mi ISKLJUČIVO validan JSON format, bez ikakvog dodatnog teksta ili Markdowna.
                Struktura JSON-a mora biti tačno ovakva:
                {
                    'ugovorni_organ': 'Ime ustanove',
                    'rok_za_prijavu': 'YYYY-MM-DD HH:mm:ss',
                    'procijenjena_vrijednost': broj (float),
                    'lotovi': [
                        {'broj': broj, 'naziv': 'Opis lota', 'vrijednost': broj (float)}
                    ]
                }
            ";

            // 3. Šaljemo PDF i prompt na besplatni Gemini 1.5 Flash model
            $response = Gemini::geminiProVision()->generateContent([
                $prompt,
                new Blob(
                    mimeType: MimeType::APPLICATION_PDF,
                    data: $pdfContent
                )
            ]);

            // 4. Dobijamo čisti JSON nazad i pretvaramo ga u PHP array
            $cleanJson = str_replace(['```json', '```'], '', $response->text());
            $this->parsedData = json_decode($cleanJson, true);

            // Ovdje sada možeš uraditi: Tender::create($this->parsedData);
            
            $this->isParsing = false;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Tender uspješno parsiran!']);

        } catch (\Exception $e) {
            $this->isParsing = false;
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Greška pri parsiranju: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.tender-parser');
    }
}
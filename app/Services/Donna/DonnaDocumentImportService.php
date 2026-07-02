<?php

namespace App\Services\Donna;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Extrae texto de un documento (TXT/PDF/DOCX) subido por el cliente y lo
 * manda a OpenAI para estructurarlo en ítems de la base de conocimientos de
 * Donna Business. No guarda nada en BD — solo devuelve ítems propuestos para
 * que el cliente los revise/edite antes de confirmarlos.
 *
 * Nunca lanza excepción hacia el controller: si la extracción o la llamada a
 * OpenAI fallan, se registra en el log y se devuelve un resultado vacío/con
 * error legible, igual que el patrón defensivo de DonnaEmbeddingService.
 */
class DonnaDocumentImportService
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    private const MAX_TEXT_LENGTH = 20000;
    private const MAX_ITEMS = 40;
    private const ALLOWED_TYPES = ['product', 'service', 'faq', 'policy', 'table'];

    public function enabled(): bool
    {
        return filled(config('services.openai.api_key'));
    }

    /**
     * Extrae el texto plano de un archivo subido según su extensión.
     * Lanza \RuntimeException con un mensaje apto para mostrar al usuario si
     * el formato no es soportado o el archivo no se puede leer.
     */
    public function extractText(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        $text = match ($ext) {
            'txt'  => $this->extractFromTxt($file),
            'pdf'  => $this->extractFromPdf($file),
            'docx' => $this->extractFromDocx($file),
            default => throw new \RuntimeException(
                'Formato no soportado. Solo se aceptan archivos .txt, .pdf o .docx (Word moderno, no el formato .doc antiguo).'
            ),
        };

        $text = trim($text);
        if ($text === '') {
            throw new \RuntimeException('No se pudo extraer texto del archivo. ¿Está vacío o es una imagen escaneada sin texto?');
        }

        return mb_substr($text, 0, self::MAX_TEXT_LENGTH);
    }

    private function extractFromTxt(UploadedFile $file): string
    {
        return (string) file_get_contents($file->getRealPath());
    }

    private function extractFromPdf(UploadedFile $file): string
    {
        try {
            $pdf = (new PdfParser())->parseFile($file->getRealPath());
            return $pdf->getText();
        } catch (\Throwable $e) {
            Log::warning('DonnaDocumentImportService: fallo al leer PDF', ['error' => $e->getMessage()]);
            throw new \RuntimeException('No se pudo leer el PDF. Verifica que no esté protegido con contraseña ni sea un escaneo sin texto.');
        }
    }

    private function extractFromDocx(UploadedFile $file): string
    {
        try {
            $doc = WordIOFactory::load($file->getRealPath(), 'Word2007');
            $text = '';
            foreach ($doc->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $sub) {
                            if (method_exists($sub, 'getText')) {
                                $text .= $sub->getText() . "\n";
                            }
                        }
                    }
                }
            }
            return $text;
        } catch (\Throwable $e) {
            Log::warning('DonnaDocumentImportService: fallo al leer DOCX', ['error' => $e->getMessage()]);
            throw new \RuntimeException('No se pudo leer el documento Word. Asegúrate de que sea un .docx válido.');
        }
    }

    /**
     * Manda el texto extraído a OpenAI para que lo divida en ítems
     * normalizados. Devuelve un array de ['type', 'title', 'content_text'].
     * Nunca lanza: si algo falla, devuelve [] y loguea.
     */
    public function structureIntoItems(string $text): array
    {
        if (!$this->enabled()) {
            return [];
        }

        $prompt = <<<PROMPT
Eres un asistente que organiza información de un negocio en ítems independientes para
la base de conocimientos de un agente de atención al cliente por WhatsApp (Donna).

A partir del texto de un documento que subió el dueño del negocio, genera una lista de
ítems. Cada ítem debe ser una unidad de información autocontenida (un producto, un
servicio, una pregunta frecuente con su respuesta, una política, o una tabla de datos).

Reglas:
- No inventes información que no esté en el texto.
- Si el texto tiene una lista de productos/precios, crea un ítem por cada producto o
  grupo de variantes del mismo producto.
- Redacta el título de forma corta y clara (máx. 200 caracteres).
- Redacta el contenido normalizado y bien estructurado, en español neutro, listo para
  que el agente lo lea tal cual (máx. 5000 caracteres por ítem).
- El campo "type" debe ser exactamente uno de: product, service, faq, policy, table.
- Máximo {$this->maxItems()} ítems. Si el texto tiene más información de la que cabe,
  prioriza lo más útil para responder preguntas de clientes (precios, horarios,
  políticas, contacto) antes que detalles secundarios.

Devuelve SOLO este JSON, sin texto antes ni después:
{"items": [{"type": "...", "title": "...", "content_text": "..."}]}
PROMPT;

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(60)
                ->post(self::ENDPOINT, [
                    'model'           => config('services.donna.knowledge_import_model', 'gpt-4o-mini'),
                    'response_format' => ['type' => 'json_object'],
                    'temperature'     => 0.2,
                    'messages'        => [
                        ['role' => 'system', 'content' => $prompt],
                        ['role' => 'user', 'content' => $text],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('DonnaDocumentImportService: fallo al estructurar documento', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 500),
                ]);
                return [];
            }

            $content = $response->json('choices.0.message.content');
            $decoded = json_decode((string) $content, true);
            $items   = is_array($decoded['items'] ?? null) ? $decoded['items'] : [];

            return $this->sanitizeItems($items);
        } catch (\Throwable $e) {
            Log::warning('DonnaDocumentImportService: excepción al estructurar documento', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function maxItems(): int
    {
        return self::MAX_ITEMS;
    }

    /**
     * Recorta a los mismos límites que ClienteDonnaKnowledgeController::store()
     * y corrige un "type" fuera del enum en vez de descartar el ítem.
     */
    private function sanitizeItems(array $items): array
    {
        $clean = [];

        foreach (array_slice($items, 0, self::MAX_ITEMS) as $item) {
            $title   = trim((string) ($item['title'] ?? ''));
            $content = trim((string) ($item['content_text'] ?? ''));
            if ($title === '' || $content === '') {
                continue;
            }

            $type = $item['type'] ?? 'faq';
            if (!in_array($type, self::ALLOWED_TYPES, true)) {
                $type = 'faq';
            }

            $clean[] = [
                'type'         => $type,
                'title'        => mb_substr($title, 0, 200),
                'content_text' => mb_substr($content, 0, 5000),
            ];
        }

        return $clean;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DonnaAgentConfig;
use App\Models\DonnaIntegration;
use App\Models\DonnaKnowledgeBase;
use App\Models\DonnaKnowledgeItem;
use App\Models\DonnaSubscription;
use App\Services\Donna\DonnaDocumentImportService;
use App\Services\Donna\DonnaEmbeddingService;
use App\Services\Donna\Google\DonnaBusinessKnowledgeSheetService;
use App\Services\Donna\Google\DonnaGoogleTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClienteDonnaKnowledgeController extends Controller
{
    public function __construct(
        private DonnaBusinessKnowledgeSheetService $sheetService,
        private DonnaGoogleTokenService            $tokenService,
        private DonnaEmbeddingService              $embeddingService,
        private DonnaDocumentImportService         $importService,
    ) {}

    /**
     * Genera (o regenera) el embedding semántico del ítem y lo persiste.
     * Nunca lanza excepción hacia afuera: si OpenAI falla o no está
     * configurado, el ítem se guarda igual y la búsqueda cae a keyword-match
     * para ese ítem puntual (ver DonnaKnowledgeService::scoreItems).
     */
    private function refreshEmbedding(DonnaKnowledgeItem $item): void
    {
        try {
            $vector = $this->embeddingService->embed($item->embeddingSourceText());

            if ($vector !== null) {
                $item->forceFill([
                    'embedding_json'       => $vector,
                    'embedding_model'      => $this->embeddingService->model(),
                    'embedding_updated_at' => now(),
                ])->save();
            }
        } catch (\Throwable $e) {
            Log::warning('DonnaKnowledge: no se pudo generar embedding', [
                'item_id' => $item->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function getBusinessSub(int $clientId): ?DonnaSubscription
    {
        return DonnaSubscription::where('client_id', $clientId)
            ->where('service_type', 'business')
            ->whereIn('status', ['active', 'pending'])
            ->latest()
            ->first();
    }

    private function getOrCreateBase(int $clientId, int $subId): DonnaKnowledgeBase
    {
        return DonnaKnowledgeBase::firstOrCreate(
            ['client_id' => $clientId],
            [
                'service_id'  => $subId,
                'name'        => 'Base de conocimientos',
                'description' => '',
                'status'      => 'active',
            ]
        );
    }

    private function trySheetSync(int $clientId, DonnaKnowledgeItem $item, string $action): bool
    {
        try {
            $config = DonnaAgentConfig::where('client_id', $clientId)
                ->where('service_type', 'business')
                ->where('is_active', true)
                ->where('sheets_enabled', true)
                ->whereNotNull('spreadsheet_id')
                ->first();

            if (!$config) return false;

            $integ = DonnaIntegration::where('client_id', $clientId)
                ->where('integration_type', 'google')
                ->where('status', 'active')
                ->first();

            if (!$integ) return false;

            $token = $this->tokenService->getValidAccessToken($integ);
            if (!$token) {
                Log::warning('DonnaKnowledge: token inválido o expirado', ['client_id' => $clientId]);
                return false;
            }

            return $this->sheetService->syncAllItems($token, $config->spreadsheet_id, $clientId);
        } catch (\Throwable $e) {
            Log::warning('DonnaKnowledge: sheet sync failed', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function store(Request $request)
    {
        // El contador de caracteres del front usa .value.length (\n = 1 char), pero el
        // navegador normaliza los saltos de línea del <textarea> a CRLF al armar el
        // FormData. Igualamos a LF antes de validar para que el límite de 5000 sea
        // consistente con lo que el cliente ve en pantalla.
        $request->merge([
            'content_text' => str_replace(["\r\n", "\r"], "\n", (string) $request->input('content_text')),
        ]);

        $validated = $request->validate([
            'type'         => 'required|in:product,service,faq,policy,table',
            'title'        => 'required|string|max:200',
            'content_text' => 'required|string|max:5000',
            'source_url'   => 'nullable|url|max:500',
        ]);

        $cliente = Auth::guard('cliente')->user();
        $sub = $this->getBusinessSub($cliente->idcli);

        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'No tienes una suscripción Donna Business.'], 403);
        }

        $base = $this->getOrCreateBase($cliente->idcli, $sub->id);

        $item = DonnaKnowledgeItem::create([
            'knowledge_base_id' => $base->id,
            'client_id'         => $cliente->idcli,
            'service_id'        => $sub->id,
            'type'              => $validated['type'],
            'title'             => $validated['title'],
            'content_text'      => $validated['content_text'],
            'source_url'        => $validated['source_url'] ?? null,
            'is_active'         => true,
        ]);

        $this->refreshEmbedding($item);
        $synced = $this->trySheetSync($cliente->idcli, $item, 'create');

        return response()->json(['success' => true, 'item' => $item, 'sheet_synced' => $synced]);
    }

    public function update(Request $request, int $id)
    {
        $request->merge([
            'content_text' => str_replace(["\r\n", "\r"], "\n", (string) $request->input('content_text')),
        ]);

        $validated = $request->validate([
            'type'         => 'required|in:product,service,faq,policy,table',
            'title'        => 'required|string|max:200',
            'content_text' => 'required|string|max:5000',
            'source_url'   => 'nullable|url|max:500',
        ]);

        $cliente = Auth::guard('cliente')->user();

        $item = DonnaKnowledgeItem::where('id', $id)
            ->where('client_id', $cliente->idcli)
            ->firstOrFail();

        $item->update([
            'type'         => $validated['type'],
            'title'        => $validated['title'],
            'content_text' => $validated['content_text'],
            'source_url'   => $validated['source_url'] ?? null,
        ]);

        $this->refreshEmbedding($item);
        $synced = $this->trySheetSync($cliente->idcli, $item, 'update');

        return response()->json(['success' => true, 'item' => $item, 'sheet_synced' => $synced]);
    }

    public function destroy(int $id)
    {
        $cliente = Auth::guard('cliente')->user();

        $item = DonnaKnowledgeItem::where('id', $id)
            ->where('client_id', $cliente->idcli)
            ->firstOrFail();

        $item->delete();

        $this->trySheetSync($cliente->idcli, $item, 'delete');

        return response()->json(['success' => true]);
    }

    /**
     * Paso 1 de la importación: recibe un archivo (txt/pdf/docx), extrae su
     * texto y lo manda a la IA para proponer ítems. No toca la base de datos
     * — el cliente revisa/edita los ítems propuestos antes de confirmarlos
     * en importConfirm().
     */
    public function importExtract(Request $request)
    {
        if (!$this->importService->enabled()) {
            return response()->json([
                'success' => false,
                'message' => 'La importación automática no está disponible todavía. Contacta al equipo de Streamify.',
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:txt,pdf,docx|max:8192',
        ]);

        $cliente = Auth::guard('cliente')->user();
        if (!$this->getBusinessSub($cliente->idcli)) {
            return response()->json(['success' => false, 'message' => 'No tienes una suscripción Donna Business.'], 403);
        }

        try {
            $text = $this->importService->extractText($request->file('file'));
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $items = $this->importService->structureIntoItems($text);

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar ningún ítem a partir del documento. Intenta con un archivo con más texto o más claro.',
            ], 422);
        }

        return response()->json(['success' => true, 'items' => $items]);
    }

    /**
     * Paso 2 de la importación: recibe la lista de ítems ya revisados/editados
     * por el cliente y los crea en bloque, igual que store() pero en loop.
     * El sync a Google Sheets se hace una sola vez al final (sincroniza todos
     * los ítems del cliente de una), no por cada ítem.
     */
    public function importConfirm(Request $request)
    {
        $items = $request->input('items', []);
        foreach ($items as $key => $item) {
            $items[$key]['content_text'] = str_replace(["\r\n", "\r"], "\n", (string) ($item['content_text'] ?? ''));
        }
        $request->merge(['items' => $items]);

        $validated = $request->validate([
            'items'                     => 'required|array|min:1|max:40',
            'items.*.type'              => 'required|in:product,service,faq,policy,table',
            'items.*.title'             => 'required|string|max:200',
            'items.*.content_text'      => 'required|string|max:5000',
        ]);

        $cliente = Auth::guard('cliente')->user();
        $sub = $this->getBusinessSub($cliente->idcli);

        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'No tienes una suscripción Donna Business.'], 403);
        }

        $base = $this->getOrCreateBase($cliente->idcli, $sub->id);

        $created = [];
        foreach ($validated['items'] as $itemData) {
            $item = DonnaKnowledgeItem::create([
                'knowledge_base_id' => $base->id,
                'client_id'         => $cliente->idcli,
                'service_id'        => $sub->id,
                'type'              => $itemData['type'],
                'title'             => $itemData['title'],
                'content_text'      => $itemData['content_text'],
                'is_active'         => true,
            ]);

            $this->refreshEmbedding($item);
            $created[] = $item;
        }

        $synced = $this->trySheetSync($cliente->idcli, $created[0], 'import');

        return response()->json(['success' => true, 'items' => $created, 'sheet_synced' => $synced]);
    }
}

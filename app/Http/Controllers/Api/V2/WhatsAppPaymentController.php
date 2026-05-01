<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Jobs\TriggerRecargaVerificationJob;
use App\Models\Banco;
use App\Models\Cliente;
use App\Models\Historial;
use App\Models\Recarga;
use App\Services\WhatsAppReceiptCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WhatsAppPaymentController extends Controller
{
    private const ESTADO_RECARGA_PENDIENTE = 1;
    private const BANK_ALIASES = [
        'banco guayaquil' => ['guayaquil', 'banco del barrio', 'del barrio'],
    ];

    public function __construct(private WhatsAppReceiptCheckoutService $checkoutService)
    {
        request()->headers->set('Accept', 'application/json');
    }

    public function receiptCheckout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcli' => 'nullable|exists:clientes,idcli',
            'cliente_nombre' => 'nullable|string|max:100',
            'cliente_telefono' => 'nullable|string|max:50',
            'cliente_email' => 'nullable|email|max:255',
            'producto_id' => 'required|exists:productos,id',
            'idban' => 'required|exists:bancos,idban',
            'valor' => 'required|numeric|min:0.01',
            'foto' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:5120',
            'numcomprobante' => 'nullable|string|max:255',
            'canal' => 'nullable|in:whatsapp,messenger,telegram,webchat',
            'external_reference' => 'nullable|string|max:191',
            'observacion_cliente' => 'nullable|string|max:500',
            'trace_id' => 'nullable|string|max:120',
            'wait_seconds' => 'nullable|integer|min:1|max:15',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('idcli') && !$request->filled('cliente_telefono')) {
                $validator->errors()->add('cliente_telefono', 'Debes enviar idcli o cliente_telefono.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->checkoutService->process($validator->validated());
        $httpStatus = $result['http_status'] ?? 200;
        unset($result['http_status']);

        return response()->json($result, $httpStatus);
    }

    public function receiptIntake(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcli' => 'nullable|exists:clientes,idcli',
            'cliente_nombre' => 'nullable|string|max:100',
            'cliente_telefono' => 'nullable|string|max:50',
            'cliente_email' => 'nullable|email|max:255',
            'idban' => 'nullable|exists:bancos,idban',
            'banco_nombre' => 'nullable|string|max:100',
            'banco' => 'nullable|string|max:100',
            'valor' => 'required|numeric|min:0.01',
            'foto' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
            'media_base64' => 'nullable|string',
            'media_url' => 'nullable|string|max:2048',
            'media_file_name' => 'nullable|string|max:191',
            'media_mime_type' => 'nullable|string|max:120',
            'numcomprobante' => 'nullable|string|max:255',
            'canal' => 'nullable|in:whatsapp,messenger,telegram,webchat',
            'external_reference' => 'nullable|string|max:191',
            'trace_id' => 'nullable|string|max:120',
            'idconv' => 'nullable|integer',
            'canal_user_id' => 'nullable|string|max:120',
            'disparar_verificacion' => 'nullable|boolean',
            'ocr' => 'nullable|array',
            'validacion' => 'nullable|array',
            'qr' => 'nullable|array',
            'observacion_cliente' => 'nullable|string|max:500',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('idcli') && !$request->filled('cliente_telefono')) {
                $validator->errors()->add('cliente_telefono', 'Debes enviar idcli o cliente_telefono.');
            }

            if (!$request->filled('idban') && !$request->filled('banco_nombre') && !$request->filled('banco')) {
                $validator->errors()->add('idban', 'Debes enviar idban o banco_nombre.');
            }

            if (!$request->hasFile('foto') && !$request->filled('media_base64') && !$request->filled('media_url')) {
                $validator->errors()->add('foto', 'Debes enviar foto, media_base64 o media_url.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $temporaryFilePath = null;

        try {
            ['file' => $receiptFile, 'temp_path' => $temporaryFilePath] = $this->resolveReceiptFile($request, $validated);

            $validated['idban'] = $validated['idban']
                ?? $this->resolveBancoId($validated['banco_nombre'] ?? $validated['banco'] ?? null);

            if (!$validated['idban']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo resolver el banco enviado.',
                ], 422);
            }

            ['cliente' => $cliente, 'created' => $clienteCreado] = $this->resolveOrCreateCliente($validated);

            $receiptData = $this->prepareReceiptIdentity(
                file: $receiptFile,
                providedReceiptNumber: $validated['numcomprobante'] ?? null
            );

            $duplicate = $this->findDuplicateReceipt(
                comprobanteHash: $receiptData['hash'],
                receiptNumber: $receiptData['number']
            );

            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'El comprobante ya fue registrado previamente.',
                    'status' => 'duplicate_receipt',
                    'data' => [
                        'cliente_creado' => false,
                        'recarga' => $this->formatRecarga($duplicate->fresh(['cliente', 'banco', 'estado'])),
                    ],
                ], 409);
            }

            $receiptData['path'] = $this->storeReceiptFile($receiptFile, $receiptData['hash']);

            $recarga = Recarga::create([
                'idcli' => $cliente->idcli,
                'idban' => $validated['idban'],
                'numcomprobante' => $receiptData['number'],
                'valor' => $validated['valor'],
                'foto' => $receiptData['path'],
                'comprobante_hash' => $receiptData['hash'],
                'idestado' => self::ESTADO_RECARGA_PENDIENTE,
                'origen' => $validated['canal'] ?? 'whatsapp',
                'external_reference' => $validated['external_reference'] ?? null,
                'metadata' => array_filter([
                    'trace_id' => $validated['trace_id'] ?? ('wa-recarga-' . Str::uuid()),
                    'cliente_nombre_reportado' => $validated['cliente_nombre'] ?? null,
                    'cliente_telefono_reportado' => $validated['cliente_telefono'] ?? null,
                    'cliente_email_reportado' => $validated['cliente_email'] ?? null,
                    'canal_user_id' => $validated['canal_user_id'] ?? null,
                    'idconv' => $validated['idconv'] ?? null,
                    'observacion_cliente' => $validated['observacion_cliente'] ?? null,
                    'ocr' => $validated['ocr'] ?? null,
                    'validacion' => $validated['validacion'] ?? null,
                    'qr' => $validated['qr'] ?? null,
                ], fn ($value) => $value !== null && $value !== ''),
            ]);

            Historial::create([
                'accion' => 'Recarga-N8N-Creada',
                'descripcion' => 'Se registró recarga desde intake n8n. ID: ' . $recarga->idrec . ' | Cliente: ' . $cliente->nombrecli,
                'empleado_id' => null,
            ]);

            $verificacionDisparada = false;
            $verificationError = null;

            if (filter_var($validated['disparar_verificacion'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                try {
                    TriggerRecargaVerificationJob::dispatchSync($recarga->idrec);
                    $verificacionDisparada = true;
                } catch (\Throwable $e) {
                    $verificationError = $e->getMessage();

                    Log::warning('No se pudo disparar verificacion automatica desde intake n8n', [
                        'idrec' => $recarga->idrec,
                        'error' => $verificationError,
                    ]);
                }
            }

            $response = [
                'success' => true,
                'message' => 'Comprobante registrado correctamente.',
                'status' => $verificacionDisparada ? 'recarga_created_verification_dispatched' : 'recarga_created',
                'data' => [
                    'cliente_creado' => $clienteCreado,
                    'verificacion_disparada' => $verificacionDisparada,
                    'cliente' => [
                        'idcli' => $cliente->idcli,
                        'nombrecli' => $cliente->nombrecli,
                        'telefonocli' => $cliente->telefonocli,
                        'email' => $cliente->email,
                    ],
                    'recarga' => $this->formatRecarga($recarga->fresh(['cliente', 'banco', 'estado'])),
                ],
            ];

            if ($verificationError) {
                $response['data']['verification_error'] = $verificationError;
            }

            return response()->json($response, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el comprobante.',
                'error' => $e->getMessage(),
            ], 500);
        } finally {
            if ($temporaryFilePath && is_file($temporaryFilePath)) {
                @unlink($temporaryFilePath);
            }
        }
    }

    private function resolveReceiptFile(Request $request, array $payload): array
    {
        if ($request->hasFile('foto')) {
            return [
                'file' => $request->file('foto'),
                'temp_path' => null,
            ];
        }

        if (!empty($payload['media_base64'])) {
            return $this->createUploadedFileFromBinary(
                binaryContent: $this->decodeBase64Media($payload['media_base64']),
                mimeType: $payload['media_mime_type'] ?? null,
                originalName: $payload['media_file_name'] ?? 'comprobante.jpg'
            );
        }

        if (!empty($payload['media_url'])) {
            $response = Http::timeout(15)->get($payload['media_url']);

            if (!$response->successful()) {
                throw new \RuntimeException('No se pudo descargar media_url.');
            }

            return $this->createUploadedFileFromBinary(
                binaryContent: $response->body(),
                mimeType: $payload['media_mime_type'] ?? $response->header('Content-Type'),
                originalName: $payload['media_file_name'] ?? basename(parse_url($payload['media_url'], PHP_URL_PATH) ?: 'comprobante.jpg')
            );
        }

        throw new \RuntimeException('No se recibió un archivo de comprobante válido.');
    }

    private function decodeBase64Media(string $rawBase64): string
    {
        $normalized = trim($rawBase64);

        if (str_contains($normalized, ',')) {
            [, $normalized] = explode(',', $normalized, 2);
        }

        $binary = base64_decode($normalized, true);

        if ($binary === false) {
            throw new \RuntimeException('media_base64 no es válido.');
        }

        return $binary;
    }

    private function createUploadedFileFromBinary(string $binaryContent, ?string $mimeType, string $originalName): array
    {
        $extension = $this->guessExtension($mimeType, $originalName);
        $tempPath = tempnam(sys_get_temp_dir(), 'receipt_');

        if ($tempPath === false) {
            throw new \RuntimeException('No se pudo crear archivo temporal para el comprobante.');
        }

        $finalTempPath = $tempPath . '.' . $extension;
        rename($tempPath, $finalTempPath);
        file_put_contents($finalTempPath, $binaryContent);

        return [
            'file' => new UploadedFile(
                $finalTempPath,
                $originalName !== '' ? $originalName : ('comprobante.' . $extension),
                $mimeType ?: ('image/' . $extension),
                null,
                true
            ),
            'temp_path' => $finalTempPath,
        ];
    }

    private function guessExtension(?string $mimeType, string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $extension;
        }

        return match (strtolower((string) $mimeType)) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
    }

    private function resolveBancoId(?string $bankName): ?int
    {
        $normalizedSearch = $this->normalizeText($bankName);

        if ($normalizedSearch === '') {
            return null;
        }

        $searchTerms = [$normalizedSearch];

        foreach (self::BANK_ALIASES as $canonicalName => $aliases) {
            $normalizedCanonicalName = $this->normalizeText($canonicalName);
            $normalizedAliases = array_map(fn (string $alias) => $this->normalizeText($alias), $aliases);

            if (
                $normalizedSearch === $normalizedCanonicalName
                || in_array($normalizedSearch, $normalizedAliases, true)
            ) {
                $searchTerms = array_values(array_unique([
                    $normalizedCanonicalName,
                    ...$normalizedAliases,
                ]));
                break;
            }
        }

        return Banco::query()
            ->get(['idban', 'nombreban'])
            ->first(function (Banco $banco) use ($searchTerms) {
                $normalizedBank = $this->normalizeText($banco->nombreban);

                foreach ($searchTerms as $term) {
                    if (
                        $normalizedBank === $term
                        || str_contains($normalizedBank, $term)
                        || str_contains($term, $normalizedBank)
                    ) {
                        return true;
                    }
                }

                foreach (self::BANK_ALIASES as $canonicalName => $aliases) {
                    if ($normalizedBank !== $this->normalizeText($canonicalName)) {
                        continue;
                    }

                    foreach ($aliases as $alias) {
                        $normalizedAlias = $this->normalizeText($alias);
                        if (in_array($normalizedAlias, $searchTerms, true)) {
                            return true;
                        }
                    }
                }

                return false;
            })?->idban;
    }

    private function normalizeText(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();
    }

    private function resolveOrCreateCliente(array $payload): array
    {
        // 1) Primero intentamos identificar por telefono.
        $clientePorTelefono = Cliente::buscarPorTelefonoNormalizado($payload['cliente_telefono'] ?? null);

        if ($clientePorTelefono) {
            $updates = [];

            if (!empty($payload['cliente_nombre']) && empty($clientePorTelefono->nombrecli)) {
                $updates['nombrecli'] = trim($payload['cliente_nombre']);
            }

            if (!empty($payload['cliente_email']) && empty($clientePorTelefono->email)) {
                $updates['email'] = $payload['cliente_email'];
            }

            if ($updates !== []) {
                $clientePorTelefono->update($updates);
                $clientePorTelefono->refresh();
            }

            return [
                'cliente' => $clientePorTelefono,
                'created' => false,
            ];
        }

        // 2) Si no hubo match por telefono, usamos idcli si fue provisto.
        if (!empty($payload['idcli'])) {
            return [
                'cliente' => Cliente::query()->findOrFail($payload['idcli']),
                'created' => false,
            ];
        }

        return [
            'cliente' => Cliente::create([
                'nombrecli' => $this->buildNombreClienteParaNuevoRegistro($payload),
                'telefonocli' => trim((string) ($payload['cliente_telefono'] ?? '')),
                'email' => $payload['cliente_email'] ?? null,
                'saldo' => 0,
            ]),
            'created' => true,
        ];
    }

    private function buildNombreClienteParaNuevoRegistro(array $payload): string
    {
        $provided = trim((string) ($payload['cliente_nombre'] ?? ''));

        if ($provided !== '') {
            return $provided;
        }

        return $this->nextWhatsappClientName();
    }

    private function nextWhatsappClientName(): string
    {
        $prefix = 'Cliente WhatsApp';
        $existingNames = Cliente::query()
            ->where('nombrecli', 'like', $prefix . '%')
            ->pluck('nombrecli')
            ->all();

        $used = [];
        foreach ($existingNames as $name) {
            $normalized = trim((string) $name);

            if ($normalized === $prefix) {
                $used[0] = true;
                continue;
            }

            if (preg_match('/^Cliente WhatsApp\s+(\d+)$/', $normalized, $matches) === 1) {
                $used[(int) $matches[1]] = true;
            }
        }

        $sequence = 1;
        while (isset($used[$sequence])) {
            $sequence++;
        }

        return $prefix . ' ' . $sequence;
    }

    private function prepareReceiptIdentity(UploadedFile $file, ?string $providedReceiptNumber): array
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $receiptNumber = trim((string) $providedReceiptNumber);

        if ($receiptNumber === '') {
            $receiptNumber = 'WA-' . strtoupper(substr($hash, 0, 20));
        }

        return [
            'hash' => $hash,
            'number' => $receiptNumber,
        ];
    }

    private function findDuplicateReceipt(string $comprobanteHash, string $receiptNumber): ?Recarga
    {
        return Recarga::query()
            ->where(function ($query) use ($comprobanteHash, $receiptNumber) {
                $query->where('comprobante_hash', $comprobanteHash)
                    ->orWhere('numcomprobante', $receiptNumber);
            })
            ->latest('created_at')
            ->first();
    }

    private function storeReceiptFile(UploadedFile $file, string $hash): string
    {
        $directory = public_path('storage/comprobantes');
        File::ensureDirectoryExists($directory);

        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';
        $filename = now()->format('YmdHis') . '_' . substr($hash, 0, 16) . '.' . $extension;
        $file->move($directory, $filename);

        return 'comprobantes/' . $filename;
    }

    private function formatRecarga(Recarga $recarga): array
    {
        return [
            'idrec' => $recarga->idrec,
            'idcli' => $recarga->idcli,
            'idban' => $recarga->idban,
            'numcomprobante' => $recarga->numcomprobante,
            'valor' => (float) $recarga->valor,
            'idestado' => $recarga->idestado,
            'estado' => optional($recarga->estado)->nombre,
            'foto' => $recarga->foto,
            'origen' => $recarga->origen,
            'external_reference' => $recarga->external_reference,
            'metadata' => $recarga->metadata,
            'cliente' => [
                'idcli' => $recarga->cliente?->idcli,
                'nombrecli' => $recarga->cliente?->nombrecli,
                'telefonocli' => $recarga->cliente?->telefonocli,
            ],
            'banco' => [
                'idban' => $recarga->banco?->idban,
                'nombreban' => $recarga->banco?->nombreban,
            ],
            'created_at' => optional($recarga->created_at)->toIso8601String(),
            'updated_at' => optional($recarga->updated_at)->toIso8601String(),
        ];
    }
}

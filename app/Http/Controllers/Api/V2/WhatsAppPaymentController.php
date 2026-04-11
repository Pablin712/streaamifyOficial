<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppReceiptCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhatsAppPaymentController extends Controller
{
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
}

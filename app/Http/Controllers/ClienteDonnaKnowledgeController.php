<?php

namespace App\Http\Controllers;

use App\Models\DonnaKnowledgeBase;
use App\Models\DonnaKnowledgeItem;
use App\Models\DonnaSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteDonnaKnowledgeController extends Controller
{
    private function getActiveBusinessSub(int $clientId): ?DonnaSubscription
    {
        return DonnaSubscription::where('client_id', $clientId)
            ->where('service_type', 'business')
            ->where('status', 'active')
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

    public function store(Request $request)
    {
        $request->validate([
            'type'         => 'required|in:product,service,faq,policy,table',
            'title'        => 'required|string|max:200',
            'content_text' => 'required|string|max:5000',
            'source_url'   => 'nullable|url|max:500',
        ]);

        $cliente = Auth::guard('cliente')->user();
        $sub = $this->getActiveBusinessSub($cliente->idcli);

        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'No tienes una suscripción Donna Business activa.'], 403);
        }

        $base = $this->getOrCreateBase($cliente->idcli, $sub->id);

        $item = DonnaKnowledgeItem::create([
            'knowledge_base_id' => $base->id,
            'client_id'         => $cliente->idcli,
            'service_id'        => $sub->id,
            'type'              => $request->input('type'),
            'title'             => $request->input('title'),
            'content_text'      => $request->input('content_text'),
            'source_url'        => $request->input('source_url') ?: null,
            'is_active'         => true,
        ]);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
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
            'type'         => $request->input('type'),
            'title'        => $request->input('title'),
            'content_text' => $request->input('content_text'),
            'source_url'   => $request->input('source_url') ?: null,
        ]);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function destroy(int $id)
    {
        $cliente = Auth::guard('cliente')->user();

        DonnaKnowledgeItem::where('id', $id)
            ->where('client_id', $cliente->idcli)
            ->firstOrFail()
            ->delete();

        return response()->json(['success' => true]);
    }
}

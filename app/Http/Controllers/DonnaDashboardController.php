<?php

namespace App\Http\Controllers;

use App\Models\DonnaConversation;
use App\Models\DonnaMessage;
use App\Models\DonnaSubscription;
use Illuminate\Http\Request;

class DonnaDashboardController extends Controller
{
    public function index()
    {
        $totalSubsActivas  = DonnaSubscription::where('status', 'active')->count();
        $totalPersonal     = DonnaSubscription::where('status', 'active')->where('service_type', 'personal')->count();
        $totalBusiness     = DonnaSubscription::where('status', 'active')->where('service_type', 'business')->count();
        $totalConvs        = DonnaConversation::count();
        $convsHoy          = DonnaConversation::whereDate('last_message_at', today())->count();
        $mensajesHoy       = DonnaMessage::whereDate('created_at', today())->count();

        $topClientes = DonnaConversation::selectRaw('client_id, COUNT(*) as total_convs, MAX(last_message_at) as ultima_actividad')
            ->with('cliente')
            ->groupBy('client_id')
            ->orderByDesc('total_convs')
            ->limit(10)
            ->get();

        $conversacionesRecientes = DonnaConversation::with(['cliente', 'channel'])
            ->latest('last_message_at')
            ->limit(8)
            ->get();

        return view('donna.dashboard.index', compact(
            'totalSubsActivas', 'totalPersonal', 'totalBusiness',
            'totalConvs', 'convsHoy', 'mensajesHoy',
            'topClientes', 'conversacionesRecientes'
        ));
    }

    public function conversaciones(Request $request)
    {
        $query = DonnaConversation::with(['cliente', 'channel'])
            ->withCount('messages');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('sender_name', 'like', "%{$s}%")
                  ->orWhere('external_chat_id', 'like', "%{$s}%")
                  ->orWhereHas('cliente', fn ($c) => $c->where('nombrecli', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('tipo')) {
            $tipo = $request->input('tipo');
            $query->whereHas('channel', fn ($c) => $c->where('channel_type', $tipo));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $conversaciones = $query->latest('last_message_at')->paginate(25)->withQueryString();

        return view('donna.conversaciones.index', compact('conversaciones'));
    }

    public function showMessages($id)
    {
        $conv = DonnaConversation::with(['cliente', 'channel'])->findOrFail($id);

        $messages = $conv->messages()
            ->whereIn('sender_type', ['final_customer', 'ai_agent'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'sender_type'  => $m->sender_type,
                'content'      => $m->effective_text,
                'message_type' => $m->message_type,
                'created_at'   => $m->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'conversation' => [
                'id'             => $conv->id,
                'sender_name'    => $conv->sender_name,
                'status'         => $conv->status,
                'cliente'        => $conv->cliente?->nombrecli ?? '—',
                'channel_type'   => $conv->channel?->channel_type ?? '—',
                'last_message_at' => $conv->last_message_at?->format('d/m/Y H:i'),
            ],
            'messages' => $messages,
        ]);
    }
}

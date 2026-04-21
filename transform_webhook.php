<?php

// Script para transformar payload de Evolution API a formato del sistema de chat

function transformEvolutionToChat($evolutionPayload) {
    $data = $evolutionPayload['body']['data'];
    
    // Extraer información básica
    $transformed = [
        'canal_user_id' => $data['key']['remoteJid'] ?? '',
        'telefono' => extractPhone($data['key']['remoteJid'] ?? ''),
        'nombre' => $data['pushName'] ?? 'Cliente',
        'instance' => $evolutionPayload['body']['instance'] ?? 'Streamify Azul',
        'origen' => 'evolution-api',
        'external_message_id' => $data['key']['id'] ?? '',
        'payload' => [
            'source' => 'evolution-api',
            'original_payload' => $evolutionPayload
        ]
    ];
    
    // Determinar tipo de mensaje y extraer contenido
    $message = $data['message'] ?? [];
    
    if (isset($message['imageMessage'])) {
        $image = $message['imageMessage'];
        $transformed['tipo'] = 'imagen';
        $transformed['mensaje'] = $image['caption'] ?? '';
        $transformed['media_url'] = $image['url'] ?? '';
        $transformed['mime_type'] = $image['mimetype'] ?? 'image/jpeg';
        
    } elseif (isset($message['conversation']) || isset($message['extendedTextMessage'])) {
        $transformed['tipo'] = 'texto';
        
        if (isset($message['conversation'])) {
            $transformed['mensaje'] = $message['conversation'];
        } elseif (isset($message['extendedTextMessage']['text'])) {
            $transformed['mensaje'] = $message['extendedTextMessage']['text'];
        }
        
    } elseif (isset($message['audioMessage'])) {
        $audio = $message['audioMessage'];
        $transformed['tipo'] = 'audio';
        $transformed['media_url'] = $audio['url'] ?? '';
        $transformed['mime_type'] = $audio['mimetype'] ?? 'audio/ogg';
        
    } else {
        // Tipo no soportado
        return null;
    }
    
    return $transformed;
}

function extractPhone($jid) {
    // Extraer número de teléfono del JID (ej: 593961778319@s.whatsapp.net -> 593961778319)
    if (strpos($jid, '@') !== false) {
        return explode('@', $jid)[0];
    }
    return $jid;
}

// Ejemplo de uso
$evolutionPayload = [
    'headers' => [],
    'params' => [],
    'query' => [],
    'body' => [
        'event' => 'messages.upsert',
        'instance' => 'Streamify Azul',
        'data' => [
            'key' => [
                'remoteJid' => '593961778319@s.whatsapp.net',
                'id' => '3EB0E294C292EB5C7F4715',
            ],
            'pushName' => 'Pablin',
            'message' => [
                'imageMessage' => [
                    'url' => 'https://mmg.whatsapp.net/...',
                    'mimetype' => 'image/jpeg',
                    'caption' => 'quiero este de aquí'
                ]
            ]
        ]
    ]
];

echo "=== TRANSFORMACIÓN DE PAYLOAD ===\n\n";
echo "Payload Evolution API original:\n";
echo json_encode($evolutionPayload, JSON_PRETTY_PRINT) . "\n\n";

echo "Transformado a formato del sistema de chat:\n";
$transformed = transformEvolutionToChat($evolutionPayload);
echo json_encode($transformed, JSON_PRETTY_PRINT) . "\n\n";

echo "=== CÓDIGO PARA ENVIAR AL WEBHOOK ===\n";
echo "Una vez transformado, envía este payload a:\n";
echo "POST http://localhost:8000/api/chat/whatsapp/inbound\n";
echo "Header: X-Chat-Webhook-Token: test_token_123\n\n";

echo "=== EJEMPLO DE CURL ===\n";
if ($transformed) {
    $jsonPayload = json_encode($transformed);
    echo "curl -X POST 'http://localhost:8000/api/chat/whatsapp/inbound' \\\n";
    echo "  -H 'Content-Type: application/json' \\\n";
    echo "  -H 'X-Chat-Webhook-Token: test_token_123' \\\n";
    echo "  -d '" . addslashes($jsonPayload) . "'\n";
}
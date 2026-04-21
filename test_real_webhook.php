<?php

// Tu payload real de Evolution API
$evolutionPayload = [
    'headers' => [
        'host' => 'autobot.aaronsoft.es',
        'user-agent' => 'axios/1.13.2',
        'content-length' => '79344',
        'accept' => 'application/json, text/plain, */*',
        'accept-encoding' => 'gzip, compress, deflate, br',
        'content-type' => 'application/json',
        'x-forwarded-for' => '172.29.0.4',
        'x-forwarded-host' => 'autobot.aaronsoft.es',
        'x-forwarded-port' => '443',
        'x-forwarded-proto' => 'https',
        'x-forwarded-server' => 'vps-82a11f08',
        'x-real-ip' => '172.29.0.4'
    ],
    'params' => [],
    'query' => [],
    'body' => [
        'event' => 'messages.upsert',
        'instance' => 'Streamify Azul',
        'data' => [
            'key' => [
                'remoteJid' => '593961778319@s.whatsapp.net',
                'remoteJidAlt' => '593961778319@s.whatsapp.net',
                'fromMe' => false,
                'id' => '3EB0E294C292EB5C7F4715',
                'participant' => '',
                'addressingMode' => 'lid'
            ],
            'pushName' => 'Pablin',
            'status' => 'DELIVERY_ACK',
            'message' => [
                'imageMessage' => [
                    'interactiveAnnotations' => [],
                    'scanLengths' => [],
                    'annotations' => [],
                    'url' => 'https://mmg.whatsapp.net/o1/v/t24/f2/m238/AQMbQgWRXigiHEdoM224wdda5vUeJkpI-Srl8PUAbNDY2955RQ0F4lLvdmE_GdntpOSPnrZP3p7ftpgcbXbK4QbaX4X2KP374Iez9gRu9Q?ccb=9-4&oh=01_Q5Aa4QHCXz3YllWD6rw-OQf2d_QtbZu1aZ4Guhvz5Y9mP92rhA&oe=6A03D473&_nc_sid=e6ed6c&mms3=true',
                    'mimetype' => 'image/jpeg',
                    'caption' => 'quiero este de aquí',
                    // ... (otros campos omitidos por brevedad)
                ]
            ]
        ],
        'destination' => 'https://autobot.aaronsoft.es/webhook/whatsapp-azul',
        'date_time' => '2026-04-13T03:02:13.149Z',
        'sender' => '593996464991@s.whatsapp.net',
        'server_url' => 'https://evoapi.abigailsoft.com',
        'apikey' => null
    ],
    'webhookUrl' => 'https://autobot.aaronsoft.es/webhook/whatsapp-azul',
    'executionMode' => 'production'
];

// Función de transformación
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
            'source' => 'evolution-api'
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
    // Extraer número de teléfono del JID
    if (strpos($jid, '@') !== false) {
        return explode('@', $jid)[0];
    }
    return $jid;
}

// Transformar el payload
echo "=== TRANSFORMANDO PAYLOAD REAL ===\n\n";

$transformed = transformEvolutionToChat($evolutionPayload);

if ($transformed) {
    echo "✅ Payload transformado correctamente:\n";
    echo json_encode($transformed, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== ENVIAR AL WEBHOOK ===\n";
    
    // Configuración
    $webhookUrl = 'http://localhost:8000/api/chat/whatsapp/inbound';
    $token = 'test_token_123';
    
    // Enviar al webhook
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($transformed));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Chat-Webhook-Token: ' . $token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "URL: $webhookUrl\n";
    echo "Código HTTP: $httpCode\n";
    echo "Respuesta: $response\n";
    
    curl_close($ch);
    
} else {
    echo "❌ No se pudo transformar el payload (tipo de mensaje no soportado)\n";
}
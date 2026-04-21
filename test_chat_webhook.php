<?php

// Configuración
$webhookUrl = 'http://localhost:8000/api/chat/whatsapp/inbound';
$token = getenv('CHAT_WEBHOOK_TOKEN') ?: 'test_token_123';

// Payload de prueba (mensaje de texto)
$payload = [
    'canal_user_id' => '593999999999@s.whatsapp.net',
    'telefono' => '593999999999',
    'nombre' => 'Cliente Demo',
    'mensaje' => 'Hola, necesito ayuda con mi cuenta',
    'tipo' => 'texto',
    'external_message_id' => 'wamid.test-' . time(),
    'instance' => 'Streamify Azul',
    'origen' => 'n8n',
    'payload' => [
        'source' => 'evolution-api'
    ]
];

// Headers
$headers = [
    'Content-Type: application/json',
    'X-Chat-Webhook-Token: ' . $token
];

// Inicializar cURL
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Ejecutar
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Mostrar resultados
echo "=== PRUEBA WEBHOOK CHAT ===\n";
echo "URL: $webhookUrl\n";
echo "Token: $token\n";
echo "Payload:\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";
echo "Código HTTP: $httpCode\n";
echo "Respuesta:\n$response\n";

if ($error) {
    echo "Error cURL: $error\n";
}

// También probar con token en payload (para desarrollo)
echo "\n=== PRUEBA CON TOKEN EN PAYLOAD ===\n";
$payload['token'] = $token;
unset($headers[1]); // Quitar header de token

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Código HTTP: $httpCode\n";
echo "Respuesta:\n$response\n";
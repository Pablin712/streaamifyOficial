<?php

// Servidor webhook de prueba para recibir mensajes outbound
$port = 8080;
$address = '0.0.0.0';

echo "🚀 Servidor webhook de prueba iniciado en http://localhost:$port\n";
echo "📝 Recibiendo mensajes outbound del sistema de chat\n";
echo "📌 Configura en tu .env: N8N_CLIENT_MESSAGE_WEBHOOK=http://localhost:$port/webhook\n";
echo "🔍 Esperando mensajes...\n\n";

// Crear socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, $address, $port);
socket_listen($socket);

while (true) {
    $client = socket_accept($socket);

    // Leer la solicitud
    $request = socket_read($client, 4096);

    // Extraer el cuerpo JSON
    $lines = explode("\r\n", $request);
    $body = '';
    $inBody = false;

    foreach ($lines as $line) {
        if ($line === '') {
            $inBody = true;
            continue;
        }
        if ($inBody) {
            $body .= $line . "\n";
        }
    }

    // Parsear JSON
    $data = json_decode(trim($body), true);

    // Mostrar información
    echo "📨 MENSAJE RECIBIDO:\n";
    echo "📅 " . date('Y-m-d H:i:s') . "\n";

    if ($data) {
        echo "📱 Tipo: " . ($data['tipo_contenido'] ?? 'texto') . "\n";
        echo "👤 Para: " . ($data['numero'] ?? 'N/A') . "\n";
        echo "💬 Mensaje: " . ($data['mensaje'] ?? 'N/A') . "\n";

        if (isset($data['media_url'])) {
            echo "🖼️ Media URL: " . $data['media_url'] . "\n";
        }

        echo "📊 Datos completos:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "⚠️ No se pudo parsear JSON\n";
        echo "Raw request:\n$request\n";
    }

    echo str_repeat("-", 50) . "\n";

    // Responder con éxito
    $response = "HTTP/1.1 200 OK\r\n";
    $response .= "Content-Type: application/json\r\n";
    $response .= "Content-Length: 21\r\n";
    $response .= "\r\n";
    $response .= '{"status":"received"}';

    socket_write($client, $response);
    socket_close($client);
}

socket_close($socket);


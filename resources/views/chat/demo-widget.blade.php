<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Widget Chat - Streamify</title>
    @vite(['resources/css/app.css', 'resources/js/chat-widget.js'])
</head>
<body class="bg-gradient-to-br from-purple-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Demo Widget de Chat</h1>
                <p class="text-lg text-gray-600 mb-6">
                    Esta es una página de demostración del widget de chat de Streamify.
                    El widget flotante aparecerá en la esquina inferior derecha.
                </p>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <h3 class="font-semibold text-blue-900 mb-2">Instrucciones:</h3>
                    <ol class="list-decimal list-inside text-blue-800 space-y-1">
                        <li>Haz clic en la burbuja morada flotante</li>
                        <li>Se abrirá el chat en modo anónimo (sesión temporal de 20 horas)</li>
                        <li>Escribe un mensaje para iniciar la conversación</li>
                        <li>Los empleados podrán ver y responder tus mensajes</li>
                    </ol>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-3">🔵 Modo Anónimo</h3>
                        <p class="text-gray-600 text-sm">
                            Como visitante anónimo, tu sesión se guarda localmente y expira en 20 horas.
                            Puedes continuar la conversación mientras la sesión esté activa.
                        </p>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-3">✅ Modo Autenticado</h3>
                        <p class="text-gray-600 text-sm">
                            Si inicias sesión como cliente, tu historial de chat se guardará permanentemente
                            y podrás acceder a él desde cualquier dispositivo.
                        </p>
                    </div>
                </div>

                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-2">Estado actual:</h3>
                    <p class="text-gray-700">
                        <span class="font-medium">Tipo de sesión:</span>
                        @if (Auth::guard('cliente')->check())
                            <span class="text-green-600 font-semibold">Autenticado</span>
                        @else
                            <span class="text-purple-600 font-semibold">Anónimo</span>
                        @endif
                    </p>
                    <p class="text-gray-700 mt-1">
                        <span class="font-medium">Cliente:</span>
                        @if (Auth::guard('cliente')->check())
                            <span class="text-gray-900">{{ Auth::guard('cliente')->user()->nombrecli }} (ID: {{ Auth::guard('cliente')->user()->idcli }})</span>
                        @else
                            <span class="text-gray-500">No autenticado</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Contenido de ejemplo -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Servicios de Streamify</h2>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <div class="text-4xl mb-2">📱</div>
                        <h3 class="font-semibold">Recargas</h3>
                        <p class="text-sm text-gray-600">Todas las operadoras</p>
                    </div>
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <div class="text-4xl mb-2">💳</div>
                        <h3 class="font-semibold">Paquetes</h3>
                        <p class="text-sm text-gray-600">Internet y llamadas</p>
                    </div>
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <div class="text-4xl mb-2">🎮</div>
                        <h3 class="font-semibold">Gaming</h3>
                        <p class="text-sm text-gray-600">Tarjetas digitales</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget de Chat -->
    <div id="chat-widget-mount"></div>

    <script>
        // Inicializar el widget cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            @if (Auth::guard('cliente')->check())
                window.initChatWidget({{ Auth::guard('cliente')->user()->idcli }}, '/api/v1/chat');
            @else
                window.initChatWidget(null, '/api/v1/chat');
            @endif
        });
    </script>
</body>
</html>

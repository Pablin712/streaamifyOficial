<div wire:poll.1s="verificarNuevosMensajes" style="display: none;">
    <!-- Notificador invisible que verifica nuevos mensajes cada segundo -->
</div>

<script>
    let audioContext = null;
    let notificationTimeout = null;
    let lastCheckTime = Date.now();

    document.addEventListener('livewire:init', () => {
        Livewire.on('nuevoMensajeGlobal', (event) => {
            console.log('Evento recibido:', event);
            // Livewire v3 envía los datos como primer elemento del array
            const data = event[0] || event;
            console.log('Data procesada:', data);
            mostrarNotificacionGlobal(data.count, data.nuevos);
            reproducirSonidoNotificacion();

            // Actualizar tiempo del último check
            lastCheckTime = Date.now();
        });
    });

    // Verificación adicional cuando la pestaña vuelve a estar activa
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            const timeSinceLastCheck = Date.now() - lastCheckTime;
            // Si han pasado más de 2 segundos, forzar verificación inmediata
            if (timeSinceLastCheck > 2000) {
                @this.call('verificarNuevosMensajes');
            }
        }
    });

    function mostrarNotificacionGlobal(total, nuevos) {
        // Crear notificación si no existe
        let notif = document.getElementById('chat-notification-global');

        if (!notif) {
            notif = document.createElement('div');
            notif.id = 'chat-notification-global';
            notif.className = 'chat-notification-bubble';
            notif.innerHTML = `
                <div class="chat-notification-icon">💬</div>
                <div class="chat-notification-content">
                    <div class="chat-notification-title">Nuevo mensaje de chat</div>
                    <div class="chat-notification-message" id="notification-message-global"></div>
                </div>
                <button class="chat-notification-close" onclick="cerrarNotificacionGlobal()">×</button>
            `;
            document.body.appendChild(notif);
        }

        const message = document.getElementById('notification-message-global');
        if (nuevos === 1) {
            message.textContent = 'Tienes 1 mensaje nuevo';
        } else {
            message.textContent = `Tienes ${nuevos} mensajes nuevos`;
        }

        notif.style.display = 'flex';

        // Auto-ocultar después de 10 segundos
        clearTimeout(notificationTimeout);
        notificationTimeout = setTimeout(() => {
            cerrarNotificacionGlobal();
        }, 10000);

        // Click para ir al chat
        notif.onclick = (e) => {
            if (!e.target.classList.contains('chat-notification-close')) {
                window.location.href = '/chat/panel';
            }
        };
    }

    function cerrarNotificacionGlobal() {
        const notif = document.getElementById('chat-notification-global');
        if (notif) {
            notif.style.display = 'none';
        }
        clearTimeout(notificationTimeout);
    }

    function reproducirSonidoNotificacion() {
        try {
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }

            // Primer tono
            const oscillator1 = audioContext.createOscillator();
            const gainNode1 = audioContext.createGain();

            oscillator1.connect(gainNode1);
            gainNode1.connect(audioContext.destination);

            oscillator1.frequency.value = 800;
            oscillator1.type = 'sine';

            gainNode1.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode1.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

            oscillator1.start(audioContext.currentTime);
            oscillator1.stop(audioContext.currentTime + 0.15);

            // Segundo tono
            const oscillator2 = audioContext.createOscillator();
            const gainNode2 = audioContext.createGain();

            oscillator2.connect(gainNode2);
            gainNode2.connect(audioContext.destination);

            oscillator2.frequency.value = 1000;
            oscillator2.type = 'sine';

            gainNode2.gain.setValueAtTime(0.3, audioContext.currentTime + 0.1);
            gainNode2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

            oscillator2.start(audioContext.currentTime + 0.1);
            oscillator2.stop(audioContext.currentTime + 0.3);
        } catch (err) {
            console.log('Error al generar sonido:', err);
        }
    }
</script>

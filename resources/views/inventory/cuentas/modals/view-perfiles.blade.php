{{-- Modal para ver perfiles de una cuenta --}}
<x-modal name="view-perfiles" :show="false" maxWidth="6xl">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-users me-2"></i>Perfiles de la Cuenta: <span id="viewPerfilesCuentaNombre"></span>
        </h5>
        <button type="button" class="btn-close" @click="$dispatch('close-modal', 'view-perfiles')"></button>
    </div>
    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
        <div id="perfiles-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando perfiles...</p>
        </div>
        <div id="perfiles-container" style="display: none;">
            {{-- Aquí se cargará el contenido via AJAX --}}
        </div>
        <div id="perfiles-error" class="alert alert-danger" style="display: none;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="perfiles-error-message"></span>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'view-perfiles')">
            <i class="fas fa-times me-1"></i>Cerrar
        </button>
    </div>
</x-modal>

{{-- Modales anidados --}}
@include('inventory.cuentas.modals.edit-profile')
@include('inventory.cuentas.modals.confirm-move-user')
@include('inventory.cuentas.modals.confirm-move-user-mesa')
@include('inventory.cuentas.modals.confirm-delete-user')
@include('inventory.cuentas.modals.confirm-move-all-mesa')
@include('inventory.cuentas.modals.confirm-move-all-disperso')
@include('inventory.cuentas.modals.confirm-move-user-otro-servicio')

<script>
// Función global para copiar mensaje (debe estar disponible cuando se carga el contenido AJAX)
function copyMessage(idcue, usuariocue, contrasenacue, numeroper, pinper, bot, servicio) {
    var message = "";
    var esSpotify = (servicio || '').toString().trim().toUpperCase() === 'SPOTIFY';

    // Verificar si es Spotify
    if (esSpotify) {
        // Mensaje especial para Spotify
        message = "🎵 *SPOTIFY PREMIUM* 🎵\n\n";

        // Spotify: usuario y contraseña se copian desde pinper
        if (pinper && pinper.trim() !== '') {
            var usuarioSpotify = pinper;
            var claveSpotify = pinper;

            if (pinper.includes('|')) {
                var credenciales = pinper.split('|');
                usuarioSpotify = (credenciales[0] || '').trim();
                claveSpotify = (credenciales[1] || '').trim();
            }

            message += "👤 *Usuario:* " + usuarioSpotify + "\n";
            message += "🔑 *Contraseña:* " + claveSpotify + "\n";
        } else {
            message += "⚠️ *Perfil sin PIN configurado*\n";
            message += "Usuario: " + usuariocue + "\n";
            message += "Contraseña: " + contrasenacue + "\n";
        }

        message += "📍 *Perfil:* #" + numeroper + "\n";

        message += "\n*Prohibido:* Modificar perfil o contraseña.\n";
        message += "¡Gracias por tu confianza! 🎶";

    } else {
        // Mensaje estándar para otros servicios
        var servicioNombre = idcue.replace(/[^a-zA-Z]/g, '');
        message = "*" + servicioNombre + "*\n";
        message += "Usuario: " + usuariocue + "\n";
        message += "Clave: " + contrasenacue + "\n";
        message += "PIN de perfil Nro " + numeroper + ": " + pinper + "\n";
        message += "*Prohibido:* Modificar perfiles o contraseñas.\n";

        // Verificar si el bot no está vacío
        if (bot && bot.trim() !== "") {
            message += "\n\n*Nota importante:*\n";
            message += "Te daré acceso al bot de códigos. Si en algún momento se te solicita un código de acceso (Hogar), puedes obtenerlo ingresando al siguiente enlace:\n";
            message += bot + "\n";
            message += "¡Gracias por tu confianza!";
        }
    }

    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = message;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand("copy");
    document.body.removeChild(tempTextArea);

    // Mostrar mensaje de confirmación
    showTemporaryAlert('success', 'El mensaje se ha copiado al portapapeles');
}

// Función para cargar perfiles via AJAX
function loadPerfilesInModal(cuentaId, cuentaNombre) {
    // Actualizar el título
    document.getElementById('viewPerfilesCuentaNombre').textContent = cuentaNombre;

    // Mostrar loading
    document.getElementById('perfiles-loading').style.display = 'block';
    document.getElementById('perfiles-container').style.display = 'none';
    document.getElementById('perfiles-error').style.display = 'none';

    // Abrir el modal usando el sistema de eventos
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'view-perfiles' }));

    // Cargar datos via AJAX
    const url = "{{ route('cuentas.loadPerfiles', ':id') }}".replace(':id', cuentaId);
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error al cargar los perfiles');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Ocultar loading
            document.getElementById('perfiles-loading').style.display = 'none';

            // Mostrar contenido
            document.getElementById('perfiles-container').innerHTML = data.html;
            document.getElementById('perfiles-container').style.display = 'block';

            // Inicializar event listeners después de cargar el contenido
            initializePerfilesEventListeners();
        } else {
            throw new Error(data.message || 'Error al cargar los perfiles');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('perfiles-loading').style.display = 'none';
        document.getElementById('perfiles-error').style.display = 'block';
        document.getElementById('perfiles-error-message').textContent = error.message;
    });
}

// Inicializar event listeners para botones dentro del contenido cargado
function initializePerfilesEventListeners() {
    // Total de usuarios activos
    var totalUsuarios = 0;
    var usuariosActivos = document.querySelectorAll('#perfiles-container .usuarios-activos');
    usuariosActivos.forEach(function(item) {
        totalUsuarios += parseInt(item.textContent) || 0;
    });
    document.getElementById('totalUsuariosActivos').textContent = totalUsuarios;

    // Event listeners para botones de editar perfil
    document.querySelectorAll('#perfiles-container .btn-edit-profile').forEach(function(button) {
        button.addEventListener('click', function() {
            const idper = this.getAttribute('data-idper');
            const numeroper = this.getAttribute('data-numeroper');
            const pinper = this.getAttribute('data-pinper');

            document.getElementById('edit_profile_id').value = idper;
            document.getElementById('edit_profile_numero').value = numeroper;
            document.getElementById('edit_profile_pin').value = pinper;

            // Abrir modal anidado
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-profile' }));
        });
    });

    // Event listeners para botones de mover usuario
    document.querySelectorAll('#perfiles-container .btn-move-user').forEach(function(button) {
        button.addEventListener('click', function() {
            const iddet = this.getAttribute('data-iddet');
            const nombre = this.getAttribute('data-nombre');

            document.getElementById('confirm_move_user_id').value = iddet;
            document.getElementById('confirm_move_user_name').textContent = nombre;
            document.getElementById('confirm_move_user_form').action = "{{ route('usuarios.moverUsuario', ':id') }}".replace(':id', iddet);

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-move-user' }));
        });
    });

    // Event listeners para botones de mover usuario a mesa
    document.querySelectorAll('#perfiles-container .btn-move-user-mesa').forEach(function(button) {
        button.addEventListener('click', function() {
            const iddet = this.getAttribute('data-iddet');
            const nombre = this.getAttribute('data-nombre');

            document.getElementById('confirm_move_mesa_user_id').value = iddet;
            document.getElementById('confirm_move_mesa_user_name').textContent = nombre;
            document.getElementById('confirm_move_mesa_form').action = "{{ route('usuarios.moverUsuarioMesa', ':id') }}".replace(':id', iddet);

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-move-user-mesa' }));
        });
    });

    // Event listeners para botones de eliminar usuario
    document.querySelectorAll('#perfiles-container .btn-delete-user').forEach(function(button) {
        button.addEventListener('click', function() {
            const iddet = this.getAttribute('data-iddet');
            const nombre = this.getAttribute('data-nombre');

            document.getElementById('confirm_delete_user_id').value = iddet;
            document.getElementById('confirm_delete_user_name').textContent = nombre;
            document.getElementById('confirm_delete_user_form').action = "{{ route('usuarios.destroy', ':id') }}".replace(':id', iddet);

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-delete-user' }));
        });
    });

    // Event listeners para botones de mover todos a mesa
    document.querySelectorAll('#perfiles-container .btn-move-all-mesa').forEach(function(button) {
        button.addEventListener('click', function() {
            const cuentaId = this.getAttribute('data-cuenta-id');
            const cuentaNombre = this.getAttribute('data-cuenta-nombre');

            document.getElementById('confirm_move_all_mesa_id').value = cuentaId;
            document.getElementById('confirm_move_all_mesa_cuenta').textContent = cuentaNombre;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-move-all-mesa' }));
        });
    });

    // Event listeners para botones de dispersar todos
    document.querySelectorAll('#perfiles-container .btn-move-all-disperso').forEach(function(button) {
        button.addEventListener('click', function() {
            const cuentaId = this.getAttribute('data-cuenta-id');
            const cuentaNombre = this.getAttribute('data-cuenta-nombre');

            document.getElementById('confirm_move_all_disperso_id').value = cuentaId;
            document.getElementById('confirm_move_all_disperso_cuenta').textContent = cuentaNombre;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-move-all-disperso' }));
        });
    });

    // Event listeners para botones de mover a otro servicio
    document.querySelectorAll('#perfiles-container .btn-move-user-otro-servicio').forEach(function(button) {
        button.addEventListener('click', function() {
            const iddet = this.getAttribute('data-iddet');
            const nombre = this.getAttribute('data-nombre');
            const servicio = this.getAttribute('data-servicio');

            document.getElementById('confirm_move_otro_servicio_user_id').value = iddet;
            document.getElementById('confirm_move_otro_servicio_user_name').textContent = nombre;
            document.getElementById('confirm_move_otro_servicio_actual').textContent = servicio;
            document.getElementById('confirm_move_otro_servicio_form').action =
                "{{ route('usuarios.moverUsuarioOtroServicio', ':id') }}".replace(':id', iddet);

            // Filtrar el servicio actual de las opciones
            const selectServicio = document.getElementById('idser_destino');
            Array.from(selectServicio.options).forEach(option => {
                if (option.value === servicio) {
                    option.disabled = true;
                    option.textContent = option.value + ' (Servicio actual)';
                } else {
                    option.disabled = false;
                    // Restaurar el texto original sin el sufijo
                    if (option.value !== '') {
                        option.textContent = option.value;
                    }
                }
            });

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-move-user-otro-servicio' }));
        });
    });
}

// Función para enviar el formulario de editar perfil
function submitEditProfile() {
    const idper = document.getElementById('edit_profile_id').value;
    const pinper = document.getElementById('edit_profile_pin').value;

    if (!pinper || pinper.trim() === '') {
        showTemporaryAlert('warning', 'Por favor ingresa un PIN válido');
        return;
    }

    const url = "{{ route('perfil.update', ':idper') }}".replace(':idper', idper);

    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            pinper: pinper
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Cerrar modal anidado
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-profile' }));

            // Mostrar mensaje de éxito
            showTemporaryAlert('success', data.message);

            // Recargar el contenido de perfiles
            const cuentaId = document.querySelector('#perfiles-container .btn-move-all-mesa')?.getAttribute('data-cuenta-id');
            const cuentaNombre = document.getElementById('viewPerfilesCuentaNombre').textContent;

            if (cuentaId) {
                // Recargar contenido sin cerrar el modal principal
                setTimeout(() => {
                    document.getElementById('perfiles-container').style.display = 'none';
                    document.getElementById('perfiles-loading').style.display = 'block';

                    const reloadUrl = "{{ route('cuentas.loadPerfiles', ':id') }}".replace(':id', cuentaId);
                    fetch(reloadUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('perfiles-loading').style.display = 'none';
                            document.getElementById('perfiles-container').innerHTML = data.html;
                            document.getElementById('perfiles-container').style.display = 'block';
                            initializePerfilesEventListeners();
                        }
                    });
                }, 500);
            }
        } else {
            showTemporaryAlert('danger', data.message || 'Error al actualizar el perfil');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTemporaryAlert('danger', 'Error al actualizar el perfil. Por favor intenta nuevamente.');
    });
}
</script>

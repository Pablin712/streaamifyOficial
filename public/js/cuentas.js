document.addEventListener("DOMContentLoaded", function() {
    if (window.location.href.includes('#tabla-perfiles')) {
        document.getElementById('tabla-perfiles').scrollIntoView({
            behavior: 'smooth'
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    var editProfileModal = document.getElementById('editProfileModal');
    editProfileModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var actionUrl = button.getAttribute('data-action');
        var perfilId = button.getAttribute('data-id');
        var pinper = button.getAttribute('data-pin');

        var modalForm = editProfileModal.querySelector('#editProfileForm');
        modalForm.setAttribute('action', actionUrl);
        modalForm.querySelector('#perfilId').value = perfilId;
        modalForm.querySelector('#pinper').value = pinper;
    });
});
document.addEventListener('DOMContentLoaded', function() {
    var totalUsuarios = 0;
    var usuariosActivos = document.querySelectorAll('.usuarios-activos');
    usuariosActivos.forEach(function(item) {
        totalUsuarios += parseInt(item.textContent) || 0;
    });
    document.getElementById('totalUsuariosActivos').textContent = totalUsuarios;
});
function copyMessage(idcue, usuariocue, contrasenacue, numeroper, pinper) {
    var servicio = idcue.replace(/[^a-zA-Z]/g, '');
    var message = servicio + "\n";
    message += "Usuario: " + usuariocue + "\n";
    message += "Clave: " + contrasenacue + "\n";
    message += "PIN de perfil Nro " + numeroper + ": " + pinper;
    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = message;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand("copy");
    document.body.removeChild(tempTextArea);
    alert("El mensaje se ha copiado al portapapeles.");
}
$(document).ready(function() {
    $('#idcue').select2({
        placeholder: "Selecciona una Cuenta",
        allowClear: true
    });
});
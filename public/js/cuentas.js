document.addEventListener("DOMContentLoaded", function() {
    if (window.location.href.includes('#tabla-perfiles')) {
        document.getElementById('tabla-perfiles').scrollIntoView({
            behavior: 'smooth'
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    $('#editProfileModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var perfilId = button.data('id');
        var pinper = button.data('pin');
        var modal = $(this);
        modal.find('#perfilId').val(perfilId);
        modal.find('#pinper').val(pinper);
        var formAction = "{{ url('admin/perfil') }}/" + perfilId;
        modal.find('#editProfileForm').attr('action', formAction);
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
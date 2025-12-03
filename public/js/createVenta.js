// No necesitamos inicializar Select2 manualmente ya que el componente searchable-select lo hace automáticamente

// Función para agregar un detalle a la tabla
$("#guardarDetalleBtn").on("click", function () {
    // Obtener los valores del modal
    var cuenta = $("#selectCuenta").val();
    var perfil = $("#selectPerfil").val();
    var fechaVencimiento = $("#fechaVencimiento").val();
    var descripcion = $("#descripcion").val();
    var monto = parseFloat($("#monto").val());

    // Validar que todos los campos estén completos
    if (cuenta && perfil && fechaVencimiento && descripcion && monto) {
        // Calcular el nuevo total
        var totalVenta = parseFloat($("#total-venta").text()) + monto;

        // Crear una nueva fila con los datos del detalle
        var nuevaFila = `<tr>
            <td>${cuenta}</td>
            <td>${perfil}</td>
            <td>${descripcion}</td>
            <td>${fechaVencimiento}</td>
            <td>$${monto.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-warning btn-sm editarDetalleBtn"><i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                    <i class="fas fa-trash"></i>
                </button>
            </td>

        </tr>`;

        // Agregar la nueva fila a la tabla
        $("#tabla-detalles").append(nuevaFila);

        // Actualizar el total de la venta
        $("#total-venta").text(totalVenta.toFixed(2));

        // Limpiar los campos del modal
        $("#selectCuenta").val(null).trigger('change'); // Limpiar Select2
        $("#selectPerfil").val("");
        $("#fechaVencimiento").val("");
        $("#descripcion").val("");
        $("#monto").val("");

        // Cerrar el modal usando Alpine.js
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'agregar-detalle-modal' }));
    } else {
        alert("Por favor complete todos los campos.");
    }
});

// Eliminar fila de la tabla
$("#tabla-detalles").on("click", ".eliminarDetalleBtn", function () {
    // Obtener el monto de la fila a eliminar
    var montoEliminado = parseFloat(
        $(this).closest("tr").find("td").eq(4).text().replace("$", "")
    );

    // Restar el monto eliminado del total
    var totalVenta = parseFloat($("#total-venta").text()) - montoEliminado;

    // Eliminar la fila
    $(this).closest("tr").remove();

    // Actualizar el total de la venta
    $("#total-venta").text(totalVenta.toFixed(2));
});

// El componente searchable-select inicializa automáticamente los selects cuando se abre un modal
document
    .getElementById("form-venta")
    .addEventListener("submit", function (event) {
        event.preventDefault(); // Evitar que se envíe el formulario inmediatamente

        // Crear un arreglo para almacenar los detalles de venta
        let detalles = [];

        // Obtener todas las filas de la tabla #tabla-detalles (cada fila es un detalle de venta)
        document.querySelectorAll("#tabla-detalles tr").forEach(function (row) {
            // Obtener los valores de cada celda de la fila
            let cuenta = row.cells[0].innerText; // La primera celda es la Cuenta
            let perfil = row.cells[1].innerText; // La segunda celda es el Perfil
            let descripcion = row.cells[2].innerText; // La tercera celda es la Descripción
            let fechaVencimiento = row.cells[3].innerText; // La cuarta celda es la Fecha de Vencimiento
            let monto = parseFloat(
                row.cells[4].innerText.replace("$", "").trim()
            ); // La quinta celda es el Monto

            // Asegurarse de que los campos no estén vacíos (esto es opcional, según tu caso)
            if (cuenta && perfil && descripcion && fechaVencimiento && monto) {
                // Agregar cada detalle al arreglo
                detalles.push({
                    cuenta: cuenta,
                    perfil: perfil,
                    descripcion: descripcion,
                    fecha_vencimiento: fechaVencimiento,
                    monto: monto,
                });
            }
        });

        // Asignar los detalles serializados al campo oculto para enviarlos en el formulario
        document.getElementById("detalles_venta").value =
            JSON.stringify(detalles);

        // Ahora enviamos el formulario
        this.submit();
    });

$(document).ready(function () {
    // Maneja el evento de clic en el botón "Editar Detalle"
    $(document).on("click", ".editarDetalleBtn", function () {
        var row = $(this).closest("tr");
        var cuenta = row.find("td:eq(0)").text().split(":")[0].trim();
        var perfil = row.find("td:eq(1)").text().trim();
        var descripcion = row.find("td:eq(2)").text().trim();
        var fechaVencimiento = row.find("td:eq(3)").text().trim();
        var monto = row.find("td:eq(4)").text().replace("$", "").trim();

        $("#editarSelectCuenta").val(cuenta).trigger('change'); // Usar trigger para Select2
        $("#editarSelectPerfil").val(perfil);
        $("#editarDescripcion").val(descripcion);
        $("#editarFechaVencimiento").val(fechaVencimiento);
        $("#editarMonto").val(monto);

        $("#guardarCambiosDetalleBtn").data("row", row);

        // Abrir modal usando Alpine.js
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-detalle-modal' }));
    });

    // Maneja el evento de clic en el botón "Guardar Cambios"
    $("#guardarCambiosDetalleBtn").click(function () {
        var row = $(this).data("row");
        var cuenta = $("#editarSelectCuenta").val();
        var perfil = $("#editarSelectPerfil").val();
        var descripcion = $("#editarDescripcion").val();
        var fechaVencimiento = $("#editarFechaVencimiento").val();
        var monto = $("#editarMonto").val();

        var cuentaText = $("#editarSelectCuenta option:selected")
            .text()
            .split(":")[0]
            .trim();
        row.find("td:eq(0)").text(cuentaText).data("id", cuenta);
        row.find("td:eq(1)").text(perfil);
        row.find("td:eq(2)").text(descripcion);
        row.find("td:eq(3)").text(fechaVencimiento);
        row.find("td:eq(4)").text("$" + parseFloat(monto).toFixed(2));

        // Actualizar total de venta
        actualizarTotalVenta();

        // Cerrar modal usando Alpine.js
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-detalle-modal' }));
    });
});

// Función auxiliar para actualizar el total
function actualizarTotalVenta() {
    let total = 0;
    $('#tabla-detalles tr').each(function() {
        const monto = parseFloat($(this).find('td').eq(4).text().replace('$', '').trim());
        if (!isNaN(monto)) {
            total += monto;
        }
    });
    $('#total-venta').text(total.toFixed(2));
}

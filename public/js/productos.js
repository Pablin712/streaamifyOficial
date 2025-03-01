document.addEventListener("DOMContentLoaded", function () {
    // Selecciona todos los botones con la clase 'btn-ver-producto'
    const botonesVerProducto = document.querySelectorAll(".btn-ver-producto");

    botonesVerProducto.forEach(boton => {
        boton.addEventListener("click", function () {
            // Obtiene los datos del producto desde los atributos del botón
            const id = this.getAttribute("data-id");
            const codigo = this.getAttribute("data-codigo");
            const nombre = this.getAttribute("data-nombre");
            const precio = this.getAttribute("data-precio");
            const descripcion = this.getAttribute("data-descripcion");
            const categoria = this.getAttribute("data-categoria");
            const tipo = this.getAttribute("data-tipo");
            const estado = this.getAttribute("data-activo");
            const foto = this.getAttribute("data-foto");

            // Asigna los datos al modal
            document.getElementById("modalCodigo").textContent = codigo;
            document.getElementById("modalNombre").textContent = nombre;
            document.getElementById("modalPrecio").textContent = precio;
            document.getElementById("modalDescripcion").textContent = descripcion;
            document.getElementById("modalCategoria").textContent = categoria;
            document.getElementById("modalTipo").textContent = tipo;
            document.getElementById("modalEstado").innerHTML = estado === "Activo" ? 
                '<span class="badge bg-success">Activo</span>' : 
                '<span class="badge bg-danger">Inactivo</span>';

            // Asigna la imagen si existe, sino pone una imagen por defecto
            const imagenElemento = document.getElementById("modalImagen");
            if (foto && foto !== "public/") {
                imagenElemento.src = foto;
            } else {
                imagenElemento.src = "https://via.placeholder.com/200"; // Imagen por defecto
            }

            // Muestra el modal
            const modal = new bootstrap.Modal(document.getElementById("modalProducto"));
            modal.show();
        });
    });
});
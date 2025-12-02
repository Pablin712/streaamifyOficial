<x-modal name="createEmpleadoModal" :show="false" maxWidth="lg">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-user-plus me-2"></i>Crear Nuevo Empleado
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createEmpleadoModal' }))"></button>
    </div>
    <form id="createEmpleadoForm" onsubmit="submitCreate(event)" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="row g-3">
                <!-- Nombre -->
                <div class="col-md-6">
                    <label for="create_nombreemp" class="form-label required">
                        <i class="fas fa-user me-1"></i>Nombre
                    </label>
                    <input type="text" class="form-control" id="create_nombreemp" name="nombreemp" required>
                </div>

                <!-- Teléfono -->
                <div class="col-md-6">
                    <label for="create_telefonoemp" class="form-label required">
                        <i class="fas fa-phone me-1"></i>Teléfono
                    </label>
                    <input type="text" class="form-control" id="create_telefonoemp" name="telefonoemp" required>
                </div>

                <!-- Usuario -->
                <div class="col-md-6">
                    <label for="create_usuarioemp" class="form-label required">
                        <i class="fas fa-user-circle me-1"></i>Usuario
                    </label>
                    <input type="text" class="form-control" id="create_usuarioemp" name="usuarioemp" required>
                </div>

                <!-- Contraseña -->
                <div class="col-md-6">
                    <label for="create_passwordemp" class="form-label required">
                        <i class="fas fa-lock me-1"></i>Contraseña
                    </label>
                    <input type="password" class="form-control" id="create_passwordemp" name="passwordemp" required>
                </div>

                <!-- Email -->
                <div class="col-md-12">
                    <label for="create_email" class="form-label required">
                        <i class="fas fa-envelope me-1"></i>Email
                    </label>
                    <input type="email" class="form-control" id="create_email" name="email" required>
                </div>

                <!-- Foto -->
                <div class="col-md-12">
                    <label for="create_foto_url" class="form-label">
                        <i class="fas fa-camera me-1"></i>Foto de Perfil (opcional)
                    </label>
                    <input type="file" class="form-control" id="create_foto_url" name="foto_url" accept="image/*">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Empleado
            </button>
        </div>
    </form>
</x-modal>

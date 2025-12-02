<x-modal name="editEmpleadoModal" :show="false" maxWidth="lg">
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-user-edit me-2"></i>Editar Empleado
        </h5>
        <button type="button" class="btn-close" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editEmpleadoModal' }))"></button>
    </div>
    <form id="editEmpleadoForm" onsubmit="submitEdit(event)" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="idemp" id="edit_empleado_id">
        <div class="modal-body">
            <div class="row g-3">
                <!-- Foto actual -->
                <div class="col-md-12 text-center">
                    <div id="edit_foto_preview"></div>
                </div>

                <!-- Nombre -->
                <div class="col-md-6">
                    <label for="edit_nombreemp" class="form-label required">
                        <i class="fas fa-user me-1"></i>Nombre
                    </label>
                    <input type="text" class="form-control" id="edit_nombreemp" name="nombreemp" required>
                </div>

                <!-- Teléfono -->
                <div class="col-md-6">
                    <label for="edit_telefonoemp" class="form-label required">
                        <i class="fas fa-phone me-1"></i>Teléfono
                    </label>
                    <input type="text" class="form-control" id="edit_telefonoemp" name="telefonoemp" required>
                </div>

                <!-- Usuario -->
                <div class="col-md-6">
                    <label for="edit_usuarioemp" class="form-label required">
                        <i class="fas fa-user-circle me-1"></i>Usuario
                    </label>
                    <input type="text" class="form-control" id="edit_usuarioemp" name="usuarioemp" required>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label for="edit_email" class="form-label required">
                        <i class="fas fa-envelope me-1"></i>Email
                    </label>
                    <input type="email" class="form-control" id="edit_email" name="email" required>
                </div>

                <!-- Contraseña -->
                <div class="col-md-12">
                    <label for="edit_passwordemp" class="form-label">
                        <i class="fas fa-lock me-1"></i>Nueva Contraseña (opcional)
                    </label>
                    <input type="password" class="form-control" id="edit_passwordemp" name="passwordemp">
                    <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                </div>

                <!-- Foto -->
                <div class="col-md-12">
                    <label for="edit_foto_url" class="form-label">
                        <i class="fas fa-camera me-1"></i>Cambiar Foto de Perfil (opcional)
                    </label>
                    <input type="file" class="form-control" id="edit_foto_url" name="foto_url" accept="image/*">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Empleado
            </button>
        </div>
    </form>
</x-modal>

<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');

// Agregar data-attribute para perfiles del sistema
if (isset($perfil) && ($perfil['sistema'] ?? false)): ?>
    <script>document.body.dataset.perfilSistema = 'true';</script>
<?php endif; ?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/perfiles" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        <?php if (isset($perfil)): ?>
        <a href="/perfiles-modulos?perfil=<?= $perfil['id_perfil'] ?>" class="btn btn-info">
            <i class="fas fa-key"></i> Gestionar Permisos
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="form-container">
    <form method="POST" class="form-modern" id="perfilForm">
        <div class="form-sections">
            <!-- Información Básica -->
            <div class="form-section">
                <h3>Información del Perfil</h3>
                
                <div class="form-group">
                    <label for="perfil_descripcion" class="required">Descripción:</label>
                    <input type="text" name="perfil_descripcion" id="perfil_descripcion" 
                           value="<?= $perfil['perfil_descripcion'] ?? '' ?>" 
                           required maxlength="100" class="form-control"
                           placeholder="Ej: Administrador, Recepcionista, Operador...">
                    <small class="form-text text-muted">
                        Nombre descriptivo del perfil. <span id="desc_counter">0</span>/100 caracteres
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="perfil_observaciones">Observaciones:</label>
                    <textarea name="perfil_observaciones" id="perfil_observaciones" 
                              rows="4" maxlength="500" class="form-control" 
                              placeholder="Descripción detallada de las responsabilidades y alcance del perfil..."><?= $perfil['perfil_observaciones'] ?? '' ?></textarea>
                    <small class="form-text text-muted">
                        Descripción detallada del perfil y sus responsabilidades. <span id="obs_counter">0</span>/500 caracteres
                    </small>
                </div>
            </div>

            <!-- Configuraciones Avanzadas -->
            <div class="form-section">
                <h3>Configuraciones del Perfil</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="perfil_nivel">Nivel de Acceso:</label>
                        <select name="perfil_nivel" id="perfil_nivel" class="form-control">
                            <option value="1" <?= (isset($perfil) && $perfil['perfil_nivel'] == 1) ? 'selected' : '' ?>>Básico</option>
                            <option value="2" <?= (isset($perfil) && $perfil['perfil_nivel'] == 2) ? 'selected' : '' ?>>Intermedio</option>
                            <option value="3" <?= (isset($perfil) && $perfil['perfil_nivel'] == 3) ? 'selected' : '' ?>>Avanzado</option>
                            <option value="4" <?= (isset($perfil) && $perfil['perfil_nivel'] == 4) ? 'selected' : '' ?>>Administrador</option>
                        </select>
                        <small class="form-text text-muted">
                            Nivel jerárquico del perfil en el sistema
                        </small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="perfil_color">Color Identificativo:</label>
                        <div class="color-input">
                            <input type="color" name="perfil_color" id="perfil_color" 
                                   value="<?= $perfil['perfil_color'] ?? '#007bff' ?>" 
                                   class="form-control color-picker">
                            <span class="color-preview" id="colorPreview"></span>
                        </div>
                        <small class="form-text text-muted">
                            Color para identificar visualmente el perfil
                        </small>
                    </div>
                </div>

                <!-- Configuraciones de Seguridad -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="perfil_puede_crear_usuarios" id="perfil_puede_crear_usuarios" 
                                   value="1" <?= ($perfil['perfil_puede_crear_usuarios'] ?? 0) ? 'checked' : '' ?> 
                                   class="form-check-input">
                            <label class="form-check-label" for="perfil_puede_crear_usuarios">
                                Puede crear usuarios
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Permite crear y gestionar otros usuarios
                        </small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="perfil_puede_modificar_perfiles" id="perfil_puede_modificar_perfiles" 
                                   value="1" <?= ($perfil['perfil_puede_modificar_perfiles'] ?? 0) ? 'checked' : '' ?> 
                                   class="form-check-input">
                            <label class="form-check-label" for="perfil_puede_modificar_perfiles">
                                Puede modificar perfiles
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Permite gestionar perfiles y permisos
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="perfil_acceso_completo" id="perfil_acceso_completo" 
                                   value="1" <?= ($perfil['perfil_acceso_completo'] ?? 0) ? 'checked' : '' ?> 
                                   class="form-check-input">
                            <label class="form-check-label" for="perfil_acceso_completo">
                                Acceso completo al sistema
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Otorga acceso a todos los módulos automáticamente
                        </small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="perfil_solo_lectura" id="perfil_solo_lectura" 
                                   value="1" <?= ($perfil['perfil_solo_lectura'] ?? 0) ? 'checked' : '' ?> 
                                   class="form-check-input">
                            <label class="form-check-label" for="perfil_solo_lectura">
                                Solo lectura
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Restringe a operaciones de consulta únicamente
                        </small>
                    </div>
                </div>
            </div>

            <!-- Estado del Perfil -->
            <?php if (isset($perfil)): ?>
            <div class="form-section">
                <h3>Estado y Activación</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="perfil_estado" id="perfil_estado" 
                                   value="1" <?= ($perfil['perfil_estado'] ?? 1) ? 'checked' : '' ?> 
                                   class="form-check-input">
                            <label class="form-check-label" for="perfil_estado">
                                Perfil activo
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Solo los perfiles activos pueden ser asignados a usuarios
                        </small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="perfil_predeterminado" id="perfil_predeterminado" 
                                   value="1" <?= ($perfil['perfil_predeterminado'] ?? 0) ? 'checked' : '' ?> 
                                   class="form-check-input">
                            <label class="form-check-label" for="perfil_predeterminado">
                                Perfil predeterminado
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Se asigna automáticamente a usuarios nuevos
                        </small>
                    </div>
                </div>
                
                <!-- Información del sistema (solo lectura) -->
                <?php if ($perfil['sistema'] ?? false): ?>
                <div class="system-warning">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perfil del Sistema</strong><br>
                        Este es un perfil crítico del sistema. Algunas configuraciones no pueden ser modificadas para mantener la seguridad y estabilidad.
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Botones de acción -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= isset($perfil) ? 'Actualizar Perfil' : 'Crear Perfil' ?>
            </button>
            <a href="/perfiles" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancelar
            </a>
            <?php if (isset($perfil)): ?>
            <a href="/perfiles-modulos?perfil=<?= $perfil['id_perfil'] ?>" class="btn btn-info">
                <i class="fas fa-key"></i>
                Gestionar Permisos
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php $this->endSection(); ?>
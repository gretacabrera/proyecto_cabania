<?php $this->layout('layouts/main', ['title' => $title]) ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?= $title ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST" id="estadoProductoForm">
                        <div class="mb-3">
                            <label for="estadoproducto_descripcion" class="form-label">
                                Descripción <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="estadoproducto_descripcion" 
                                   name="estadoproducto_descripcion"
                                   value="<?= htmlspecialchars($estado_producto['estadoproducto_descripcion'] ?? '') ?>"
                                   required 
                                   maxlength="100"
                                   placeholder="Ingrese la descripción del estado">
                            <div class="form-text">Máximo 100 caracteres</div>
                        </div>

                        <?php if (isset($estado_producto)): ?>
                            <div class="mb-3">
                                <label class="form-label">Estado Actual</label>
                                <div>
                                    <?php if ($estado_producto['estadoproducto_estado'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between">
                            <a href="/proyecto_cabania/estados-productos" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?= isset($estado_producto) ? 'Actualizar' : 'Crear' ?> Estado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
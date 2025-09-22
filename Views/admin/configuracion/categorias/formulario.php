<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= isset($categoria) ? '/categorias/' . $categoria['id_categoria'] . '/edit' : '/categorias/create' ?>">
                        <div class="form-group">
                            <label for="categoria_nombre">Nombre de la Categoría *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="categoria_nombre" 
                                   name="categoria_nombre" 
                                   value="<?= htmlspecialchars($categoria['categoria_nombre'] ?? '') ?>"
                                   required 
                                   maxlength="100">
                            <small class="form-text text-muted">Máximo 100 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label for="categoria_descripcion">Descripción</label>
                            <textarea class="form-control" 
                                      id="categoria_descripcion" 
                                      name="categoria_descripcion" 
                                      rows="3"
                                      maxlength="500"><?= htmlspecialchars($categoria['categoria_descripcion'] ?? '') ?></textarea>
                            <small class="form-text text-muted">Máximo 500 caracteres (opcional)</small>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="categoria_estado" 
                                       name="categoria_estado" 
                                       value="1" 
                                       <?= (!isset($categoria) || $categoria['categoria_estado'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="categoria_estado">
                                    Categoría Activa
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> 
                                <?= isset($categoria) ? 'Actualizar' : 'Guardar' ?> Categoría
                            </button>
                            <a href="/categorias" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
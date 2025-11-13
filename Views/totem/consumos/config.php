<?php
$this->extend('layouts/totem');
$this->section('title', $title);
$this->section('content');
?>

<div class="totem-header text-center">
    <h1 class="display-4 mb-0">
        <i class="fas fa-home text-primary"></i> Bienvenido al Sistema de Pedidos
    </h1>
    <p class="lead text-muted mt-2">Configure el tótem ingresando el código de su cabaña</p>
</div>

<div class="totem-card">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center mb-4">
                <i class="fas fa-qrcode fa-5x text-primary mb-3"></i>
                <h3>Configuración Inicial</h3>
            </div>
            
            <form id="formConfigTotem">
                <div class="mb-4">
                    <label for="cabania_codigo" class="form-label fs-5 fw-bold">
                        <i class="fas fa-key"></i> Código de Cabaña
                    </label>
                    <input type="text" 
                           id="cabania_codigo" 
                           name="cabania_codigo" 
                           class="form-control form-control-lg text-center text-uppercase" 
                           placeholder="Ej: CAB001"
                           required
                           autocomplete="off"
                           autofocus
                           style="font-size: 2rem; letter-spacing: 3px;">
                    <small class="form-text text-muted">
                        Ingrese el código que se encuentra en su cabaña
                    </small>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-totem">
                        <i class="fas fa-check-circle"></i> Configurar Tótem
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Si no conoce su código de cabaña, consulte con la recepción
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formConfigTotem').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const codigo = document.getElementById('cabania_codigo').value.trim().toUpperCase();
    
    if (!codigo) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe ingresar el código de cabaña',
            confirmButtonColor: '#667eea'
        });
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Configurando...',
        text: 'Verificando código de cabaña',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar al servidor
    fetch('<?= url('/totem/configurar') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'cabania_codigo=' + encodeURIComponent(codigo)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Configurado!',
                text: 'Tótem configurado para: ' + data.cabania,
                confirmButtonColor: '#28a745',
                timer: 2000
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión. Intente nuevamente.',
            confirmButtonColor: '#d33'
        });
    });
});

// Convertir a mayúsculas automáticamente
document.getElementById('cabania_codigo').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});
</script>

<?php $this->endSection(); ?>

<?php
use App\Core\Auth;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isAuthenticated = Auth::check();
$currentUser = $isAuthenticated ? Auth::user() : null;
?>

<!-- Incluir estilos del componente menú -->
<link href="<?= $this->asset('assets/css/menu-component.css') ?>" rel="stylesheet">

<!-- Barra de navegación minimalista y sobria -->
<nav class="navbar navbar-expand-lg navbar-minimal" style="
    background: #ffffff;
    border-bottom: 1px solid #e5e5e5;
    position: sticky;
    top: 0;
    z-index: 1030;
">
    <div class="container-fluid">
        <!-- Logo y marca -->
        <a class="navbar-brand d-flex align-items-center" href="<?= $this->url('/') ?>" style="color: #2c3e50; font-weight: 600;">
            <i class="fas fa-mountain me-2" style="color: #007bff; margin-right: 0.5rem;"></i>
            Casa de Palos
        </a>

        <!-- Botón hamburguesa para móvil -->
        <button class="navbar-toggler" type="button" id="publicMenuToggle"
                aria-controls="publicNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Enlaces de navegación -->
        <div class="collapse navbar-collapse" id="publicNavbar">
            <!-- Enlaces principales (derecha) -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $this->url('/') ?>">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $this->url('/catalogo') ?>">Catálogo</a>
                </li>
                
                <?php if ($isAuthenticated): ?>
                    <!-- Usuario autenticado -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                            <img src="<?= $this->asset('imagenes/home.png') ?>" alt="Avatar" class="user-avatar">
                            Usuario: <?= htmlspecialchars($currentUser) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <h6 class="dropdown-header">Mi Cuenta</h6>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= $this->url('/mis-reservas') ?>">
                                <i class="fas fa-calendar-alt me-2"></i>Mis Reservas
                            </a>
                            <a class="dropdown-item" href="<?= $this->url('/auth/change-password') ?>">
                                <i class="fas fa-key me-2"></i>Cambiar Contraseña
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= $this->url('/auth/logout') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <!-- Botón de login (sin icono) -->
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary" href="<?= $this->url('/auth/login') ?>">Iniciar Sesión</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Espaciador para el navbar pegajoso -->
<div style="height: 20px;"></div>
<?php
use App\Core\Auth;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isAuthenticated = Auth::check();
$currentUser = $isAuthenticated ? Auth::user() : null;
?>

<!-- Menú de navegación público -->
<nav class="public-navbar">
    <div class="container">
        <!-- Brand/Logo -->
        <div class="navbar-brand">
            <a href="<?= $this->url('/') ?>" class="brand-link">
                <i class="fas fa-mountain brand-icon"></i>
                <span class="brand-text">Casa de Palos</span>
            </a>
        </div>
        
        <!-- Botón hamburger para móvil -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#publicNavbar">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <!-- Contenido del menú -->
        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav ml-auto">
                <!-- Enlaces públicos -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= $this->url('/') ?>">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $this->url('/catalogo') ?>">
                        <i class="fas fa-search"></i> Catálogo
                    </a>
                </li>
                
                <?php if ($isAuthenticated): ?>
                    <!-- Usuario autenticado -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            <?= $this->escape($currentUser) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="<?= $this->url('/reservas') ?>">
                                <i class="fas fa-calendar-alt"></i> Mis Reservas
                            </a>
                            <a class="dropdown-item" href="<?= $this->url('/usuarios/profile') ?>">
                                <i class="fas fa-user-edit"></i> Mi Perfil
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= $this->url('/auth/logout') ?>">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <!-- Usuario no autenticado -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/auth/login') ?>">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-register" href="<?= $this->url('/auth/register') ?>">
                            <i class="fas fa-user-plus"></i> Registrarse
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
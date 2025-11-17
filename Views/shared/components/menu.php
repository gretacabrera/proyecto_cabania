<?php
use App\Core\Auth;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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
        <button class="navbar-toggler" type="button" id="menuToggle"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Enlaces de navegación -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Enlaces principales (derecha) -->
            <ul class="navbar-nav ml-auto">
                <!-- Catálogo para usuarios no autenticados y huéspedes -->
                <?php if (!Auth::check()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $this->url('/catalogo') ?>">Catálogo</a>
                </li>
                <?php endif; ?>

                <!-- Módulos del usuario autenticado organizados por menú -->
                <?php if (Auth::check()): ?>
                    <?php 
                    // Menú específico para perfil huésped
                    $userProfile = strtolower(Auth::getUserProfile() ?? '');
                    if ($userProfile === 'huesped'): 
                    ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/catalogo') ?>">Catálogo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/reservas') ?>">Reservas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/huesped/consumos') ?>">Consumos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/ingresos') ?>">Ingresos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/salidas') ?>">Salidas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/comentarios') ?>">Comentarios</a>
                        </li>
                    <?php else: ?>
                    <?php 
                    $userModules = Auth::getUserModules();
                    $groupedModules = [];
                    $modulesWithoutMenu = [];
                    
                    // Separar módulos por menú y módulos sin menú
                    foreach ($userModules as $module) {
                        if ($module['menu_nombre'] && !empty(trim($module['menu_nombre']))) {
                            // Módulo con menú asignado
                            $menuName = $module['menu_nombre'];
                            $groupedModules[$menuName][] = $module;
                        } else {
                            // Módulo sin menú asignado
                            $modulesWithoutMenu[] = $module;
                        }
                    }
                    
                    // Primero mostrar módulos sin menú como enlaces individuales
                    foreach ($modulesWithoutMenu as $module): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/' . $module['modulo_ruta']) ?>">
                                <?= htmlspecialchars($module['modulo_descripcion']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php foreach ($groupedModules as $menuName => $modules): ?>
                        <?php if (count($modules) > 1): ?>
                            <!-- Dropdown para múltiples módulos -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                                    <?= htmlspecialchars($menuName) ?>
                                </a>
                                <div class="dropdown-menu">
                                    <?php foreach ($modules as $module): ?>
                                    <a class="dropdown-item" href="<?= $this->url('/' . $module['modulo_ruta']) ?>">
                                        <?= htmlspecialchars($module['modulo_descripcion']) ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php else: ?>
                            <!-- Enlace directo para módulos únicos (sin iconos) -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $this->url('/' . $modules[0]['modulo_ruta']) ?>">
                                    <?= htmlspecialchars($modules[0]['modulo_descripcion']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endif; // Fin del else del perfil huésped ?>
                <?php endif; // Fin del Auth::check() ?>
            </ul>

            <!-- Enlaces de usuario (derecha) -->
            <ul class="navbar-nav">
                <?php if (Auth::check()): ?>
                    <!-- Dropdown de usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                            <img src="<?= $this->asset('imagenes/home.png') ?>" alt="Avatar" class="user-avatar">
                            Usuario: <?= htmlspecialchars(Auth::user()) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <h6 class="dropdown-header">Perfil: <?= htmlspecialchars(Auth::getUserProfile()) ?></h6>
                            <div class="dropdown-divider"></div>
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

<script>
$(document).ready(function() {
    const menuToggle = $('#menuToggle');
    const navbarCollapse = $('#navbarNav');
    let isMenuOpen = false;
    
    // Manejar click en el botón hamburguesa
    menuToggle.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (isMenuOpen) {
            // Cerrar menú
            navbarCollapse.removeClass('show');
            menuToggle.removeClass('active');
            menuToggle.attr('aria-expanded', 'false');
            isMenuOpen = false;
        } else {
            // Abrir menú
            navbarCollapse.addClass('show');
            menuToggle.addClass('active');
            menuToggle.attr('aria-expanded', 'true');
            isMenuOpen = true;
        }
    });
    
    // Cerrar menú móvil al hacer clic en un enlace
    navbarCollapse.find('.nav-link:not(.dropdown-toggle)').on('click', function() {
        if ($(window).width() <= 991) {
            navbarCollapse.removeClass('show');
            menuToggle.removeClass('active');
            menuToggle.attr('aria-expanded', 'false');
            isMenuOpen = false;
        }
    });
    
    // Manejar dropdowns personalizados
    $('.dropdown-toggle').on('click', function(e) {
        const currentDropdown = $(this).next('.dropdown-menu');
        const isCurrentlyOpen = currentDropdown.hasClass('show');
        
        e.preventDefault();
        e.stopPropagation();
        
        // Cerrar TODOS los dropdowns primero
        $('.dropdown-menu.show').removeClass('show')
            .prev('.dropdown-toggle').attr('aria-expanded', 'false');
        
        // Si el dropdown actual no estaba abierto, abrirlo
        if (!isCurrentlyOpen) {
            currentDropdown.addClass('show');
            $(this).attr('aria-expanded', 'true');
        }
    });
    
    // Cerrar dropdowns al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu.show').removeClass('show')
                .prev('.dropdown-toggle').attr('aria-expanded', 'false');
        }
    });
});
</script>
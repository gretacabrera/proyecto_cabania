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
                    <!-- Notificaciones en tiempo real (solo para usuarios huéspedes) -->
                    <?php 
                    $userProfile = strtolower(Auth::getUserProfile() ?? '');
                    if ($userProfile === 'huesped'): 
                    ?>
                    <li class="nav-item dropdown mr-3">
                        <a class="nav-link position-relative" href="#" role="button" data-toggle="dropdown" aria-expanded="false" title="Notificaciones">
                            <i class="fas fa-bell fa-lg"></i>
                            <span id="notifications-badge" class="badge badge-danger badge-pill position-absolute d-none" 
                                  style="top: 0; right: 5px; font-size: 0.65rem; padding: 0.25em 0.5em;">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right p-0 notifications-dropdown" style="min-width: 350px; max-height: 500px; overflow-y: auto;">
                            <div class="dropdown-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-bell mr-2"></i>Notificaciones</strong>
                                <button class="btn btn-sm btn-link text-white p-0" onclick="NotificationService.clearAll()" title="Limpiar todo">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div id="notifications-list" class="py-2">
                                <div class="dropdown-item text-center text-muted">Sin notificaciones</div>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>
                    
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
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const navbarCollapse = document.getElementById('navbarNav');
    let isMenuOpen = false;
    
    if (!menuToggle || !navbarCollapse) return;
    
    // Manejar click en el botón hamburguesa
    menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (isMenuOpen) {
            // Cerrar menú
            navbarCollapse.classList.remove('show');
            menuToggle.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
            isMenuOpen = false;
        } else {
            // Abrir menú
            navbarCollapse.classList.add('show');
            menuToggle.classList.add('active');
            menuToggle.setAttribute('aria-expanded', 'true');
            isMenuOpen = true;
        }
    });
    
    // Cerrar menú móvil al hacer clic en un enlace
    navbarCollapse.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 991) {
                navbarCollapse.classList.remove('show');
                menuToggle.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                isMenuOpen = false;
            }
        });
    });
    
    // Manejar dropdowns personalizados
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const currentDropdown = this.nextElementSibling;
            if (!currentDropdown || !currentDropdown.classList.contains('dropdown-menu')) return;
            
            const isCurrentlyOpen = currentDropdown.classList.contains('show');
            
            e.preventDefault();
            e.stopPropagation();
            
            // Cerrar TODOS los dropdowns primero
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                const toggle = menu.previousElementSibling;
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            });
            
            // Si el dropdown actual no estaba abierto, abrirlo
            if (!isCurrentlyOpen) {
                currentDropdown.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });
    
    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                const toggle = menu.previousElementSibling;
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            });
        }
    });
});
</script>
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
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Enlaces de navegación -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Enlaces principales (derecha) -->
            <ul class="navbar-nav ml-auto">
                <!-- Catálogo solo para usuarios no autenticados -->
                <?php if (!Auth::check()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $this->url('/catalogo') ?>">Catálogo</a>
                </li>
                <?php endif; ?>

                <!-- Módulos del usuario autenticado organizados por menú -->
                <?php if (Auth::check()): ?>
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
                <?php endif; ?>
            </ul>

            <!-- Enlaces de usuario (derecha) -->
            <ul class="navbar-nav">
                <?php if (Auth::check()): ?>
                    <!-- Dropdown de usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                            <img src="<?= $this->asset('imagenes/home.png') ?>" alt="Avatar" class="user-avatar">
                            <?= htmlspecialchars(Auth::user()) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <h6 class="dropdown-header" style="color: #2c3e50;">Usuario: <?= htmlspecialchars(Auth::user()) ?></h6>
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
    // Manejar el cierre automático de dropdowns
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const currentDropdown = this.nextElementSibling;
            const isCurrentlyOpen = currentDropdown && currentDropdown.classList.contains('show');
            
            // Siempre prevenir el comportamiento por defecto para manejar manualmente
            e.preventDefault();
            e.stopPropagation();
            
            // Cerrar TODOS los dropdowns primero
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                const associatedToggle = menu.previousElementSibling;
                if (associatedToggle) {
                    associatedToggle.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Si el dropdown actual no estaba abierto, abrirlo
            if (!isCurrentlyOpen && currentDropdown) {
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
                const associatedToggle = menu.previousElementSibling;
                if (associatedToggle) {
                    associatedToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
    
    // Mejorar la experiencia en móvil con Bootstrap 4.6.2
    if (window.innerWidth <= 991) {
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        
        if (navbarToggler && navbarCollapse) {
            // Manejar el toggle del menú móvil
            navbarToggler.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                if (isExpanded) {
                    navbarCollapse.classList.remove('show');
                    this.setAttribute('aria-expanded', 'false');
                    this.classList.remove('active');
                } else {
                    navbarCollapse.classList.add('show');
                    this.setAttribute('aria-expanded', 'true');
                    this.classList.add('active');
                }
            });
            
            // Cerrar menú móvil al hacer clic en un enlace
            navbarCollapse.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
                link.addEventListener('click', function() {
                    navbarCollapse.classList.remove('show');
                    navbarToggler.setAttribute('aria-expanded', 'false');
                    navbarToggler.classList.remove('active');
                });
            });
        }
    }
});
</script>
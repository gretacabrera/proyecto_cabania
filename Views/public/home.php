<div class="home-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="hero-highlight">Casa de Palos</span>
                    <span class="hero-subtitle">Cabañas en la Naturaleza</span>
                </h1>
                <p class="hero-description">
                    Descubre la tranquilidad perfecta en nuestras cabañas ubicadas en el corazón del bosque. 
                    Un refugio donde la naturaleza y el confort se encuentran.
                </p>
                
                <?php if (!isset($user)): ?>
                    <div class="hero-actions">
                        <a href="<?= $this->url('/auth/login') ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i>
                            Iniciar Sesión
                        </a>
                        <a href="<?= $this->url('/catalogo') ?>" class="btn btn-secondary btn-lg">
                            <i class="fas fa-calendar-alt"></i>
                            Ver Disponibilidad
                        </a>
                    </div>
                <?php else: ?>
                    <div class="hero-welcome">
                        <div class="welcome-card">
                            <div class="welcome-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="welcome-info">
                                <h3>¡Bienvenido/a, <?= $this->escape($user) ?>!</h3>
                                <p class="welcome-role"><?= ucfirst($userProfile ?? 'Usuario') ?></p>
                            </div>
                        </div>
                        
                        <div class="hero-actions">
                            <?php if ($userProfile === 'huesped'): ?>
                                <a href="<?= $this->url('/catalogo') ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus-circle"></i>
                                    Nueva Reserva
                                </a>
                                <a href="<?= $this->url('/reservas') ?>" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-list"></i>
                                    Mis Reservas
                                </a>
                            <?php else: ?>
                                <a href="<?= $this->url('/') ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Panel de Control
                                </a>
                                <a href="<?= $this->url('/reservas') ?>" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-calendar-check"></i>
                                    Gestionar Reservas
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">¿Por qué elegir Casa de Palos?</h2>
            <p class="section-subtitle">
                Ofrecemos una experiencia única que combina naturaleza, confort y aventura
            </p>
        </div>
        
        <div class="grid grid-cols-3 features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="feature-title">Cabañas Cómodas</h3>
                <p class="feature-description">
                    Espacios completamente equipados con todas las comodidades modernas 
                    para garantizar tu descanso y bienestar.
                </p>
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Cocina completa</li>
                    <li><i class="fas fa-check"></i> Baño privado</li>
                    <li><i class="fas fa-check"></i> Calefacción</li>
                    <li><i class="fas fa-check"></i> WiFi gratuito</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tree"></i>
                </div>
                <h3 class="feature-title">Entorno Natural</h3>
                <p class="feature-description">
                    Ubicadas estratégicamente en el bosque con acceso directo a 
                    senderos naturales y paisajes espectaculares.
                </p>
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Senderos hiking</li>
                    <li><i class="fas fa-check"></i> Observación fauna</li>
                    <li><i class="fas fa-check"></i> Vistas panorámicas</li>
                    <li><i class="fas fa-check"></i> Aire puro</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <h3 class="feature-title">Servicios Premium</h3>
                <p class="feature-description">
                    Actividades recreativas, servicios gastronómicos y atención 
                    personalizada para hacer tu estadía inolvidable.
                </p>
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Actividades guiadas</li>
                    <li><i class="fas fa-check"></i> Servicio de comidas</li>
                    <li><i class="fas fa-check"></i> Atención 24/7</li>
                    <li><i class="fas fa-check"></i> Eventos especiales</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php if (!isset($user)): ?>
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">¿Listo para tu próxima aventura?</h2>
            <p class="cta-description">
                Únete a miles de huéspedes que han descubierto la experiencia Casa de Palos
            </p>
            <div class="cta-actions">
                <a href="<?= $this->url('/auth/register') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i>
                    Crear Cuenta
                </a>
                <a href="<?= $this->url('/auth/login') ?>" class="btn btn-secondary btn-lg">
                    <i class="fas fa-sign-in-alt"></i>
                    Ya tengo cuenta
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
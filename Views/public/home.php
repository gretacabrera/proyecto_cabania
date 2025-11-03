<?php if (!isset($user)): ?>
<!-- Vista para usuarios no autenticados -->
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

<?php elseif ($userProfile === 'huesped'): ?>
<!-- Dashboard del Huésped -->
<div class="dashboard-container">
    <div class="container">
        <!-- Header de bienvenida -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <div class="welcome-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="welcome-info">
                    <h1>¡Bienvenido/a, <?= $this->escape($user) ?>!</h1>
                    <p class="user-role">Huésped</p>
                    <?php if (isset($persona)): ?>
                        <p class="user-details">
                            <i class="fas fa-id-card"></i>
                            <?= $this->escape($persona['persona_nombre'] . ' ' . $persona['persona_apellido']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="quick-actions">
                <a href="<?= $this->url('/catalogo') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle"></i>
                    Nueva Reserva
                </a>
            </div>
        </div>
        
        <!-- Próximas reservas -->
        <div class="dashboard-section">
            <h2><i class="fas fa-calendar-check"></i> Mis Próximas Reservas</h2>
            
            <?php if (!empty($reservas_proximas)): ?>
                <div class="reservas-grid">
                    <?php foreach ($reservas_proximas as $reserva): ?>
                        <div class="reserva-card">
                            <div class="reserva-header">
                                <h3><?= $this->escape($reserva['cabania_nombre']) ?></h3>
                                <span class="reserva-estado estado-<?= strtolower($reserva['estadoreserva_descripcion']) ?>">
                                    <?= ucfirst($reserva['estadoreserva_descripcion']) ?>
                                </span>
                            </div>
                            <div class="reserva-details">
                                <p><i class="fas fa-home"></i> <?= $this->escape($reserva['cabania_codigo']) ?></p>
                                <p><i class="fas fa-calendar"></i> 
                                    Del <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?> 
                                    al <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                                </p>
                            </div>
                            <div class="reserva-actions">
                                <a href="<?= $this->url('/reservas/ver/' . $reserva['id_reserva']) ?>" class="btn btn-sm btn-secondary">
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No tienes reservas próximas</p>
                    <a href="<?= $this->url('/catalogo') ?>" class="btn btn-primary">
                        Hacer una Reserva
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Historial reciente -->
        <?php if (!empty($reservas_historial)): ?>
        <div class="dashboard-section">
            <h2><i class="fas fa-history"></i> Historial Reciente</h2>
            
            <div class="historial-list">
                <?php foreach ($reservas_historial as $reserva): ?>
                    <div class="historial-item">
                        <div class="historial-info">
                            <h4><?= $this->escape($reserva['cabania_nombre']) ?></h4>
                            <p><?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?> - <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?></p>
                        </div>
                        <div class="historial-estado">
                            <span class="estado-<?= strtolower($reserva['estadoreserva_descripcion']) ?>">
                                <?= ucfirst($reserva['estadoreserva_descripcion']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php elseif ($userProfile === 'administrador'): ?>
<!-- Dashboard del Administrador -->
<div class="dashboard-container">
    <div class="container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1><i class="fas fa-tachometer-alt"></i> Panel de Administración</h1>
                <p class="dashboard-subtitle">Vista general del negocio</p>
            </div>
        </div>
        
        <!-- KPIs principales -->
        <div class="kpis-grid">
            
            <div class="kpi-card">
                <div class="kpi-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="kpi-content">
                    <h3><?= $kpis['ocupacion_actual'] ?>%</h3>
                    <p>Ocupación Actual</p>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="kpi-content">
                    <h3>$<?= number_format($kpis['ingresos_mes'], 0, ',', '.') ?></h3>
                    <p>Ingresos del Mes</p>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="kpi-content">
                    <h3><?= $kpis['reservas_activas'] ?></h3>
                    <p>Reservas Activas</p>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-content">
                    <h3><?= $kpis['huespedes_mes'] ?></h3>
                    <p>Huéspedes este Mes</p>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas mensuales -->
        <div class="dashboard-section">
            <h2><i class="fas fa-chart-line"></i> Tendencias (Últimos 6 Meses)</h2>
            <div class="estadisticas-mensuales">
                <?php foreach ($estadisticas_mensuales as $mes): ?>
                    <div class="mes-stat">
                        <h4><?= $mes['mes'] ?></h4>
                        <p><i class="fas fa-calendar"></i> <?= $mes['reservas'] ?> reservas</p>
                        <p><i class="fas fa-dollar-sign"></i> $<?= number_format($mes['ingresos'], 0, ',', '.') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Reservas recientes -->
        <div class="dashboard-section">
            <h2><i class="fas fa-clock"></i> Actividad Reciente</h2>
            <div class="actividad-list">
                <?php foreach ($reservas_recientes as $reserva): ?>
                    <div class="actividad-item">
                        <div class="actividad-info">
                            <h4>Reserva #<?= $reserva['id_reserva'] ?></h4>
                            <p><?= $this->escape($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?></p>
                            <p><?= $this->escape($reserva['cabania_nombre']) ?> - <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?></p>
                        </div>
                        <div class="actividad-estado">
                            <span class="estado-<?= strtolower($reserva['estadoreserva_descripcion']) ?>">
                                <?= ucfirst($reserva['estadoreserva_descripcion']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php elseif ($userProfile === 'cajero'): ?>
<!-- Dashboard del Cajero -->
<div class="dashboard-container">
    <div class="container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1><i class="fas fa-cash-register"></i> Panel de Facturación</h1>
                <p class="dashboard-subtitle">Gestión de pagos y facturación</p>
            </div>
        </div>
        
        <!-- KPIs de facturación -->
        <div class="kpis-grid">
            <div class="kpi-card kpi-hoy">
                <div class="kpi-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="kpi-content">
                    <h3><?= $facturacion['facturas_hoy'] ?></h3>
                    <p>Facturas Hoy</p>
                </div>
            </div>
            
            <div class="kpi-card kpi-hoy">
                <div class="kpi-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="kpi-content">
                    <h3>$<?= number_format($facturacion['ingresos_hoy'], 0, ',', '.') ?></h3>
                    <p>Ingresos Hoy</p>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="kpi-content">
                    <h3><?= $facturacion['facturas_mes'] ?></h3>
                    <p>Facturas del Mes</p>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="kpi-content">
                    <h3>$<?= number_format($facturacion['ingresos_mes'], 0, ',', '.') ?></h3>
                    <p>Ingresos del Mes</p>
                </div>
            </div>
        </div>
        
        <!-- Métodos de pago -->
        <?php if (!empty($facturacion['metodos_pago'])): ?>
        <div class="dashboard-section">
            <h2><i class="fas fa-credit-card"></i> Métodos de Pago (Este Mes)</h2>
            <div class="metodos-pago-grid">
                <?php foreach ($facturacion['metodos_pago'] as $metodo): ?>
                    <div class="metodo-card">
                        <h4><?= $this->escape($metodo['metododepago_descripcion']) ?></h4>
                        <p><strong><?= $metodo['cantidad'] ?></strong> transacciones</p>
                        <p>$<?= number_format($metodo['total'], 0, ',', '.') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Reservas pendientes de pago -->
        <div class="dashboard-section">
            <h2><i class="fas fa-exclamation-triangle"></i> Reservas Pendientes de Pago</h2>
            <?php if (!empty($reservas_pendientes_pago)): ?>
                <div class="pendientes-list">
                    <?php foreach ($reservas_pendientes_pago as $reserva): ?>
                        <div class="pendiente-item">
                            <div class="pendiente-info">
                                <h4>Reserva #<?= $reserva['id_reserva'] ?></h4>
                                <p><?= $this->escape($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?></p>
                                <p><?= $this->escape($reserva['cabania_nombre']) ?></p>
                                <p><?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?></p>
                            </div>
                            <div class="pendiente-monto">
                                <strong>$<?= number_format($reserva['cabania_precio'], 0, ',', '.') ?></strong>
                            </div>
                            <div class="pendiente-actions">
                                <a href="<?= $this->url('/reservas/procesar-pago/' . $reserva['id_reserva']) ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-money-bill"></i>
                                    Procesar Pago
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>No hay reservas pendientes de pago</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Facturas recientes -->
        <div class="dashboard-section">
            <h2><i class="fas fa-history"></i> Facturas Recientes</h2>
            <div class="facturas-list">
                <?php foreach ($facturas_recientes as $factura): ?>
                    <div class="factura-item">
                        <div class="factura-info">
                            <h4>Factura <?= $this->escape($factura['factura_nro']) ?></h4>
                            <p><?= $this->escape($factura['persona_nombre'] . ' ' . $factura['persona_apellido']) ?></p>
                            <p><?= $this->escape($factura['cabania_nombre']) ?></p>
                            <p><?= date('d/m/Y H:i', strtotime($factura['factura_fechahora'])) ?></p>
                        </div>
                        <div class="factura-monto">
                            <strong>$<?= number_format($factura['factura_total'], 0, ',', '.') ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php elseif ($userProfile === 'recepcionista'): ?>
<!-- Dashboard del Recepcionista -->
<div class="dashboard-container">
    <div class="container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1><i class="fas fa-concierge-bell"></i> Panel de Recepción</h1>
                <p class="dashboard-subtitle">Estado de cabañas y gestión de reservas</p>
            </div>
        </div>
        
        <!-- Estado de cabañas -->
        <div class="cabanias-estado-grid">
            <div class="estado-card disponible">
                <div class="estado-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="estado-content">
                    <h3><?= count($cabanias_estado['disponibles']) ?></h3>
                    <p>Cabañas Disponibles</p>
                </div>
            </div>
            
            <div class="estado-card ocupada">
                <div class="estado-icon">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="estado-content">
                    <h3><?= count($cabanias_estado['ocupadas']) ?></h3>
                    <p>Cabañas Ocupadas</p>
                </div>
            </div>
            
            <div class="estado-card total">
                <div class="estado-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="estado-content">
                    <h3><?= count($cabanias_estado['total']) ?></h3>
                    <p>Total Cabañas</p>
                </div>
            </div>
        </div>
        
        <!-- Actividad del día -->
        <div class="dashboard-section">
            <h2><i class="fas fa-calendar-day"></i> Actividad de Hoy</h2>
            
            <div class="actividad-hoy-grid">
                <!-- Check-ins -->
                <div class="actividad-grupo">
                    <h3><i class="fas fa-sign-in-alt"></i> Check-ins</h3>
                    <?php if (!empty($checkins_hoy)): ?>
                        <div class="actividad-items">
                            <?php foreach ($checkins_hoy as $checkin): ?>
                                <div class="actividad-item-small">
                                    <strong><?= $this->escape($checkin['cabania_nombre']) ?></strong>
                                    <p><?= $this->escape($checkin['persona_nombre'] . ' ' . $checkin['persona_apellido']) ?></p>
                                    <small><?= date('H:i', strtotime($checkin['reserva_fhinicio'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="empty-text">No hay check-ins programados</p>
                    <?php endif; ?>
                </div>
                
                <!-- Check-outs -->
                <div class="actividad-grupo">
                    <h3><i class="fas fa-sign-out-alt"></i> Check-outs</h3>
                    <?php if (!empty($checkouts_hoy)): ?>
                        <div class="actividad-items">
                            <?php foreach ($checkouts_hoy as $checkout): ?>
                                <div class="actividad-item-small">
                                    <strong><?= $this->escape($checkout['cabania_nombre']) ?></strong>
                                    <p><?= $this->escape($checkout['persona_nombre'] . ' ' . $checkout['persona_apellido']) ?></p>
                                    <small><?= date('H:i', strtotime($checkout['reserva_fhfin'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="empty-text">No hay check-outs programados</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Estado detallado de cabañas -->
        <div class="dashboard-section">
            <h2><i class="fas fa-list"></i> Estado Detallado de Cabañas</h2>
            
            <div class="cabanias-detalle-grid">
                <?php foreach ($cabanias_estado['total'] as $cabania): ?>
                    <div class="cabania-card <?= $cabania['cabania_estado'] == 1 ? 'disponible' : 'ocupada' ?>">
                        <div class="cabania-header">
                            <h4><?= $this->escape($cabania['cabania_nombre']) ?></h4>
                            <span class="cabania-codigo"><?= $this->escape($cabania['cabania_codigo']) ?></span>
                        </div>
                        <div class="cabania-details">
                            <p><i class="fas fa-users"></i> Capacidad: <?= $cabania['cabania_capacidad'] ?></p>
                            <p><i class="fas fa-dollar-sign"></i> $<?= number_format($cabania['cabania_precio'], 0, ',', '.') ?>/noche</p>
                        </div>
                        <div class="cabania-estado">
                            <?php if ($cabania['cabania_estado'] == 1): ?>
                                <span class="estado-disponible">
                                    <i class="fas fa-check-circle"></i> Disponible
                                </span>
                            <?php else: ?>
                                <span class="estado-ocupada">
                                    <i class="fas fa-bed"></i> Ocupada
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Reservas próximas -->
        <div class="dashboard-section">
            <h2><i class="fas fa-calendar-alt"></i> Próximas Reservas (7 días)</h2>
            
            <?php if (!empty($reservas_proximas)): ?>
                <div class="reservas-proximas-list">
                    <?php foreach ($reservas_proximas as $reserva): ?>
                        <div class="reserva-proxima-item">
                            <div class="reserva-fecha">
                                <div class="fecha-dia"><?= date('d', strtotime($reserva['reserva_fhinicio'])) ?></div>
                                <div class="fecha-mes"><?= date('M', strtotime($reserva['reserva_fhinicio'])) ?></div>
                            </div>
                            <div class="reserva-info">
                                <h4><?= $this->escape($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?></h4>
                                <p><i class="fas fa-home"></i> <?= $this->escape($reserva['cabania_nombre']) ?></p>
                                <p><i class="fas fa-calendar"></i> 
                                    <?= date('d/m', strtotime($reserva['reserva_fhinicio'])) ?> - 
                                    <?= date('d/m', strtotime($reserva['reserva_fhfin'])) ?>
                                </p>
                            </div>
                            <div class="reserva-estado">
                                <span class="estado-<?= strtolower($reserva['estadoreserva_descripcion']) ?>">
                                    <?= ucfirst($reserva['estadoreserva_descripcion']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No hay reservas próximas en los próximos 7 días</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Vista por defecto para perfiles no reconocidos -->
<div class="dashboard-container">
    <div class="container">
        <div class="dashboard-header">
            <h1>¡Bienvenido/a, <?= $this->escape($user) ?>!</h1>
            <p>Perfil: <?= ucfirst($userProfile ?? 'Usuario') ?></p>
        </div>
        
        <div class="dashboard-section">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Su perfil no tiene un dashboard específico configurado. 
                Contacte al administrador para más información.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
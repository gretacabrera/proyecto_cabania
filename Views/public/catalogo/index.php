<?php
/**
 * Vista del catálogo público de cabañas
 * Layout: main (para todos los usuarios)
 */
?>

<!-- Hero Section Minimalista -->
<div class="hero-section catalog-hero minimal-hero">
    <div class="container">
        <div class="hero-minimal-content">
            <h1 class="minimal-title">Catálogo de Cabañas</h1>
            <p class="minimal-subtitle">Encuentra la cabaña perfecta para tu mejor experiencia</p>
            
            <div class="minimal-filters">
                <form method="GET" class="minimal-form">
                    <div class="minimal-grid">
                        <input type="text" 
                               id="busqueda" 
                               name="busqueda" 
                               value="<?= $this->escape($filters['busqueda']) ?>" 
                               placeholder="Buscar cabaña..."
                               class="minimal-input">
                        
                        <select id="capacidad" name="capacidad" class="minimal-select">
                            <option value="">Personas</option>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $filters['capacidad'] == $i ? 'selected' : '' ?>>
                                    <?= $i ?> <?= $i > 1 ? 'personas' : 'persona' ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        
                        <input type="number" 
                               id="precio_max" 
                               name="precio_max" 
                               value="<?= $this->escape($filters['precio_max']) ?>" 
                               placeholder="Precio máx"
                               step="0.01"
                               min="0"
                               class="minimal-input price-input">
                        
                        <div class="minimal-actions">
                            <button type="submit" class="btn-minimal btn-search" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= $this->url('/catalogo') ?>" class="btn-minimal btn-clear" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Resultados -->
<section class="catalog-results">
    <div class="container">
        <!-- Form oculto para envío de datos de reserva -->
        <form id="reservationForm" method="POST" action="<?= $this->url('/catalogo/reserve') ?>" class="hidden-form">
            <input type="hidden" name="cabania_id" id="reserveCabinId">
            <input type="hidden" name="fecha_inicio" id="reserveDateStart">
            <input type="hidden" name="fecha_fin" id="reserveDateEnd">
        </form>

        <!-- Resultados del Catálogo -->
<section class="catalog-results">
    <div class="container">
        <div class="results-header">
            <div class="results-info">
                <h2>
                    <?php if ($total_results > 0): ?>
                        <?= $total_results ?> cabaña<?= $total_results > 1 ? 's' : '' ?> encontrada<?= $total_results > 1 ? 's' : '' ?>
                    <?php else: ?>
                        No se encontraron cabañas
                    <?php endif; ?>
                </h2>
            </div>
        </div>
        
        <?php if (!empty($cabanias)): ?>
            <div class="cabins-grid">
                <?php foreach ($cabanias as $cabania): ?>
                    <div class="cabin-card" data-cabin-id="<?= $cabania['id_cabania'] ?>">
                        <div class="cabin-image">
                            <img src="<?= $this->asset('imagenes/cabanias/' . $cabania['imagen']) ?>" 
                                 alt="<?= $this->escape($cabania['cabania_nombre']) ?>"
                                 loading="lazy">
                            <div class="cabin-status">
                                <span class="status-badge status-available">
                                    <i class="fas fa-check-circle"></i> Disponible
                                </span>
                            </div>
                        </div>
                        
                        <div class="cabin-info">
                            <h3 class="cabin-name"><?= $this->escape($cabania['cabania_nombre']) ?></h3>
                            <p class="cabin-code">Código: <?= $this->escape($cabania['cabania_codigo']) ?></p>
                            
                            <?php if (!empty($cabania['cabania_descripcion'])): ?>
                                <p class="cabin-description">
                                    <?= $this->escape($cabania['cabania_descripcion']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="cabin-features">
                                <div class="feature">
                                    <i class="fas fa-users"></i>
                                    <span>Hasta <?= $cabania['cabania_capacidad'] ?> personas</span>
                                </div>
                                <div class="feature">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span>$<?= number_format($cabania['cabania_precio'], 2) ?> por noche</span>
                                </div>
                            </div>
                            
                            <div class="cabin-actions">
                                <button type="button" 
                                        class="btn btn-outline btn-availability" 
                                        data-cabin-id="<?= $cabania['id_cabania'] ?>"
                                        data-cabin-name="<?= $this->escape($cabania['cabania_nombre']) ?>"
                                        data-cabin-price="<?= $cabania['cabania_precio'] ?>">
                                    <i class="fas fa-calendar-alt"></i>
                                    Ver Disponibilidad
                                </button>
                                
                                <button type="button" 
                                        class="btn btn-primary btn-reserve" 
                                        data-cabin-id="<?= $cabania['id_cabania'] ?>"
                                        disabled>
                                    <i class="fas fa-booking"></i>
                                    Reservar Ahora
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination-container">
                    <nav class="pagination">
                        <?php
                        $currentPage = $pagination['current_page'];
                        $totalPages = $pagination['total_pages'];
                        $baseUrl = $this->url('/catalogo') . '?' . http_build_query(array_filter($filters));
                        $baseUrl .= $baseUrl && strpos($baseUrl, '?') ? '&' : '?';
                        ?>
                        
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= $baseUrl ?>page=<?= $currentPage - 1 ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <div class="pagination-numbers">
                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                            ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <a href="<?= $baseUrl ?>page=<?= $i ?>" 
                                   class="pagination-number <?= $i == $currentPage ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= $baseUrl ?>page=<?= $currentPage + 1 ?>" class="pagination-btn">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Estado vacío -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>No se encontraron cabañas</h3>
                <p>Intenta ajustar los filtros de búsqueda para encontrar más opciones.</p>
                <a href="<?= $this->url('/catalogo') ?>" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Ver todas las cabañas
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal de Calendario de Disponibilidad Rediseñado -->
<div class="availability-modal-redesigned" id="availabilityModal" style="display: none;">
    <div class="modal-overlay" onclick="closeAvailabilityModal()"></div>
    <div class="modal-container-new">
        <div class="modal-header-new">
            <h3 class="modal-title-new">
                <i class="fas fa-calendar-check"></i>
                Disponibilidad - <span id="modalCabinName"></span>
            </h3>
            <button type="button" class="modal-close-new" onclick="closeAvailabilityModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body-new">
            <!-- Información de fechas seleccionadas -->
            <div class="selected-dates-display" id="selectedDatesDisplay" style="display: none;">
                <div class="dates-summary">
                    <div class="date-box checkin-date">
                        <span class="date-label">Entrada</span>
                        <span class="date-value" id="displayCheckinDate">--</span>
                    </div>
                    <div class="date-arrow">
                        <i class="fas fa-long-arrow-alt-right"></i>
                    </div>
                    <div class="date-box checkout-date">
                        <span class="date-label">Salida</span>
                        <span class="date-value" id="displayCheckoutDate">--</span>
                    </div>
                </div>
                <div class="nights-summary" id="nightsSummary"></div>
            </div>
            
            <!-- Calendario simplificado -->
            <div class="simple-calendar">
                <div class="calendar-nav">
                    <button type="button" class="nav-button prev" id="prevMonthNew">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h4 class="month-title" id="monthTitleNew">Enero 2025</h4>
                    <button type="button" class="nav-button next" id="nextMonthNew">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="calendar-table">
                    <div class="calendar-header-row">
                        <div class="day-header">Dom</div>
                        <div class="day-header">Lun</div>
                        <div class="day-header">Mar</div>
                        <div class="day-header">Mié</div>
                        <div class="day-header">Jue</div>
                        <div class="day-header">Vie</div>
                        <div class="day-header">Sáb</div>
                    </div>
                    <div class="calendar-body" id="calendarBodyNew">
                        <!-- Los días se generan aquí -->
                    </div>
                </div>
            </div>
            
            <!-- Leyenda simplificada -->
            <div class="calendar-legend-new">
                <div class="legend-row">
                    <div class="legend-item-new">
                        <span class="legend-square available"></span>
                        <span>Disponible</span>
                    </div>
                    <div class="legend-item-new">
                        <span class="legend-square occupied"></span>
                        <span>Ocupado</span>
                    </div>
                    <div class="legend-item-new">
                        <span class="legend-square selected-start"></span>
                        <span>Entrada</span>
                    </div>
                    <div class="legend-item-new">
                        <span class="legend-square selected-end"></span>
                        <span>Salida</span>
                    </div>
                    <div class="legend-item-new">
                        <span class="legend-square in-range"></span>
                        <span>Estancia</span>
                    </div>
                </div>
            </div>
            
            <!-- Mensaje de instrucciones -->
            <div class="instruction-message" id="instructionMessage">
                Selecciona la fecha de entrada
            </div>
        </div>
        
        <div class="modal-footer-new">
            <button type="button" class="btn-cancel" onclick="closeAvailabilityModal()">Cancelar</button>
            <button type="button" class="btn-confirm" id="confirmReservationNew" disabled>
                <i class="fas fa-check"></i>
                Confirmar Reserva
            </button>
        </div>
    </div>
    
    <!-- Inputs ocultos para compatibilidad -->
    <input type="hidden" id="checkinDate" />
    <input type="hidden" id="checkoutDate" />
</div>

<!-- Sistema de calendario rediseñado -->
<script src="<?= $this->url('/assets/js/calendar-system.js') ?>"></script>
/**
 * Admin JavaScript - Casa de Palos Cabañas
 * JavaScript específico para el panel administrativo
 */

// ===========================================
// DASHBOARD DE ESTADÍSTICAS
// ===========================================

class AdminDashboard {
    constructor() {
        this.charts = {};
        this.initializeDashboard();
    }

    initializeDashboard() {
        this.animateStatCards();
        this.initializeCharts();
        this.bindEvents();
        this.initProgressBars();
    }

    animateStatCards() {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                
                // Animar números
                const numberElement = card.querySelector('.number');
                if (numberElement) {
                    this.animateNumber(numberElement);
                }
            }, index * 100);
        });
    }

    animateNumber(element) {
        const finalValue = parseInt(element.textContent.replace(/\D/g, ''));
        let currentValue = 0;
        const increment = finalValue / 50;
        const duration = 1000;
        const stepTime = duration / 50;

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            element.textContent = Math.floor(currentValue).toLocaleString();
        }, stepTime);
    }

    initializeCharts() {
        // Placeholder para gráficos (Chart.js, D3, etc.)
        const chartContainers = document.querySelectorAll('.chart-container');
        chartContainers.forEach(container => {
            this.createChart(container);
        });
    }

    createChart(container) {
        // Implementación de gráficos según la librería utilizada
        console.log('Inicializando gráfico en:', container);
    }

    bindEvents() {
        // Actualizar dashboard
        const refreshButton = document.querySelector('.btn-refresh-dashboard');
        if (refreshButton) {
            refreshButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.refreshDashboard();
            });
        }

        // Filtros de dashboard
        const filterSelects = document.querySelectorAll('.dashboard-filter');
        filterSelects.forEach(select => {
            select.addEventListener('change', () => {
                this.updateDashboard();
            });
        });

        // Inicializar indicadores de color
        this.initColorIndicators();
    }

    initColorIndicators() {
        const colorIndicators = document.querySelectorAll('.color-indicator[data-color]');
        colorIndicators.forEach(indicator => {
            const color = indicator.getAttribute('data-color');
            if (color) {
                indicator.style.setProperty('--indicator-color', color);
            }
        });
    }

    initProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar[data-width]');
        progressBars.forEach((bar, index) => {
            const width = bar.getAttribute('data-width');
            
            // Animar después de un delay
            setTimeout(() => {
                bar.style.width = width;
            }, index * 200);
        });
    }

    refreshDashboard() {
        window.showLoading();
        
        // Simular carga de datos
        setTimeout(() => {
            this.animateStatCards();
            window.hideLoading();
            window.showSuccess('Dashboard actualizado', 'Los datos se han actualizado correctamente');
        }, 1000);
    }

    updateDashboard() {
        const filters = {};
        const filterElements = document.querySelectorAll('.dashboard-filter');
        
        filterElements.forEach(filter => {
            filters[filter.name] = filter.value;
        });

        console.log('Actualizando dashboard con filtros:', filters);
        // Aquí iría la lógica para actualizar con AJAX
    }
}

// ===========================================
// GESTIÓN DE TABLAS ADMINISTRATIVAS
// ===========================================

class AdminTables {
    constructor() {
        this.tables = new Map();
        this.initializeTables();
    }

    initializeTables() {
        const tables = document.querySelectorAll('.admin-table');
        tables.forEach(table => {
            this.bindTableFeatures(table);
        });
    }

    bindTableFeatures(table) {
        // Selección de filas
        this.bindRowSelection(table);
        
        // Ordenamiento de columnas
        this.bindColumnSorting(table);
        
        // Acciones masivas
        this.bindBulkActions(table);
        
        // Filtros rápidos
        this.bindQuickFilters(table);

        this.tables.set(table, {
            selectedRows: new Set(),
            sortColumn: null,
            sortDirection: 'asc'
        });
    }

    bindRowSelection(table) {
        const selectAllCheckbox = table.querySelector('thead .select-all');
        const rowCheckboxes = table.querySelectorAll('tbody .row-select');
        const tableData = this.tables.get(table);

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const isChecked = e.target.checked;
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    const row = checkbox.closest('tr');
                    
                    if (isChecked) {
                        row.classList.add('selected');
                        tableData.selectedRows.add(row);
                    } else {
                        row.classList.remove('selected');
                        tableData.selectedRows.delete(row);
                    }
                });
                
                this.updateBulkActionsVisibility(table);
            });
        }

        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const row = e.target.closest('tr');
                
                if (e.target.checked) {
                    row.classList.add('selected');
                    tableData.selectedRows.add(row);
                } else {
                    row.classList.remove('selected');
                    tableData.selectedRows.delete(row);
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                    }
                }
                
                this.updateBulkActionsVisibility(table);
            });
        });
    }

    bindColumnSorting(table) {
        const sortableHeaders = table.querySelectorAll('th[data-sort]');
        const tableData = this.tables.get(table);

        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sort');
                
                if (tableData.sortColumn === column) {
                    tableData.sortDirection = tableData.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    tableData.sortColumn = column;
                    tableData.sortDirection = 'asc';
                }

                this.sortTable(table, column, tableData.sortDirection);
                this.updateSortIndicators(table, column, tableData.sortDirection);
            });
        });
    }

    sortTable(table, column, direction) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const aValue = a.querySelector(`[data-sort-value="${column}"]`)?.textContent || 
                          a.querySelector(`td:nth-child(${this.getColumnIndex(table, column)})`)?.textContent || '';
            const bValue = b.querySelector(`[data-sort-value="${column}"]`)?.textContent || 
                          b.querySelector(`td:nth-child(${this.getColumnIndex(table, column)})`)?.textContent || '';

            const comparison = aValue.localeCompare(bValue, undefined, { numeric: true });
            return direction === 'asc' ? comparison : -comparison;
        });

        rows.forEach(row => tbody.appendChild(row));
    }

    getColumnIndex(table, column) {
        const headers = table.querySelectorAll('th');
        for (let i = 0; i < headers.length; i++) {
            if (headers[i].getAttribute('data-sort') === column) {
                return i + 1;
            }
        }
        return 1;
    }

    updateSortIndicators(table, activeColumn, direction) {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.classList.remove('sort-asc', 'sort-desc');
            
            if (header.getAttribute('data-sort') === activeColumn) {
                header.classList.add(`sort-${direction}`);
            }
        });
    }

    bindBulkActions(table) {
        const bulkActionButtons = table.parentNode.querySelectorAll('.bulk-action');
        
        bulkActionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const action = button.getAttribute('data-action');
                const selectedRows = Array.from(this.tables.get(table).selectedRows);
                
                if (selectedRows.length === 0) {
                    window.showWarning('Sin selección', 'Selecciona al menos un elemento');
                    return;
                }

                this.executeBulkAction(table, action, selectedRows);
            });
        });
    }

    executeBulkAction(table, action, selectedRows) {
        const ids = selectedRows.map(row => row.getAttribute('data-id')).filter(id => id);
        
        if (ids.length === 0) return;

        const confirmMessage = `¿Estás seguro de que deseas ${action} ${ids.length} elemento(s)?`;
        
        if (confirm(confirmMessage)) {
            // Aquí iría la llamada AJAX para ejecutar la acción
            console.log(`Ejecutando acción masiva: ${action}`, ids);
            
            window.showSuccess('Acción completada', `Se han procesado ${ids.length} elementos`);
            
            // Recargar la tabla o eliminar filas localmente según la acción
            if (action === 'delete') {
                selectedRows.forEach(row => row.remove());
            }
        }
    }

    bindQuickFilters(table) {
        const filterInputs = document.querySelectorAll('.quick-filter');
        
        filterInputs.forEach(input => {
            let timeout;
            input.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.applyQuickFilter(table, e.target.value);
                }, 300);
            });
        });
    }

    applyQuickFilter(table, filterValue) {
        const rows = table.querySelectorAll('tbody tr');
        const searchTerm = filterValue.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const shouldShow = searchTerm === '' || text.includes(searchTerm);
            
            row.style.display = shouldShow ? '' : 'none';
        });
    }

    updateBulkActionsVisibility(table) {
        const selectedCount = this.tables.get(table).selectedRows.size;
        const bulkActionsContainer = table.parentNode.querySelector('.bulk-actions');
        
        if (bulkActionsContainer) {
            bulkActionsContainer.style.display = selectedCount > 0 ? 'flex' : 'none';
        }

        // Actualizar contador de seleccionados
        const selectedCounter = document.querySelector('.selected-count');
        if (selectedCounter) {
            selectedCounter.textContent = `${selectedCount} seleccionados`;
        }
    }
}

// ===========================================
// FORMULARIOS ADMINISTRATIVOS AVANZADOS
// ===========================================

class AdminForms {
    constructor() {
        this.initializeForms();
    }

    initializeForms() {
        this.bindImageUploads();
        this.bindRichTextEditors();
        this.bindDependentSelects();
        this.bindFormTabs();
    }

    bindImageUploads() {
        const uploadInputs = document.querySelectorAll('input[type="file"][data-preview]');
        
        uploadInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.previewImage(e.target);
            });
        });
    }

    previewImage(input) {
        const previewContainer = document.querySelector(input.getAttribute('data-preview'));
        if (!previewContainer) return;

        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            previewContainer.innerHTML = `
                <img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 8px;">
                <button type="button" class="btn-remove-image" style="margin-top: 10px;">
                    Eliminar imagen
                </button>
            `;

            previewContainer.querySelector('.btn-remove-image').addEventListener('click', () => {
                input.value = '';
                previewContainer.innerHTML = '<p>Sin imagen seleccionada</p>';
            });
        };
        reader.readAsDataURL(file);
    }

    bindRichTextEditors() {
        // Placeholder para editores ricos (TinyMCE, CKEditor, etc.)
        const richTextAreas = document.querySelectorAll('textarea[data-rich-text]');
        
        richTextAreas.forEach(textarea => {
            console.log('Inicializando editor rico para:', textarea);
            // Aquí iría la inicialización del editor
        });
    }

    bindDependentSelects() {
        const dependentSelects = document.querySelectorAll('select[data-depends-on]');
        
        dependentSelects.forEach(select => {
            const dependsOn = select.getAttribute('data-depends-on');
            const parentSelect = document.querySelector(`[name="${dependsOn}"]`);
            
            if (parentSelect) {
                parentSelect.addEventListener('change', () => {
                    this.updateDependentSelect(select, parentSelect.value);
                });
            }
        });
    }

    async updateDependentSelect(select, parentValue) {
        const url = select.getAttribute('data-source');
        if (!url) return;

        try {
            window.showLoading();
            const response = await fetch(`${url}?parent=${encodeURIComponent(parentValue)}`);
            const data = await response.json();
            
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            data.options?.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                select.appendChild(optionElement);
            });
            
        } catch (error) {
            console.error('Error updating dependent select:', error);
            window.showMessage('error', 'Error', 'No se pudieron cargar las opciones');
        } finally {
            window.hideLoading();
        }
    }

    bindFormTabs() {
        const tabButtons = document.querySelectorAll('.form-tab-button');
        const tabPanes = document.querySelectorAll('.form-tab-pane');

        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetTab = button.getAttribute('data-tab');
                
                // Desactivar todas las pestañas
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Activar la pestaña seleccionada
                button.classList.add('active');
                const targetPane = document.getElementById(targetTab);
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
    }
}

// ===========================================
// REPORTES Y EXPORTACIÓN
// ===========================================

class AdminReports {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        // Botones de exportación
        const exportButtons = document.querySelectorAll('.btn-export');
        exportButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const format = button.getAttribute('data-format');
                const reportType = button.getAttribute('data-report');
                this.exportReport(reportType, format);
            });
        });

        // Generación de reportes
        const generateButtons = document.querySelectorAll('.btn-generate-report');
        generateButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.generateReport(button);
            });
        });
    }

    async exportReport(reportType, format) {
        try {
            window.showLoading();
            
            const params = this.getReportParams();
            const url = `/admin/reportes/exportar?tipo=${reportType}&formato=${format}&${params}`;
            
            // Crear enlace de descarga
            const link = document.createElement('a');
            link.href = url;
            link.download = `${reportType}-${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            window.showMessage('download', 'Exportación completada', 'El reporte se ha descargado correctamente');
            
        } catch (error) {
            console.error('Error exporting report:', error);
            window.showError('Error de exportación', 'No se pudo generar el reporte');
        } finally {
            window.hideLoading();
        }
    }

    async generateReport(button) {
        const reportForm = button.closest('form');
        if (!reportForm) return;

        try {
            window.showButtonLoading(button);
            
            const formData = new FormData(reportForm);
            const response = await fetch(reportForm.action, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const result = await response.text();
                const reportContainer = document.querySelector('.report-results');
                if (reportContainer) {
                    reportContainer.innerHTML = result;
                }
                
                window.showMessage('success', 'Reporte generado', 'El reporte se ha generado correctamente');
            } else {
                throw new Error('Error generating report');
            }
            
        } catch (error) {
            console.error('Error generating report:', error);
            window.showMessage('error', 'Error', 'No se pudo generar el reporte');
        } finally {
            window.hideButtonLoading(button);
        }
    }

    getReportParams() {
        const form = document.querySelector('.report-filters');
        if (!form) return '';

        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                params.append(key, value);
            }
        }
        
        return params.toString();
    }
}

// ===========================================
// FUNCIONES LEGACY Y COMPATIBILIDAD
// ===========================================

// Validación específica para formulario de menús
function initMenuFormValidation() {
    const form = document.getElementById('menuForm');
    const nombreInput = document.getElementById('menu_nombre');
    const ordenInput = document.getElementById('menu_orden');

    if (!nombreInput || !ordenInput) return;

    // Validación en tiempo real del nombre
    nombreInput.addEventListener('input', function() {
        const valor = this.value.trim();
        if (valor.length > 45) {
            this.setCustomValidity('El nombre no puede exceder 45 caracteres');
        } else if (valor.length < 2) {
            this.setCustomValidity('El nombre debe tener al menos 2 caracteres');
        } else {
            this.setCustomValidity('');
        }
    });

    // Validación del orden
    ordenInput.addEventListener('input', function() {
        const valor = parseInt(this.value);
        if (isNaN(valor) || valor < 1) {
            this.setCustomValidity('El orden debe ser un número mayor a 0');
        } else if (valor > 999) {
            this.setCustomValidity('El orden no puede ser mayor a 999');
        } else {
            this.setCustomValidity('');
        }
    });
}

// ==========================================
// DASHBOARD REPORTES - Gráficos específicos
// ==========================================

// Variable global para almacenar instancias de gráficos - verificar si ya existe
if (typeof dashboardCharts === 'undefined') {
    var dashboardCharts = {
        comentarios: null,
        productos: null,
        ingresos: null,
        cabanas: null
    };
}

// Función para aplicar filtros globales - usa función centralizada
function applyGlobalFilters() {
    applyGlobalDashboardFilters();
}

// Cargar gráficos - función específica del dashboard
function cargarGraficos() {
    cargarGraficoComentarios();
    cargarGraficoProductos();
    cargarGraficoIngresos();
    cargarGraficoCabanas();
}

// Gráfico de comentarios por puntuación
function cargarGraficoComentarios() {
    const baseUrl = window.baseUrl || '';
    fetch(baseUrl + '/reportes/api-graficos?tipo=comentarios_puntuacion')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('comentariosChart').getContext('2d');
            
            if (dashboardCharts.comentarios) {
                dashboardCharts.comentarios.destroy();
            }
            
            dashboardCharts.comentarios = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => `${item.comentario_puntuacion} estrellas`),
                    datasets: [{
                        data: data.map(item => item.cantidad),
                        backgroundColor: [
                            '#ff6b6b', '#ffa726', '#ffca28', '#66bb6a', '#26a69a'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Actualizar estadística
            const totalComentarios = data.reduce((sum, item) => sum + parseInt(item.cantidad), 0);
            const totalElement = document.getElementById('total-comentarios');
            if (totalElement) totalElement.textContent = totalComentarios;
            
            const promedioPonderado = data.reduce((sum, item) => 
                sum + (parseInt(item.comentario_puntuacion) * parseInt(item.cantidad)), 0) / totalComentarios;
            const promedioElement = document.getElementById('promedio-puntuacion');
            if (promedioElement) promedioElement.textContent = promedioPonderado.toFixed(1);
        })
        .catch(error => console.error('Error cargar gráfico comentarios:', error));
}

// Gráfico de productos más vendidos
function cargarGraficoProductos() {
    const baseUrl = window.baseUrl || '';
    fetch(baseUrl + '/reportes/api-graficos?tipo=productos_top')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('productosChart').getContext('2d');
            
            if (dashboardCharts.productos) {
                dashboardCharts.productos.destroy();
            }
            
            dashboardCharts.productos = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => item.producto_nombre.substring(0, 15) + '...'),
                    datasets: [{
                        label: 'Cantidad Vendida',
                        data: data.map(item => item.total_vendido),
                        backgroundColor: '#667eea',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error cargar gráfico productos:', error));
}

// Gráfico de ingresos mensuales
function cargarGraficoIngresos() {
    const baseUrl = window.baseUrl || '';
    fetch(baseUrl + '/reportes/api-graficos?tipo=ingresos_mensuales')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('ingresosChart').getContext('2d');
            
            if (dashboardCharts.ingresos) {
                dashboardCharts.ingresos.destroy();
            }
            
            dashboardCharts.ingresos = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.periodo),
                    datasets: [
                        {
                            label: 'Reservas',
                            data: data.map(item => item.ingresos_reservas),
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Consumos',
                            data: data.map(item => item.ingresos_consumos),
                            borderColor: '#f093fb',
                            backgroundColor: 'rgba(240, 147, 251, 0.1)',
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error cargar gráfico ingresos:', error));
}

// Gráfico de cabañas populares
function cargarGraficoCabanas() {
    const baseUrl = window.baseUrl || '';
    fetch(baseUrl + '/reportes/api-graficos?tipo=cabanas_populares')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('cabanasChart').getContext('2d');
            
            if (dashboardCharts.cabanas) {
                dashboardCharts.cabanas.destroy();
            }
            
            dashboardCharts.cabanas = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: data.map(item => item.cabania_nombre),
                    datasets: [{
                        label: 'Reservas',
                        data: data.map(item => item.total_reservas),
                        backgroundColor: [
                            '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error cargar gráfico cabañas:', error));
}

// ===========================================
// INICIALIZACIÓN
// ===========================================

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar dashboard si existe
    if (document.querySelector('.dashboard-stats')) {
        window.initDashboard();
    }
    
    // Inicializar sistemas admin solo si estamos en área administrativa
    if (document.body.classList.contains('admin-area') || window.location.pathname.includes('admin')) {
        window.adminDashboard = new AdminDashboard();
        window.adminTables = new AdminTables();
        window.adminForms = new AdminForms();
        window.adminReports = new AdminReports();

        // Funciones globales para compatibility
        window.initMenuFormValidation = initMenuFormValidation;
        window.cargarGraficos = cargarGraficos;

        console.log('Casa de Palos - Admin System loaded successfully');
    }
});
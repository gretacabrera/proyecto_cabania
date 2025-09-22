<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reporte;
use Exception;

/**
 * Controlador para el sistema de reportes y análisis
 */
class ReportesController extends Controller
{
    private $reporteModel;

    public function __construct()
    {
        parent::__construct();
        $this->reporteModel = new Reporte();
    }

    /**
     * Dashboard principal de reportes
     */
    public function index()
    {
        try {
            $estadisticas = $this->reporteModel->getEstadisticasGenerales();
            $filtrosData = $this->reporteModel->getFiltrosData();

            $this->render('admin/reportes/dashboard', [
                'title' => 'Dashboard de Reportes',
                'estadisticas' => $estadisticas,
                'filtros' => $filtrosData
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al cargar el dashboard: ' . $e->getMessage());
            redirect('');
        }
    }

    /**
     * Reporte de comentarios
     */
    public function comentarios()
    {
        try {
            $filtros = [
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                'puntuacion' => $_GET['puntuacion'] ?? '',
                'cabania' => $_GET['cabania'] ?? ''
            ];

            $page = intval($_GET['page'] ?? 1);
            $perPage = intval($_GET['per_page'] ?? 10);

            $resultado = $this->reporteModel->getComentarios($filtros, $page, $perPage);
            $filtrosData = $this->reporteModel->getFiltrosData();

            // Si es una petición AJAX (para exportar), devolver JSON
            if (isset($_GET['export']) && $_GET['export'] === 'json') {
                header('Content-Type: application/json');
                echo json_encode($resultado);
                return;
            }

            $this->render('admin/reportes/comentarios', [
                'title' => 'Reporte de Comentarios',
                'resultado' => $resultado,
                'filtros' => $filtros,
                'filtrosData' => $filtrosData,
                'currentUrl' => '/admin/reportes/comentarios'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al generar reporte de comentarios: ' . $e->getMessage());
            redirect('/reportes');
        }
    }

    /**
     * Exportar comentarios a Excel
     */
    public function exportarComentarios()
    {
        try {
            $filtros = [
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                'puntuacion' => $_GET['puntuacion'] ?? '',
                'cabania' => $_GET['cabania'] ?? ''
            ];

            // Obtener todos los datos sin paginación
            $resultado = $this->reporteModel->getComentarios($filtros, 1, 999999);
            
            $this->generateExcelExport($resultado['data'], 'Reporte_Comentarios', [
                'ID' => 'id_comentario',
                'Contenido' => 'comentario_contenido',
                'Puntuación' => 'comentario_puntuacion',
                'Fecha' => 'comentario_fechahora',
                'Cabaña' => 'cabania_nombre',
                'Huésped' => 'huesped_nombre_completo',
                'Email' => 'huesped_email',
                'Categoría' => 'categoria_puntuacion',
                'Respuesta' => 'comentario_respuesta'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al exportar comentarios: ' . $e->getMessage());
            redirect('/admin/reportes/comentarios');
        }
    }

    /**
     * Reporte de consumos por cabaña
     */
    public function consumos()
    {
        try {
            $filtros = [
                'cabania' => $_GET['cabania'] ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? ''
            ];

            $page = intval($_GET['page'] ?? 1);
            $perPage = intval($_GET['per_page'] ?? 10);

            $resultado = $this->reporteModel->getConsumosPorCabania($filtros, $page, $perPage);
            $filtrosData = $this->reporteModel->getFiltrosData();

            if (isset($_GET['export']) && $_GET['export'] === 'json') {
                header('Content-Type: application/json');
                echo json_encode($resultado);
                return;
            }

            $this->render('admin/reportes/consumos', [
                'title' => 'Reporte de Consumos por Cabaña',
                'resultado' => $resultado,
                'filtros' => $filtros,
                'filtrosData' => $filtrosData,
                'currentUrl' => '/admin/reportes/consumos'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al generar reporte de consumos: ' . $e->getMessage());
            redirect('/reportes');
        }
    }

    /**
     * Exportar consumos a Excel
     */
    public function exportarConsumos()
    {
        try {
            $filtros = [
                'cabania' => $_GET['cabania'] ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? ''
            ];

            $resultado = $this->reporteModel->getConsumosPorCabania($filtros, 1, 999999);
            
            $this->generateExcelExport($resultado['data'], 'Consumos_Por_Cabania', [
                'ID' => 'id_cabania',
                'Cabaña' => 'cabania_nombre',
                'Código' => 'cabania_codigo',
                'Total Reservas' => 'total_reservas',
                'Total Consumos' => 'total_consumos',
                'Importe Total ($)' => 'total_importe_pesos',
                'Promedio por Reserva ($)' => 'promedio_por_reserva',
                'Primera Fecha' => 'primera_fecha',
                'Última Fecha' => 'ultima_fecha',
                'Productos Consumidos' => 'productos_consumidos'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al exportar consumos: ' . $e->getMessage());
            redirect('/admin/reportes/consumos');
        }
    }

    /**
     * Reporte de productos por categoría
     */
    public function productos()
    {
        try {
            $filtros = [
                'producto_nombre' => $_GET['producto_nombre'] ?? '',
                'marca' => $_GET['marca'] ?? '',
                'estado_producto' => $_GET['estado_producto'] ?? ''
            ];

            $page = intval($_GET['page'] ?? 1);
            $perPage = intval($_GET['per_page'] ?? 10);

            $resultado = $this->reporteModel->getProductosPorCategoria($filtros, $page, $perPage);
            $filtrosData = $this->reporteModel->getFiltrosData();

            if (isset($_GET['export']) && $_GET['export'] === 'json') {
                header('Content-Type: application/json');
                echo json_encode($resultado);
                return;
            }

            $this->render('admin/reportes/productos', [
                'title' => 'Reporte de Productos por Categoría',
                'resultado' => $resultado,
                'filtros' => $filtros,
                'filtrosData' => $filtrosData,
                'currentUrl' => '/admin/reportes/productos'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al generar reporte de productos: ' . $e->getMessage());
            redirect('/reportes');
        }
    }

    /**
     * Exportar productos a Excel
     */
    public function exportarProductos()
    {
        try {
            $filtros = [
                'producto_nombre' => $_GET['producto_nombre'] ?? '',
                'marca' => $_GET['marca'] ?? '',
                'estado_producto' => $_GET['estado_producto'] ?? ''
            ];

            $resultado = $this->reporteModel->getProductosPorCategoria($filtros, 1, 999999);
            
            $this->generateExcelExport($resultado['data'], 'Productos_Por_Categoria', [
                'Categoría' => 'categoria_descripcion',
                'Cantidad Productos' => 'cantidad_productos',
                'Precio Promedio ($)' => 'precio_promedio',
                'Precio Mínimo ($)' => 'precio_minimo',
                'Precio Máximo ($)' => 'precio_maximo',
                'Total Vendido' => 'total_vendido',
                'Detalles de Productos' => 'productos_detalle'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al exportar productos: ' . $e->getMessage());
            redirect('/admin/reportes/productos');
        }
    }

    /**
     * Reporte de temporadas altas
     */
    public function temporadas()
    {
        try {
            $filtros = [
                'anio' => $_GET['anio'] ?? ''
            ];

            $page = intval($_GET['page'] ?? 1);
            $perPage = intval($_GET['per_page'] ?? 10);

            $resultado = $this->reporteModel->getTemporadasAltas($filtros, $page, $perPage);
            $filtrosData = $this->reporteModel->getFiltrosData();

            if (isset($_GET['export']) && $_GET['export'] === 'json') {
                header('Content-Type: application/json');
                echo json_encode($resultado);
                return;
            }

            $this->render('admin/reportes/temporadas', [
                'title' => 'Reporte de Temporadas Altas por Año',
                'resultado' => $resultado,
                'filtros' => $filtros,
                'filtrosData' => $filtrosData,
                'currentUrl' => '/admin/reportes/temporadas'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al generar reporte de temporadas: ' . $e->getMessage());
            redirect('/reportes');
        }
    }

    /**
     * Exportar temporadas a Excel
     */
    public function exportarTemporadas()
    {
        try {
            $filtros = [
                'anio' => $_GET['anio'] ?? ''
            ];

            $resultado = $this->reporteModel->getTemporadasAltas($filtros, 1, 999999);
            
            $this->generateExcelExport($resultado['data'], 'Temporadas_Altas', [
                'Año' => 'anio',
                'Temporada Alta' => 'temporada_alta',
                'Total Reservas' => 'total_reservas',
                'Cabañas Ocupadas' => 'cabanas_ocupadas',
                'Promedio Días Estadía' => 'promedio_dias_estadia',
                'Ingresos Totales ($)' => 'ingresos_totales',
                'Ingreso Promedio ($)' => 'ingreso_promedio_reserva',
                'Primera Reserva' => 'primera_reserva',
                'Última Reserva' => 'ultima_reserva',
                'Cabañas Utilizadas' => 'cabanas_utilizadas'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al exportar temporadas: ' . $e->getMessage());
            redirect('/admin/reportes/temporadas');
        }
    }

    /**
     * Reporte de grupos etarios
     */
    public function demografico()
    {
        try {
            $filtros = [
                'periodo' => $_GET['periodo'] ?? ''
            ];

            $page = intval($_GET['page'] ?? 1);
            $perPage = intval($_GET['per_page'] ?? 15);

            $resultado = $this->reporteModel->getGruposEtarios($filtros, $page, $perPage);
            $filtrosData = $this->reporteModel->getFiltrosData();

            if (isset($_GET['export']) && $_GET['export'] === 'json') {
                header('Content-Type: application/json');
                echo json_encode($resultado);
                return;
            }

            $this->render('admin/reportes/demografico', [
                'title' => 'Análisis Demográfico por Grupos Etarios',
                'resultado' => $resultado,
                'filtros' => $filtros,
                'filtrosData' => $filtrosData,
                'currentUrl' => '/admin/reportes/demografico'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al generar análisis demográfico: ' . $e->getMessage());
            redirect('/reportes');
        }
    }

    /**
     * Exportar análisis demográfico a Excel
     */
    public function exportarDemografico()
    {
        try {
            $filtros = [
                'periodo' => $_GET['periodo'] ?? ''
            ];

            $resultado = $this->reporteModel->getGruposEtarios($filtros, 1, 999999);
            
            $this->generateExcelExport($resultado['data'], 'Analisis_Demografico', [
                'Período' => 'periodo_descripcion',
                'Grupo Etario' => 'grupo_etario',
                'Cantidad Reservas' => 'cantidad_reservas',
                'Huéspedes Únicos' => 'huespedes_unicos',
                'Edad Promedio' => 'edad_promedio',
                'Edad Mínima' => 'edad_minima',
                'Edad Máxima' => 'edad_maxima',
                'Gasto Promedio ($)' => 'gasto_promedio',
                'Gasto Total ($)' => 'gasto_total'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al exportar análisis demográfico: ' . $e->getMessage());
            redirect('/admin/reportes/demografico');
        }
    }

    /**
     * Reporte de productos más vendidos por mes
     */
    public function ventasMensuales()
    {
        try {
            $filtros = [
                'anio' => $_GET['anio'] ?? date('Y')
            ];

            $resultado = $this->reporteModel->getProductoMasVendidoPorMes($filtros);
            $filtrosData = $this->reporteModel->getFiltrosData();

            if (isset($_GET['export']) && $_GET['export'] === 'json') {
                header('Content-Type: application/json');
                echo json_encode(['data' => $resultado]);
                return;
            }

            $this->render('admin/reportes/ventas_mensuales', [
                'title' => 'Productos Más Vendidos por Mes',
                'productos' => $resultado,
                'filtros' => $filtros,
                'filtrosData' => $filtrosData,
                'currentUrl' => '/admin/reportes/ventas-mensuales'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al generar reporte de ventas mensuales: ' . $e->getMessage());
            redirect('/reportes');
        }
    }

    /**
     * Exportar ventas mensuales a Excel
     */
    public function exportarVentasMensuales()
    {
        try {
            $filtros = [
                'anio' => $_GET['anio'] ?? date('Y')
            ];

            $resultado = $this->reporteModel->getProductoMasVendidoPorMes($filtros);
            
            $this->generateExcelExport($resultado, 'Ventas_Mensuales', [
                'Año' => 'anio',
                'Mes' => 'mes',
                'Nombre Mes' => 'nombre_mes',
                'Producto' => 'producto_nombre',
                'Categoría' => 'categoria_descripcion',
                'Marca' => 'marca_descripcion',
                'Total Vendido' => 'total_vendido',
                'Reservas Diferentes' => 'reservas_diferentes',
                'Promedio por Reserva' => 'promedio_por_reserva',
                'Ingresos Generados ($)' => 'ingresos_generados'
            ]);
        } catch (Exception $e) {
            flash('error', 'Error al exportar ventas mensuales: ' . $e->getMessage());
            redirect('/admin/reportes/ventas-mensuales');
        }
    }

    /**
     * Genera una exportación a Excel/CSV
     * 
     * @param array $data Los datos a exportar
     * @param string $filename Nombre del archivo
     * @param array $headers Mapeo de headers ['Display Name' => 'field_key']
     */
    private function generateExcelExport($data, $filename, $headers)
    {
        // Configurar headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Cache-Control: max-age=0');

        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // Escribir BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir headers
        fputcsv($output, array_keys($headers), ';');

        // Escribir datos
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $displayName => $fieldKey) {
                $value = $row[$fieldKey] ?? '';
                // Formatear números con decimales
                if (is_numeric($value) && strpos($fieldKey, 'precio') !== false || strpos($fieldKey, 'importe') !== false || strpos($fieldKey, 'promedio') !== false) {
                    $value = number_format(floatval($value), 2, ',', '.');
                }
                $csvRow[] = $value;
            }
            fputcsv($output, $csvRow, ';');
        }

        fclose($output);
        exit;
    }

    /**
     * API endpoint para obtener datos de gráficos
     */
    public function apiGraficos()
    {
        header('Content-Type: application/json');
        
        try {
            $tipo = $_GET['tipo'] ?? '';
            
            switch ($tipo) {
                case 'comentarios_puntuacion':
                    $stats = $this->reporteModel->getEstadisticasGenerales();
                    echo json_encode($stats['comentarios_por_puntuacion']);
                    break;
                    
                case 'productos_top':
                    $stats = $this->reporteModel->getEstadisticasGenerales();
                    echo json_encode($stats['top_productos']);
                    break;
                    
                case 'ingresos_mensuales':
                    $stats = $this->reporteModel->getEstadisticasGenerales();
                    echo json_encode($stats['ingresos_mensuales']);
                    break;
                    
                case 'cabanas_populares':
                    $stats = $this->reporteModel->getEstadisticasGenerales();
                    echo json_encode($stats['cabanas_populares']);
                    break;
                    
                default:
                    echo json_encode(['error' => 'Tipo de gráfico no válido']);
                    break;
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

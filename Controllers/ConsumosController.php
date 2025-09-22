<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Consumo;

/**
 * Controlador para la gestión de consumos
 */
class ConsumosController extends Controller
{
    protected $consumoModel;

    public function __construct()
    {
        parent::__construct();
        $this->consumoModel = new Consumo();
    }

    /**
     * Listar todos los consumos
     */
    public function index()
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $filters = [
            'reserva' => $this->get('reserva'),
            'producto' => $this->get('producto'),
            'fecha_desde' => $this->get('fecha_desde'),
            'fecha_hasta' => $this->get('fecha_hasta')
        ];

        $consumos = $this->consumoModel->search($filters, $page);
        $totalPages = $this->consumoModel->getTotalPages($filters);

        // Obtener datos para filtros
        $reservas = $this->consumoModel->getReservasActivas();
        $productos = $this->consumoModel->getProductosActivos();

        $data = [
            'title' => 'Gestión de Consumos',
            'consumos' => $consumos,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'reservas' => $reservas,
            'productos' => $productos
        ];

        return $this->render('admin/operaciones/consumos/listado', $data);
    }

    /**
     * Mostrar formulario de nuevo consumo
     */
    public function create()
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'rela_reserva' => $this->post('rela_reserva'),
                'rela_producto' => $this->post('rela_producto'),
                'consumo_cantidad' => $this->post('consumo_cantidad'),
                'consumo_precio_unitario' => $this->post('consumo_precio_unitario'),
                'consumo_subtotal' => $this->post('consumo_cantidad') * $this->post('consumo_precio_unitario'),
                'consumo_fecha' => $this->post('consumo_fecha') ?: date('Y-m-d H:i:s'),
                'consumo_observaciones' => $this->post('consumo_observaciones', ''),
                'consumo_estado' => 1
            ];

            if ($this->consumoModel->create($data)) {
                $this->redirect('/consumos', 'Consumo registrado exitosamente', 'exito');
            } else {
                $this->redirect('/admin/operaciones/consumos/create', 'Error al registrar el consumo', 'error');
            }
        }

        // Obtener reservas activas y productos
        $reservas = $this->consumoModel->getReservasActivas();
        $productos = $this->consumoModel->getProductosActivos();

        $data = [
            'title' => 'Registrar Consumo',
            'reservas' => $reservas,
            'productos' => $productos
        ];

        return $this->render('admin/operaciones/consumos/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $consumo = $this->consumoModel->findWithRelations($id);
        if (!$consumo) {
            $this->redirect('/consumos', 'Consumo no encontrado', 'error');
        }

        if ($this->isPost()) {
            $data = [
                'rela_reserva' => $this->post('rela_reserva'),
                'rela_producto' => $this->post('rela_producto'),
                'consumo_cantidad' => $this->post('consumo_cantidad'),
                'consumo_precio_unitario' => $this->post('consumo_precio_unitario'),
                'consumo_subtotal' => $this->post('consumo_cantidad') * $this->post('consumo_precio_unitario'),
                'consumo_fecha' => $this->post('consumo_fecha'),
                'consumo_observaciones' => $this->post('consumo_observaciones', '')
            ];

            if ($this->consumoModel->update($id, $data)) {
                $this->redirect('/consumos', 'Consumo actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/admin/operaciones/consumos/{$id}/edit", 'Error al actualizar el consumo', 'error');
            }
        }

        $reservas = $this->consumoModel->getReservasActivas();
        $productos = $this->consumoModel->getProductosActivos();

        $data = [
            'title' => 'Editar Consumo',
            'consumo' => $consumo,
            'reservas' => $reservas,
            'productos' => $productos
        ];

        return $this->render('admin/operaciones/consumos/formulario', $data);
    }

    /**
     * Ver detalle del consumo
     */
    public function show($id)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $consumo = $this->consumoModel->findWithRelations($id);
        if (!$consumo) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Detalle del Consumo',
            'consumo' => $consumo
        ];

        return $this->render('admin/operaciones/consumos/detalle', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        if ($this->consumoModel->softDelete($id)) {
            $this->redirect('/consumos', 'Consumo eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/consumos', 'Error al eliminar el consumo', 'error');
        }
    }

    /**
     * Restaurar consumo
     */
    public function restore($id)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        if ($this->consumoModel->restore($id)) {
            $this->redirect('/consumos', 'Consumo restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/consumos', 'Error al restaurar el consumo', 'error');
        }
    }

    /**
     * Ver consumos por reserva
     */
    public function byReserva($reservaId)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $consumos = $this->consumoModel->getByReserva($reservaId, $page);
        $reserva = $this->consumoModel->getReservaInfo($reservaId);

        if (!$reserva) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Consumos de la Reserva',
            'consumos' => $consumos,
            'reserva' => $reserva,
            'currentPage' => $page
        ];

        return $this->render('admin/operaciones/consumos/por_reserva', $data);
    }

    /**
     * Facturar consumos
     */
    public function facturar($reservaId)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $consumos = $this->consumoModel->getPendingByReserva($reservaId);
        $reserva = $this->consumoModel->getReservaInfo($reservaId);

        if (!$reserva) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            $consumosIds = $this->post('consumos', []);
            
            if (empty($consumosIds)) {
                $this->redirect("/admin/operaciones/consumos/facturar/{$reservaId}", 'Debe seleccionar al menos un consumo', 'error');
            }

            if ($this->consumoModel->marcarComoFacturados($consumosIds)) {
                $this->redirect('/consumos', 'Consumos facturados exitosamente', 'exito');
            } else {
                $this->redirect("/admin/operaciones/consumos/facturar/{$reservaId}", 'Error al facturar consumos', 'error');
            }
        }

        $data = [
            'title' => 'Facturar Consumos',
            'consumos' => $consumos,
            'reserva' => $reserva
        ];

        return $this->render('admin/operaciones/consumos/facturar', $data);
    }

    /**
     * Obtener precio actual del producto (AJAX)
     */
    public function getPrecioProducto($productoId)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->json(['error' => 'Sin permisos'], 403);
        }

        $producto = $this->consumoModel->getProducto($productoId);
        
        if ($producto) {
            return $this->json(['precio' => $producto['producto_precio']]);
        } else {
            return $this->json(['error' => 'Producto no encontrado'], 404);
        }
    }

    /**
     * Reporte de consumos
     */
    public function reporte()
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $fechaDesde = $this->get('fecha_desde');
        $fechaHasta = $this->get('fecha_hasta');
        $tipoReporte = $this->get('tipo', 'resumen');

        $reporteData = [];
        
        if ($fechaDesde && $fechaHasta) {
            switch ($tipoReporte) {
                case 'resumen':
                    $reporteData = $this->consumoModel->getResumenConsumos($fechaDesde, $fechaHasta);
                    break;
                case 'detallado':
                    $reporteData = $this->consumoModel->getDetalleConsumos($fechaDesde, $fechaHasta);
                    break;
                case 'productos':
                    $reporteData = $this->consumoModel->getConsumosPorProducto($fechaDesde, $fechaHasta);
                    break;
            }
        }

        $data = [
            'title' => 'Reporte de Consumos',
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'tipoReporte' => $tipoReporte,
            'reporteData' => $reporteData
        ];

        return $this->render('admin/operaciones/consumos/reporte', $data);
    }
}

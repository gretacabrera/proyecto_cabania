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
        $this->requirePermission('consumos');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $filters = [
            'huesped' => $this->get('huesped'),
            'reserva' => $this->get('reserva'),
            'producto' => $this->get('producto'),
            'servicio' => $this->get('servicio'),
            'estado' => $this->get('estado')
        ];

        $result = $this->consumoModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Consumos',
            'consumos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/consumos/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo consumo (múltiple)
     */
    public function create()
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            // Obtener ID de reserva
            $rela_reserva = $this->post('rela_reserva');
            
            // Validar reserva
            if (empty($rela_reserva)) {
                $this->redirect('/consumos/create', 'Debe seleccionar una reserva', 'error');
                return;
            }

            // Verificar si es creación múltiple o simple
            $items = $this->post('items');
            $cantidades = $this->post('cantidades');
            
            if (is_array($items) && is_array($cantidades)) {
                // MODO MÚLTIPLE - procesar array de items
                $registrosExitosos = 0;
                $errores = [];

                for ($i = 0; $i < count($items); $i++) {
                    if (empty($items[$i])) continue;

                    // Parsear item (formato: p_123 o s_456)
                    $itemParts = explode('_', $items[$i]);
                    if (count($itemParts) != 2) continue;

                    $tipo = $itemParts[0]; // 'p' para producto, 's' para servicio
                    $itemId = $itemParts[1];
                    $cantidad = floatval($cantidades[$i] ?? 1);

                    // Obtener datos del item
                    $itemData = null;
                    $descripcion = '';
                    $precioUnitario = 0;

                    if ($tipo == 'p') {
                        // Es producto
                        $itemData = $this->consumoModel->getProductoById($itemId);
                        if ($itemData) {
                            $descripcion = "Producto: " . $itemData['producto_nombre'];
                            $precioUnitario = floatval($itemData['producto_precio']);
                        }
                        $data = [
                            'rela_reserva' => $rela_reserva,
                            'rela_producto' => $itemId,
                            'rela_servicio' => null,
                            'consumo_descripcion' => $descripcion,
                            'consumo_cantidad' => $cantidad,
                            'consumo_total' => $precioUnitario * $cantidad,
                            'consumo_estado' => 1
                        ];
                    } else if ($tipo == 's') {
                        // Es servicio
                        $itemData = $this->consumoModel->getServicioById($itemId);
                        if ($itemData) {
                            $descripcion = "Servicio: " . $itemData['servicio_nombre'];
                            $precioUnitario = floatval($itemData['servicio_precio']);
                        }
                        $data = [
                            'rela_reserva' => $rela_reserva,
                            'rela_producto' => null,
                            'rela_servicio' => $itemId,
                            'consumo_descripcion' => $descripcion,
                            'consumo_cantidad' => $cantidad,
                            'consumo_total' => $precioUnitario * $cantidad,
                            'consumo_estado' => 1
                        ];
                    }

                    // Crear registro
                    if (isset($data) && $this->consumoModel->create($data)) {
                        $registrosExitosos++;
                    } else {
                        $errores[] = "Error al registrar: " . $descripcion;
                    }
                }

                if ($registrosExitosos > 0) {
                    $mensaje = "{$registrosExitosos} consumo(s) registrado(s) exitosamente";
                    if (count($errores) > 0) {
                        $mensaje .= " (con " . count($errores) . " error(es))";
                    }
                    $this->redirect('/consumos', $mensaje, 'success');
                } else {
                    $this->redirect('/consumos/create', 'No se pudo registrar ningún consumo', 'error');
                }
            } else {
                // MODO SIMPLE (no debería llegar aquí en creación, pero por compatibilidad)
                $this->redirect('/consumos/create', 'Datos de formulario inválidos', 'error');
            }
            
            return;
        }

        // Obtener reservas activas, productos y servicios
        $reservas = $this->consumoModel->getReservasActivas();
        $productos = $this->consumoModel->getProductosActivos();
        $servicios = $this->consumoModel->getServiciosActivos();

        $data = [
            'title' => 'Registrar Consumos',
            'reservas' => $reservas,
            'productos' => $productos,
            'servicios' => $servicios,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/consumos/formulario', $data, 'main');
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
            // Obtener datos del formulario
            $data = [
                'rela_reserva' => $this->post('rela_reserva'),
                'rela_producto' => $this->post('rela_producto') ?: null,
                'rela_servicio' => $this->post('rela_servicio') ?: null,
                'consumo_descripcion' => $this->post('consumo_descripcion'),
                'consumo_cantidad' => floatval($this->post('consumo_cantidad', 1)),
                'consumo_total' => floatval($this->post('consumo_total', 0))
            ];
            
            // Validar datos básicos
            if (empty($data['rela_producto']) && empty($data['rela_servicio'])) {
                $this->redirect("/consumos/{$id}/edit", 'Debe seleccionar un producto o un servicio', 'error');
                return;
            }
            
            if (empty($data['consumo_descripcion'])) {
                $this->redirect("/consumos/{$id}/edit", 'La descripción es obligatoria', 'error');
                return;
            }

            if ($this->consumoModel->update($id, $data)) {
                $this->redirect('/consumos', 'Consumo actualizado exitosamente', 'success');
            } else {
                $this->redirect("/consumos/{$id}/edit", 'Error al actualizar el consumo', 'error');
            }
            
            return;
        }

        $reservas = $this->consumoModel->getReservasActivas();
        $productos = $this->consumoModel->getProductosActivos();
        $servicios = $this->consumoModel->getServiciosActivos();

        $data = [
            'title' => 'Editar Consumo',
            'consumo' => $consumo,
            'reservas' => $reservas,
            'productos' => $productos,
            'servicios' => $servicios,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/consumos/formulario', $data, 'main');
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
            'consumo' => $consumo,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/consumos/detalle', $data, 'main');
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
     * Cambiar estado del consumo (AJAX)
     */
    public function cambiarEstado($id)
    {
        if (!$this->hasPermission('consumos')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        $consumo = $this->consumoModel->find($id);
        if (!$consumo) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Consumo no encontrado']);
            return;
        }

        $nuevoEstado = $consumo['consumo_estado'] == 1 ? 0 : 1;
        
        if ($this->consumoModel->update($id, ['consumo_estado' => $nuevoEstado])) {
            $mensaje = $nuevoEstado == 1 ? 'Consumo activado' : 'Consumo desactivado';
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $mensaje, 'nuevoEstado' => $nuevoEstado]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al cambiar estado']);
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

    /**
     * Exportar consumos a Excel
     */
    public function exportar()
    {
        $this->requirePermission('consumos');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'huesped' => $this->get('huesped'),
                'reserva' => $this->get('reserva'),
                'producto' => $this->get('producto'),
                'servicio' => $this->get('servicio'),
                'estado' => $this->get('estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->consumoModel->getAllWithDetailsForExport($filters);
            $consumos = $result['data'];

            if (empty($consumos)) {
                $this->redirect('/consumos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Consumos');

            // Definir encabezados
            $headers = [
                'A1' => 'ID',
                'B1' => 'Reserva',
                'C1' => 'Huésped',
                'D1' => 'Descripción',
                'E1' => 'Cantidad',
                'F1' => 'Precio Unit.',
                'G1' => 'Total',
                'H1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:H1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:H1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($consumos as $consumo) {
                $estadoTexto = $consumo['consumo_estado'] == 1 ? 'Activo' : 'Inactivo';
                $huesped = trim(($consumo['huesped_nombre'] ?? '') . ' ' . ($consumo['huesped_apellido'] ?? ''));
                $precioUnitario = $consumo['consumo_cantidad'] > 0 ? $consumo['consumo_total'] / $consumo['consumo_cantidad'] : 0;

                $worksheet->setCellValue('A' . $row, $consumo['id_consumo']);
                $worksheet->setCellValue('B' . $row, '#' . $consumo['rela_reserva']);
                $worksheet->setCellValue('C' . $row, $huesped ?: 'N/A');
                $worksheet->setCellValue('D' . $row, $consumo['consumo_descripcion']);
                $worksheet->setCellValue('E' . $row, $consumo['consumo_cantidad']);
                $worksheet->setCellValue('F' . $row, number_format($precioUnitario, 2));
                $worksheet->setCellValue('G' . $row, number_format($consumo['consumo_total'], 2));
                $worksheet->setCellValue('H' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(8);
            $worksheet->getColumnDimension('B')->setWidth(12);
            $worksheet->getColumnDimension('C')->setWidth(25);
            $worksheet->getColumnDimension('D')->setWidth(40);
            $worksheet->getColumnDimension('E')->setWidth(12);
            $worksheet->getColumnDimension('F')->setWidth(15);
            $worksheet->getColumnDimension('G')->setWidth(15);
            $worksheet->getColumnDimension('H')->setWidth(12);

            // Aplicar formato a las columnas de precio
            $worksheet->getStyle('F2:G' . ($row - 1))->getNumberFormat()->setFormatCode('$#,##0.00');

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "consumos_{$fecha}.xlsx";

            // Headers para descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            // Enviar archivo
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar consumos: " . $e->getMessage());
            $this->redirect('/consumos', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar consumos a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('consumos');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'huesped' => $this->get('huesped'),
                'reserva' => $this->get('reserva'),
                'producto' => $this->get('producto'),
                'servicio' => $this->get('servicio'),
                'estado' => $this->get('estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->consumoModel->getAllWithDetailsForExport($filters);
            $consumos = $result['data'];

            if (empty($consumos)) {
                $this->redirect('/consumos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Configurar documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Reporte de Consumos');
            $pdf->SetSubject('Listado de Consumos');

            // Remover header/footer por defecto
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Configurar márgenes
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);

            // Agregar página
            $pdf->AddPage();

            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Reporte de Consumos', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados
            $pdf->SetFont('helvetica', '', 9);
            $filtrosAplicados = [];
            if (!empty($filters['huesped'])) $filtrosAplicados[] = "Huésped: {$filters['huesped']}";
            if (!empty($filters['reserva'])) $filtrosAplicados[] = "Reserva: #{$filters['reserva']}";
            if (!empty($filters['producto'])) $filtrosAplicados[] = "Producto: {$filters['producto']}";
            if (!empty($filters['servicio'])) $filtrosAplicados[] = "Servicio: {$filters['servicio']}";
            if (isset($filters['estado']) && $filters['estado'] !== '') {
                $filtrosAplicados[] = "Estado: " . ($filters['estado'] == 1 ? 'Activo' : 'Inactivo');
            }

            if (!empty($filtrosAplicados)) {
                $pdf->Cell(0, 5, 'Filtros aplicados: ' . implode(' | ', $filtrosAplicados), 0, 1);
            }
            $pdf->Cell(0, 5, 'Total de registros: ' . count($consumos), 0, 1);
            $pdf->Cell(0, 5, 'Fecha de generación: ' . date('d/m/Y H:i:s'), 0, 1);
            $pdf->Ln(5);

            // Crear tabla HTML
            $html = '<table border="1" cellpadding="4" cellspacing="0" style="font-size: 8px;">
                <thead>
                    <tr style="background-color: #E3F2FD; font-weight: bold;">
                        <th width="8%">Reserva</th>
                        <th width="20%">Huésped</th>
                        <th width="35%">Descripción</th>
                        <th width="8%">Cant.</th>
                        <th width="12%">P. Unit.</th>
                        <th width="12%">Total</th>
                        <th width="10%">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($consumos as $consumo) {
                $estadoTexto = $consumo['consumo_estado'] == 1 ? 'Activo' : 'Inactivo';
                $huesped = trim(($consumo['huesped_nombre'] ?? '') . ' ' . ($consumo['huesped_apellido'] ?? ''));
                $precioUnitario = $consumo['consumo_cantidad'] > 0 ? $consumo['consumo_total'] / $consumo['consumo_cantidad'] : 0;
                $descripcion = substr($consumo['consumo_descripcion'], 0, 60) . (strlen($consumo['consumo_descripcion']) > 60 ? '...' : '');

                $html .= '<tr>
                    <td width="8%">#' . $consumo['rela_reserva'] . '</td>
                    <td width="20%">' . htmlspecialchars($huesped ?: 'N/A') . '</td>
                    <td width="35%">' . htmlspecialchars($descripcion) . '</td>
                    <td width="8%" align="center">' . $consumo['consumo_cantidad'] . '</td>
                    <td width="12%" align="right">$' . number_format($precioUnitario, 2) . '</td>
                    <td width="12%" align="right">$' . number_format($consumo['consumo_total'], 2) . '</td>
                    <td width="10%" align="center">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir tabla
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo
            $fecha = date('Y-m-d');
            $nombreArchivo = "consumos_{$fecha}.pdf";

            // Salida del PDF
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar PDF de consumos: " . $e->getMessage());
            $this->redirect('/consumos', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cabania;

/**
 * Controlador para el manejo de cabañas
 */
class CabaniasController extends Controller
{
    protected $cabaniaModel;

    public function __construct()
    {
        parent::__construct();
        $this->cabaniaModel = new Cabania();
    }

    /**
     * Listar cabañas
     */
    public function index()
    {
        $this->requirePermission('cabanias');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'cabania_codigo' => $this->get('cabania_codigo'),
            'cabania_nombre' => $this->get('cabania_nombre'),
            'cabania_ubicacion' => $this->get('cabania_ubicacion'),
            'cabania_capacidad' => $this->get('cabania_capacidad'),
            'cabania_habitaciones' => $this->get('cabania_habitaciones'),
            'cabania_banios' => $this->get('cabania_banios'),
            'cabania_estado' => $this->get('cabania_estado')
        ];

        $result = $this->cabaniaModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Cabañas',
            'cabanias' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cabanias/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva cabaña
     */
    public function create()
    {
        $this->requirePermission('cabanias');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nueva Cabaña',
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cabanias/formulario', $data, 'main');
    }

    /**
     * Guardar nueva cabaña
     */
    public function store()
    {
        $this->requirePermission('cabanias');

        // Manejar subida de foto
        $cabania_foto = null;
        if (isset($_FILES['cabania_foto']) && $_FILES['cabania_foto']['error'] == 0) {
            $target_dir = "imagenes/cabanias/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["cabania_foto"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["cabania_foto"]["tmp_name"], $target_file)) {
                $cabania_foto = $new_filename;
            }
        }

        $data = [
            'cabania_codigo' => $this->post('cabania_codigo'),
            'cabania_nombre' => $this->post('cabania_nombre'),
            'cabania_descripcion' => $this->post('cabania_descripcion'),
            'cabania_capacidad' => $this->post('cabania_capacidad'),
            'cabania_precio' => $this->post('cabania_precio'),
            'cabania_ubicacion' => $this->post('cabania_ubicacion'),
            'cabania_cantidadbanios' => $this->post('cabania_cantidadbanios'),
            'cabania_cantidadhabitaciones' => $this->post('cabania_cantidadhabitaciones'),
            'cabania_foto' => $cabania_foto,
            'cabania_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['cabania_codigo']) || empty($data['cabania_nombre'])) {
            $this->redirect('/admin/cabanias/create', 'Complete los campos obligatorios', 'error');
        }

        try {
            $id = $this->cabaniaModel->create($data);
            if ($id) {
                $this->redirect('/cabanias', 'Cabaña creada correctamente', 'exito');
            } else {
                $this->redirect('/cabanias/create', 'Error al crear la cabaña', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/cabanias/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar cabaña específica
     */
    public function show($id)
    {
        $this->requirePermission('cabanias');

        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de la cabaña
        $estadisticas = $this->cabaniaModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Cabaña',
            'cabania' => $cabania,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cabanias/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('cabanias');

        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $data = [
            'title' => 'Editar Cabaña',
            'cabania' => $cabania,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cabanias/formulario', $data, 'main');
    }

    /**
     * Actualizar cabaña
     */
    public function update($id)
    {
        $this->requirePermission('cabanias');

        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            return $this->view->error(404);
        }

        // Manejar subida de foto
        $cabania_foto = $cabania['cabania_foto']; // Mantener foto actual por defecto
        if (isset($_FILES['cabania_foto']) && $_FILES['cabania_foto']['error'] == 0) {
            $target_dir = "imagenes/cabanias/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["cabania_foto"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["cabania_foto"]["tmp_name"], $target_file)) {
                // Eliminar foto anterior si existe
                if ($cabania['cabania_foto'] && file_exists($target_dir . $cabania['cabania_foto'])) {
                    unlink($target_dir . $cabania['cabania_foto']);
                }
                $cabania_foto = $new_filename;
            }
        }

        $data = [
            'cabania_codigo' => $this->post('cabania_codigo'),
            'cabania_nombre' => $this->post('cabania_nombre'),
            'cabania_descripcion' => $this->post('cabania_descripcion'),
            'cabania_capacidad' => $this->post('cabania_capacidad'),
            'cabania_precio' => $this->post('cabania_precio'),
            'cabania_ubicacion' => $this->post('cabania_ubicacion'),
            'cabania_cantidadbanios' => $this->post('cabania_cantidadbanios'),
            'cabania_cantidadhabitaciones' => $this->post('cabania_cantidadhabitaciones'),
            'cabania_foto' => $cabania_foto
        ];

        if (empty($data['cabania_codigo']) || empty($data['cabania_nombre'])) {
            $this->redirect("/cabanias/$id/edit", 'Complete los campos obligatorios', 'error');
        }

        try {
            if ($this->cabaniaModel->update($id, $data)) {
                $this->redirect('/cabanias', 'Cabaña actualizada correctamente', 'exito');
            } else {
                $this->redirect("/cabanias/$id/edit", 'Error al actualizar la cabaña', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/cabanias/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de cabaña
     */
    public function delete($id)
    {
        $this->requirePermission('cabanias');

        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            return $this->view->error(404);
        }

        if ($this->cabaniaModel->softDelete($id, 'cabania_estado')) {
            $this->redirect('/cabanias', 'Cabaña eliminada correctamente', 'exito');
        } else {
            $this->redirect('/cabanias', 'Error al eliminar la cabaña', 'error');
        }
    }

    /**
     * Restaurar cabaña
     */
    public function restore($id)
    {
        $this->requirePermission('cabanias');

        if ($this->cabaniaModel->restore($id, 'cabania_estado')) {
            $this->redirect('/cabanias', 'Cabaña restaurada correctamente', 'exito');
        } else {
            $this->redirect('/cabanias', 'Error al restaurar la cabaña', 'error');
        }
    }

    /**
     * Verificar disponibilidad (AJAX)
     */
    public function checkAvailability()
    {
        if (!$this->isAjax()) {
            return $this->view->error(404);
        }

        $cabaniaId = $this->post('cabania_id');
        $fechaInicio = $this->post('fecha_inicio');
        $fechaFin = $this->post('fecha_fin');

        if (!$cabaniaId || !$fechaInicio || !$fechaFin) {
            return $this->json(['error' => 'Faltan parámetros'], 400);
        }

        $available = $this->cabaniaModel->checkAvailability($cabaniaId, $fechaInicio, $fechaFin);

        return $this->json([
            'available' => $available,
            'message' => $available ? 'Cabaña disponible' : 'Cabaña no disponible para las fechas seleccionadas'
        ]);
    }

    /**
     * Cambiar estado de cabaña (AJAX)
     */
    public function cambiarEstado($id)
    {
        // Log para debugging
        error_log("Petición recibida en cambiarEstado - ID: $id");
        error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("URL completa: " . $_SERVER['REQUEST_URI']);
        
        $this->requirePermission('cabanias');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            error_log("Error: No es una petición AJAX");
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que la cabaña existe
        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            error_log("Error: Cabaña no encontrada - ID: $id");
            return $this->json(['success' => false, 'message' => 'Cabaña no encontrada'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("Datos recibidos: " . json_encode($input));
        
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1, 2])) {
            error_log("Error: Estado inválido - Estado: " . var_export($nuevoEstado, true));
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactiva), 1 (activa), 2 (ocupada)'], 400);
        }

        // Actualizar el estado
        $data = ['cabania_estado' => $nuevoEstado];
        $resultado = $this->cabaniaModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactiva', 'activa', 'ocupada'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizada';
            error_log("Estado cambiado exitosamente - ID: $id, Nuevo estado: $nuevoEstado");
            return $this->json([
                'success' => true, 
                'message' => "Cabaña marcada como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            error_log("Error al actualizar el estado en la base de datos - ID: $id");
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado de la cabaña'
            ], 500);
        }
    }

    /**
     * Exportar cabañas a Excel
     */
    public function exportar()
    {
        $this->requirePermission('cabanias');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'cabania_codigo' => $this->get('cabania_codigo'),
                'cabania_nombre' => $this->get('cabania_nombre'),
                'cabania_ubicacion' => $this->get('cabania_ubicacion'),
                'cabania_capacidad' => $this->get('cabania_capacidad'),
                'cabania_habitaciones' => $this->get('cabania_habitaciones'),
                'cabania_banios' => $this->get('cabania_banios'),
                'cabania_estado' => $this->get('cabania_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->cabaniaModel->getAllWithDetailsForExport($filters);
            $cabanias = $result['data'];

            if (empty($cabanias)) {
                $this->redirect('/cabanias', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Cabañas');

            // Definir encabezados
            $headers = [
                'A1' => 'Código',
                'B1' => 'Nombre',
                'C1' => 'Descripción',
                'D1' => 'Capacidad',
                'E1' => 'Habitaciones',
                'F1' => 'Baños',
                'G1' => 'Precio',
                'H1' => 'Ubicación',
                'I1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:I1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:I1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($cabanias as $cabania) {
                // Mapear estado a texto
                $estadoTexto = '';
                switch ($cabania['cabania_estado']) {
                    case 0:
                        $estadoTexto = 'Inactiva';
                        break;
                    case 1:
                        $estadoTexto = 'Activa';
                        break;
                    case 2:
                        $estadoTexto = 'Ocupada';
                        break;
                    default:
                        $estadoTexto = 'Desconocido';
                }

                $worksheet->setCellValue('A' . $row, $cabania['cabania_codigo']);
                $worksheet->setCellValue('B' . $row, $cabania['cabania_nombre']);
                $worksheet->setCellValue('C' . $row, $cabania['cabania_descripcion']);
                $worksheet->setCellValue('D' . $row, $cabania['cabania_capacidad']);
                $worksheet->setCellValue('E' . $row, $cabania['cabania_cantidadhabitaciones']);
                $worksheet->setCellValue('F' . $row, $cabania['cabania_cantidadbanios']);
                $worksheet->setCellValue('G' . $row, number_format($cabania['cabania_precio'], 2));
                $worksheet->setCellValue('H' . $row, $cabania['cabania_ubicacion'] ?? '');
                $worksheet->setCellValue('I' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(12);
            $worksheet->getColumnDimension('B')->setWidth(25);
            $worksheet->getColumnDimension('C')->setWidth(40);
            $worksheet->getColumnDimension('D')->setWidth(12);
            $worksheet->getColumnDimension('E')->setWidth(12);
            $worksheet->getColumnDimension('F')->setWidth(10);
            $worksheet->getColumnDimension('G')->setWidth(15);
            $worksheet->getColumnDimension('H')->setWidth(25);
            $worksheet->getColumnDimension('I')->setWidth(12);

            // Aplicar formato a la columna de precio
            $worksheet->getStyle('G2:G' . ($row - 1))->getNumberFormat()->setFormatCode('$#,##0.00');

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "cabanias_{$fecha}.xlsx";

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
            error_log("Error al exportar cabañas: " . $e->getMessage());
            $this->redirect('/cabanias', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar cabañas a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('cabanias');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'cabania_codigo' => $this->get('cabania_codigo'),
                'cabania_nombre' => $this->get('cabania_nombre'),
                'cabania_ubicacion' => $this->get('cabania_ubicacion'),
                'cabania_capacidad' => $this->get('cabania_capacidad'),
                'cabania_habitaciones' => $this->get('cabania_habitaciones'),
                'cabania_banios' => $this->get('cabania_banios'),
                'cabania_estado' => $this->get('cabania_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->cabaniaModel->getAllWithDetailsForExport($filters);
            $cabanias = $result['data'];

            if (empty($cabanias)) {
                $this->redirect('/cabanias', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Cabañas');
            $pdf->SetSubject('Exportación de Cabañas');
            $pdf->SetKeywords('cabañas, listado, exportación');

            // Configurar márgenes mínimos para maximizar espacio de la tabla
            $pdf->SetMargins(8, 15, 8);
            $pdf->SetHeaderMargin(3);
            $pdf->SetFooterMargin(8);

            // Configurar auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 25);

            // Configurar escala de imagen
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Establecer fuente
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 9);

            // Agregar página
            $pdf->AddPage();

            // Título del documento
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, 'Listado de Cabañas', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['cabania_nombre'])) {
                $filtrosTexto[] = 'Nombre: ' . $filters['cabania_nombre'];
            }
            if (!empty($filters['cabania_capacidad'])) {
                $filtrosTexto[] = 'Capacidad mín.: ' . $filters['cabania_capacidad'];
            }
            if (!empty($filters['cabania_habitaciones'])) {
                $filtrosTexto[] = 'Habitaciones mín.: ' . $filters['cabania_habitaciones'];
            }
            if (!empty($filters['cabania_banios'])) {
                $filtrosTexto[] = 'Baños mín.: ' . $filters['cabania_banios'];
            }
            if (isset($filters['cabania_estado']) && $filters['cabania_estado'] !== '') {
                $estadosTexto = ['Inactiva', 'Activa', 'Ocupada'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['cabania_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Configuración fija: nunca mostrar descripción ni ubicación
            // Formato optimizado para A4 vertical
            
            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($cabanias) . ' | Formato: A4 Vertical';
            $pdf->Cell(0, 10, $infoFormato, 0, 1, 'L');
            $pdf->Ln(5);
            
            // Crear tabla HTML optimizada para A4 vertical
            $html = '<style>
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                    table-layout: fixed;
                }
                th { 
                    background-color: #E3F2FD; 
                    border: 1px solid #333; 
                    padding: 3px; 
                    text-align: center; 
                    font-weight: bold; 
                    font-size: 8px;
                    word-wrap: break-word;
                }
                td { 
                    border: 1px solid #666; 
                    padding: 2px; 
                    font-size: 7px; 
                    vertical-align: top;
                    word-wrap: break-word;
                    overflow: hidden;
                }
                .codigo { text-align: center; width: 10%; }
                .nombre { width: 20%; }
                .numero { text-align: center; width: 15%; }
                .precio { text-align: right; width: 10%; }
                .estado { text-align: center; width: 10%; }
                .estado-activa { color: #28a745; font-weight: bold; }
                .estado-ocupada { color: #ffc107; font-weight: bold; }
                .estado-inactiva { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="codigo">Código</th>
                        <th class="nombre">Nombre</th>
                        <th class="numero">Cap.</th>
                        <th class="numero">Hab.</th>
                        <th class="numero">Baños</th>
                        <th class="precio">Precio</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($cabanias as $cabania) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = '';
                $estadoClase = '';
                switch ($cabania['cabania_estado']) {
                    case 0:
                        $estadoTexto = 'Inactiva';
                        $estadoClase = 'estado-inactiva';
                        break;
                    case 1:
                        $estadoTexto = 'Activa';
                        $estadoClase = 'estado-activa';
                        break;
                    case 2:
                        $estadoTexto = 'Ocupada';
                        $estadoClase = 'estado-ocupada';
                        break;
                    default:
                        $estadoTexto = 'Desconocido';
                        $estadoClase = '';
                }

                $html .= '<tr>
                    <td class="codigo">' . htmlspecialchars($cabania['cabania_codigo']) . '</td>
                    <td class="nombre">' . htmlspecialchars($cabania['cabania_nombre']) . '</td>
                    <td class="numero">' . $cabania['cabania_capacidad'] . '</td>
                    <td class="numero">' . $cabania['cabania_cantidadhabitaciones'] . '</td>
                    <td class="numero">' . $cabania['cabania_cantidadbanios'] . '</td>
                    <td class="precio">$' . number_format($cabania['cabania_precio'], 0, '.', ',') . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "cabanias_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar cabañas a PDF: " . $e->getMessage());
            $this->redirect('/cabanias', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

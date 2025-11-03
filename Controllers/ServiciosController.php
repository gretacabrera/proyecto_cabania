<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Servicio;

/**
 * Controlador para el manejo de servicios
 */
class ServiciosController extends Controller
{
    protected $servicioModel;

    public function __construct()
    {
        parent::__construct();
        $this->servicioModel = new Servicio();
    }

    /**
     * Listar servicios
     */
    public function index()
    {
        $this->requirePermission('servicios');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'servicio_nombre' => $this->get('servicio_nombre'),
            'servicio_descripcion' => $this->get('servicio_descripcion'),
            'precio_min' => $this->get('precio_min'),
            'precio_max' => $this->get('precio_max'),
            'rela_tiposervicio' => $this->get('rela_tiposervicio'),
            'servicio_estado' => $this->get('servicio_estado')
        ];

        $result = $this->servicioModel->getWithDetails($page, $perPage, $filters);

        // Obtener tipos de servicio para el filtro
        $tiposServicio = $this->servicioModel->getTiposServicio();

        $data = [
            'title' => 'Gestión de Servicios',
            'servicios' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'tiposServicio' => $tiposServicio,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/servicios/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo servicio
     */
    public function create()
    {
        $this->requirePermission('servicios');

        if ($this->isPost()) {
            return $this->store();
        }

        // Obtener tipos de servicio para el formulario
        $tiposServicio = $this->servicioModel->getTiposServicio();

        $data = [
            'title' => 'Nuevo Servicio',
            'tiposServicio' => $tiposServicio,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/servicios/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo servicio
     */
    public function store()
    {
        $this->requirePermission('servicios');

        $data = [
            'servicio_nombre' => $this->post('servicio_nombre'),
            'servicio_descripcion' => $this->post('servicio_descripcion'),
            'servicio_precio' => (float) $this->post('servicio_precio'),
            'rela_tiposervicio' => (int) $this->post('rela_tiposervicio'),
            'servicio_estado' => 1
        ];

        // Usar validaciones del modelo
        $validation = $this->servicioModel->validate($data);
        if ($validation !== true) {
            $this->redirect('/servicios/create', $validation, 'error');
            return;
        }

        try {
            $id = $this->servicioModel->create($data);
            if ($id) {
                $this->redirect('/servicios', 'Servicio creado correctamente', 'exito');
            } else {
                $this->redirect('/servicios/create', 'Error al crear el servicio', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/servicios/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar servicio específico
     */
    public function show($id)
    {
        $this->requirePermission('servicios');

        $servicio = $this->servicioModel->getServiceWithDetails($id);
        if (!$servicio) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del servicio
        $estadisticas = $this->servicioModel->getServiceStats($id);

        $data = [
            'title' => 'Detalle de Servicio',
            'servicio' => $servicio,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/servicios/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('servicios');

        $servicio = $this->servicioModel->find($id);
        if (!$servicio) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener tipos de servicio para el formulario
        $tiposServicio = $this->servicioModel->getTiposServicio();

        $data = [
            'title' => 'Editar Servicio',
            'servicio' => $servicio,
            'tiposServicio' => $tiposServicio,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/servicios/formulario', $data, 'main');
    }

    /**
     * Actualizar servicio
     */
    public function update($id)
    {
        $this->requirePermission('servicios');

        $servicio = $this->servicioModel->find($id);
        if (!$servicio) {
            return $this->view->error(404);
        }

        $data = [
            'servicio_nombre' => $this->post('servicio_nombre'),
            'servicio_descripcion' => $this->post('servicio_descripcion'),
            'servicio_precio' => (float) $this->post('servicio_precio'),
            'rela_tiposervicio' => (int) $this->post('rela_tiposervicio')
        ];

        // Usar validaciones del modelo
        $validation = $this->servicioModel->validate($data, $id);
        if ($validation !== true) {
            $this->redirect("/servicios/$id/edit", $validation, 'error');
            return;
        }

        try {
            if ($this->servicioModel->update($id, $data)) {
                $this->redirect('/servicios', 'Servicio actualizado correctamente', 'exito');
            } else {
                $this->redirect("/servicios/$id/edit", 'Error al actualizar el servicio', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/servicios/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de servicio
     */
    public function delete($id)
    {
        $this->requirePermission('servicios');

        $servicio = $this->servicioModel->find($id);
        if (!$servicio) {
            return $this->view->error(404);
        }

        if ($this->servicioModel->softDelete($id, 'servicio_estado')) {
            $this->redirect('/servicios', 'Servicio eliminado correctamente', 'exito');
        } else {
            $this->redirect('/servicios', 'Error al eliminar el servicio', 'error');
        }
    }

    /**
     * Restaurar servicio
     */
    public function restore($id)
    {
        $this->requirePermission('servicios');

        if ($this->servicioModel->restore($id, 'servicio_estado')) {
            $this->redirect('/servicios', 'Servicio restaurado correctamente', 'exito');
        } else {
            $this->redirect('/servicios', 'Error al restaurar el servicio', 'error');
        }
    }

    /**
     * Cambiar estado de servicio (AJAX)
     */
    public function cambiarEstado($id)
    {
        error_log("Petición recibida en cambiarEstado servicio - ID: $id");
        error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("URL completa: " . $_SERVER['REQUEST_URI']);
        
        $this->requirePermission('servicios');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            error_log("Error: No es una petición AJAX");
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el servicio existe
        $servicio = $this->servicioModel->find($id);
        if (!$servicio) {
            error_log("Error: Servicio no encontrado - ID: $id");
            return $this->json(['success' => false, 'message' => 'Servicio no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("Datos recibidos: " . json_encode($input));
        
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            error_log("Error: Estado inválido - Estado: " . var_export($nuevoEstado, true));
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['servicio_estado' => $nuevoEstado];
        $resultado = $this->servicioModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactivo', 'activo'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizado';
            error_log("Estado cambiado exitosamente - ID: $id, Nuevo estado: $nuevoEstado");
            return $this->json([
                'success' => true, 
                'message' => "Servicio marcado como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            error_log("Error al actualizar el estado en la base de datos - ID: $id");
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del servicio'
            ], 500);
        }
    }

    /**
     * Exportar servicios a Excel
     */
    public function exportar()
    {
        $this->requirePermission('servicios');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'servicio_nombre' => $this->get('servicio_nombre'),
                'servicio_descripcion' => $this->get('servicio_descripcion'),
                'precio_min' => $this->get('precio_min'),
                'precio_max' => $this->get('precio_max'),
                'rela_tiposervicio' => $this->get('rela_tiposervicio'),
                'servicio_estado' => $this->get('servicio_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->servicioModel->getAllWithDetailsForExport($filters);
            $servicios = $result['data'];

            if (empty($servicios)) {
                $this->redirect('/servicios', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Servicios');

            // Definir encabezados
            $headers = [
                'A1' => 'Nombre',
                'B1' => 'Descripción',
                'C1' => 'Precio',
                'D1' => 'Tipo de Servicio',
                'E1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:E1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:E1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($servicios as $servicio) {
                // Mapear estado a texto
                $estadoTexto = $servicio['servicio_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $servicio['servicio_nombre']);
                $worksheet->setCellValue('B' . $row, $servicio['servicio_descripcion']);
                $worksheet->setCellValue('C' . $row, number_format($servicio['servicio_precio'], 2));
                $worksheet->setCellValue('D' . $row, $servicio['tiposervicio_descripcion'] ?? '');
                $worksheet->setCellValue('E' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(25);
            $worksheet->getColumnDimension('B')->setWidth(40);
            $worksheet->getColumnDimension('C')->setWidth(15);
            $worksheet->getColumnDimension('D')->setWidth(25);
            $worksheet->getColumnDimension('E')->setWidth(12);

            // Aplicar formato a la columna de precio
            $worksheet->getStyle('C2:C' . ($row - 1))->getNumberFormat()->setFormatCode('$#,##0.00');

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "servicios_{$fecha}.xlsx";

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
            error_log("Error al exportar servicios: " . $e->getMessage());
            $this->redirect('/servicios', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar servicios a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('servicios');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'servicio_nombre' => $this->get('servicio_nombre'),
                'servicio_descripcion' => $this->get('servicio_descripcion'),
                'precio_min' => $this->get('precio_min'),
                'precio_max' => $this->get('precio_max'),
                'rela_tiposervicio' => $this->get('rela_tiposervicio'),
                'servicio_estado' => $this->get('servicio_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->servicioModel->getAllWithDetailsForExport($filters);
            $servicios = $result['data'];

            if (empty($servicios)) {
                $this->redirect('/servicios', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Servicios');
            $pdf->SetSubject('Exportación de Servicios');
            $pdf->SetKeywords('servicios, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Servicios', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['servicio_nombre'])) {
                $filtrosTexto[] = 'Nombre: ' . $filters['servicio_nombre'];
            }
            if (!empty($filters['precio_min'])) {
                $filtrosTexto[] = 'Precio mín.: $' . number_format($filters['precio_min'], 2);
            }
            if (!empty($filters['precio_max'])) {
                $filtrosTexto[] = 'Precio máx.: $' . number_format($filters['precio_max'], 2);
            }
            if (isset($filters['servicio_estado']) && $filters['servicio_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['servicio_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($servicios) . ' | Formato: A4 Vertical';
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
                .nombre { width: 15%; }
                .descripcion { width: 35%; }
                .precio { text-align: right; width: 15%; }
                .tipo { width: 20%; }
                .estado { text-align: center; width: 10%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="nombre">Nombre</th>
                        <th class="descripcion">Descripción</th>
                        <th class="precio">Precio</th>
                        <th class="tipo">Tipo</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($servicios as $servicio) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = $servicio['servicio_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $servicio['servicio_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="nombre">' . htmlspecialchars($servicio['servicio_nombre']) . '</td>
                    <td class="descripcion">' . htmlspecialchars(substr($servicio['servicio_descripcion'], 0, 80)) . (strlen($servicio['servicio_descripcion']) > 80 ? '...' : '') . '</td>
                    <td class="precio">$' . number_format($servicio['servicio_precio'], 0, '.', ',') . '</td>
                    <td class="tipo">' . htmlspecialchars($servicio['tiposervicio_descripcion'] ?? '') . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "servicios_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar servicios a PDF: " . $e->getMessage());
            $this->redirect('/servicios', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

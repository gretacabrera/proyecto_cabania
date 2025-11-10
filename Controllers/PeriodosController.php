<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Periodo;

/**
 * Controlador para el manejo de periodos
 */
class PeriodosController extends Controller
{
    protected $periodoModel;

    public function __construct()
    {
        parent::__construct();
        $this->periodoModel = new Periodo();
    }

    /**
     * Listar periodos
     */
    public function index()
    {
        $this->requirePermission('periodos');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'periodo_descripcion' => $this->get('periodo_descripcion'),
            'periodo_anio' => $this->get('periodo_anio'),
            'periodo_estado' => $this->get('periodo_estado')
        ];

        $result = $this->periodoModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Periodos',
            'periodos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/periodos/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo periodo
     */
    public function create()
    {
        $this->requirePermission('periodos');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Periodo',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/periodos/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo periodo
     */
    public function store()
    {
        $this->requirePermission('periodos');

        $data = [
            'periodo_descripcion' => $this->post('periodo_descripcion'),
            'periodo_fechainicio' => $this->post('periodo_fechainicio'),
            'periodo_fechafin' => $this->post('periodo_fechafin'),
            'periodo_anio' => $this->post('periodo_anio'),
            'periodo_orden' => $this->post('periodo_orden'),
            'periodo_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['periodo_descripcion']) || empty($data['periodo_fechainicio']) || empty($data['periodo_fechafin'])) {
            $this->redirect('/periodos/create', 'Complete los campos obligatorios', 'error');
        }

        // Validar que la fecha de inicio sea menor que la fecha de fin
        if ($data['periodo_fechainicio'] >= $data['periodo_fechafin']) {
            $this->redirect('/periodos/create', 'La fecha de inicio debe ser anterior a la fecha de fin', 'error');
        }

        try {
            $id = $this->periodoModel->create($data);
            if ($id) {
                $this->redirect('/periodos', 'Periodo creado correctamente', 'exito');
            } else {
                $this->redirect('/periodos/create', 'Error al crear el periodo', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/periodos/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar periodo específico
     */
    public function show($id)
    {
        $this->requirePermission('periodos');

        $periodo = $this->periodoModel->find($id);
        if (!$periodo) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del periodo
        $estadisticas = $this->periodoModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Periodo',
            'periodo' => $periodo,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/periodos/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('periodos');

        $periodo = $this->periodoModel->find($id);
        if (!$periodo) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas del periodo
        $estadisticas = $this->periodoModel->getStatistics($id);

        $data = [
            'title' => 'Editar Periodo',
            'periodo' => $periodo,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/periodos/formulario', $data, 'main');
    }

    /**
     * Actualizar periodo
     */
    public function update($id)
    {
        $this->requirePermission('periodos');

        $periodo = $this->periodoModel->find($id);
        if (!$periodo) {
            return $this->view->error(404);
        }

        $data = [
            'periodo_descripcion' => $this->post('periodo_descripcion'),
            'periodo_fechainicio' => $this->post('periodo_fechainicio'),
            'periodo_fechafin' => $this->post('periodo_fechafin'),
            'periodo_anio' => $this->post('periodo_anio'),
            'periodo_orden' => $this->post('periodo_orden')
        ];

        if (empty($data['periodo_descripcion']) || empty($data['periodo_fechainicio']) || empty($data['periodo_fechafin'])) {
            $this->redirect("/periodos/$id/edit", 'Complete los campos obligatorios', 'error');
        }

        // Validar que la fecha de inicio sea menor que la fecha de fin
        if ($data['periodo_fechainicio'] >= $data['periodo_fechafin']) {
            $this->redirect("/periodos/$id/edit", 'La fecha de inicio debe ser anterior a la fecha de fin', 'error');
        }

        try {
            if ($this->periodoModel->update($id, $data)) {
                $this->redirect('/periodos', 'Periodo actualizado correctamente', 'exito');
            } else {
                $this->redirect("/periodos/$id/edit", 'Error al actualizar el periodo', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/periodos/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de periodo
     */
    public function delete($id)
    {
        $this->requirePermission('periodos');

        $periodo = $this->periodoModel->find($id);
        if (!$periodo) {
            return $this->view->error(404);
        }

        if ($this->periodoModel->softDelete($id, 'periodo_estado')) {
            $this->redirect('/periodos', 'Periodo eliminado correctamente', 'exito');
        } else {
            $this->redirect('/periodos', 'Error al eliminar el periodo', 'error');
        }
    }

    /**
     * Restaurar periodo
     */
    public function restore($id)
    {
        $this->requirePermission('periodos');

        if ($this->periodoModel->restore($id, 'periodo_estado')) {
            $this->redirect('/periodos', 'Periodo restaurado correctamente', 'exito');
        } else {
            $this->redirect('/periodos', 'Error al restaurar el periodo', 'error');
        }
    }

    /**
     * Cambiar estado de periodo (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('periodos');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el periodo existe
        $periodo = $this->periodoModel->find($id);
        if (!$periodo) {
            return $this->json(['success' => false, 'message' => 'Periodo no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['periodo_estado' => $nuevoEstado];
        $resultado = $this->periodoModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = $nuevoEstado == 1 ? 'activo' : 'inactivo';
            return $this->json([
                'success' => true, 
                'message' => "Periodo marcado como {$estadoTexto} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del periodo'
            ], 500);
        }
    }

    /**
     * Exportar periodos a Excel
     */
    public function exportar()
    {
        $this->requirePermission('periodos');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'periodo_descripcion' => $this->get('periodo_descripcion'),
                'periodo_anio' => $this->get('periodo_anio'),
                'periodo_estado' => $this->get('periodo_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->periodoModel->getAllWithDetailsForExport($filters);
            $periodos = $result['data'];

            if (empty($periodos)) {
                $this->redirect('/periodos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Periodos');

            // Definir encabezados
            $headers = [
                'A1' => 'Descripción',
                'B1' => 'Fecha Inicio',
                'C1' => 'Fecha Fin',
                'D1' => 'Año',
                'E1' => 'Orden',
                'F1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:F1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($periodos as $periodo) {
                // Mapear estado a texto
                $estadoTexto = $periodo['periodo_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $periodo['periodo_descripcion']);
                $worksheet->setCellValue('B' . $row, date('d/m/Y', strtotime($periodo['periodo_fechainicio'])));
                $worksheet->setCellValue('C' . $row, date('d/m/Y', strtotime($periodo['periodo_fechafin'])));
                $worksheet->setCellValue('D' . $row, $periodo['periodo_anio']);
                $worksheet->setCellValue('E' . $row, $periodo['periodo_orden']);
                $worksheet->setCellValue('F' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(30);
            $worksheet->getColumnDimension('B')->setWidth(15);
            $worksheet->getColumnDimension('C')->setWidth(15);
            $worksheet->getColumnDimension('D')->setWidth(10);
            $worksheet->getColumnDimension('E')->setWidth(10);
            $worksheet->getColumnDimension('F')->setWidth(12);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "periodos_{$fecha}.xlsx";

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
            error_log("Error al exportar periodos: " . $e->getMessage());
            $this->redirect('/periodos', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar periodos a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('periodos');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'periodo_descripcion' => $this->get('periodo_descripcion'),
                'periodo_anio' => $this->get('periodo_anio'),
                'periodo_estado' => $this->get('periodo_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->periodoModel->getAllWithDetailsForExport($filters);
            $periodos = $result['data'];

            if (empty($periodos)) {
                $this->redirect('/periodos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Periodos');
            $pdf->SetSubject('Exportación de Periodos');
            $pdf->SetKeywords('periodos, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Periodos', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['periodo_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['periodo_descripcion'];
            }
            if (!empty($filters['periodo_anio'])) {
                $filtrosTexto[] = 'Año: ' . $filters['periodo_anio'];
            }
            if (isset($filters['periodo_estado']) && $filters['periodo_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['periodo_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($periodos) . ' | Formato: A4 Vertical';
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
                .descripcion { width: 30%; }
                .fecha { text-align: center; width: 15%; }
                .numero { text-align: center; width: 10%; }
                .estado { text-align: center; width: 10%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="descripcion">Descripción</th>
                        <th class="fecha">Fecha Inicio</th>
                        <th class="fecha">Fecha Fin</th>
                        <th class="numero">Año</th>
                        <th class="numero">Orden</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($periodos as $periodo) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = $periodo['periodo_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $periodo['periodo_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($periodo['periodo_descripcion']) . '</td>
                    <td class="fecha">' . date('d/m/Y', strtotime($periodo['periodo_fechainicio'])) . '</td>
                    <td class="fecha">' . date('d/m/Y', strtotime($periodo['periodo_fechafin'])) . '</td>
                    <td class="numero">' . $periodo['periodo_anio'] . '</td>
                    <td class="numero">' . $periodo['periodo_orden'] . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "periodos_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar periodos a PDF: " . $e->getMessage());
            $this->redirect('/periodos', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

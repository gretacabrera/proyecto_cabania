<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ubicacion;

/**
 * Controlador para el manejo de ubicaciones
 */
class UbicacionesController extends Controller
{
    protected $modelo;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new Ubicacion();
    }

    /**
     * Listar ubicaciones
     */
    public function index()
    {
        $this->requirePermission('ubicaciones');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'ubicacion_descripcion' => $this->get('ubicacion_descripcion'),
            'ubicacion_estado' => $this->get('ubicacion_estado')
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Ubicaciones',
            'ubicaciones' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/ubicaciones/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva ubicación
     */
    public function create()
    {
        $this->requirePermission('ubicaciones');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nueva Ubicación',
            'ubicacion' => [],
            'isEdit' => false,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/ubicaciones/formulario', $data, 'main');
    }

    /**
     * Guardar nueva ubicación
     */
    public function store()
    {
        $this->requirePermission('ubicaciones');

        $data = [
            'ubicacion_descripcion' => $this->post('ubicacion_descripcion'),
            'ubicacion_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['ubicacion_descripcion'])) {
            $this->redirect('/ubicaciones/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            $id = $this->modelo->create($data);
            if (!$id) {
                throw new \Exception('Error al crear la ubicación');
            }

            $this->redirect('/ubicaciones', 'Ubicación creada correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect('/ubicaciones/create', 'Error al crear la ubicación: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar ubicación específica
     */
    public function show($id)
    {
        $this->requirePermission('ubicaciones');

        $ubicacion = $this->modelo->find($id);
        if (!$ubicacion) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de la ubicación
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Detalle de Ubicación',
            'ubicacion' => $ubicacion,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/ubicaciones/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('ubicaciones');

        $ubicacion = $this->modelo->find($id);
        if (!$ubicacion) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas de la ubicación
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Editar Ubicación',
            'ubicacion' => $ubicacion,
            'estadisticas' => $estadisticas,
            'isEdit' => true,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/ubicaciones/formulario', $data, 'main');
    }

    /**
     * Actualizar ubicación
     */
    public function update($id)
    {
        $this->requirePermission('ubicaciones');

        $ubicacion = $this->modelo->find($id);
        if (!$ubicacion) {
            return $this->view->error(404);
        }

        $data = [
            'ubicacion_descripcion' => $this->post('ubicacion_descripcion')
        ];

        if (empty($data['ubicacion_descripcion'])) {
            $this->redirect("/ubicaciones/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            if (!$this->modelo->update($id, $data)) {
                throw new \Exception('Error al actualizar la ubicación');
            }

            $this->redirect('/ubicaciones', 'Ubicación actualizada correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect("/ubicaciones/$id/edit", 'Error al actualizar la ubicación: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de ubicación
     */
    public function delete($id)
    {
        $this->requirePermission('ubicaciones');

        $ubicacion = $this->modelo->find($id);
        if (!$ubicacion) {
            return $this->view->error(404);
        }

        if ($this->modelo->softDelete($id, 'ubicacion_estado')) {
            $this->redirect('/ubicaciones', 'Ubicación eliminada correctamente', 'exito');
        } else {
            $this->redirect('/ubicaciones', 'Error al eliminar la ubicación', 'error');
        }
    }

    /**
     * Restaurar ubicación
     */
    public function restore($id)
    {
        $this->requirePermission('ubicaciones');

        if ($this->modelo->restore($id, 'ubicacion_estado')) {
            $this->redirect('/ubicaciones', 'Ubicación restaurada correctamente', 'exito');
        } else {
            $this->redirect('/ubicaciones', 'Error al restaurar la ubicación', 'error');
        }
    }

    /**
     * Cambiar estado de ubicación (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('ubicaciones');

        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        $ubicacion = $this->modelo->find($id);
        if (!$ubicacion) {
            return $this->json(['success' => false, 'message' => 'Ubicación no encontrada'], 404);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido'], 400);
        }

        $data = ['ubicacion_estado' => $nuevoEstado];
        $resultado = $this->modelo->update($id, $data);

        if ($resultado) {
            $accion = $nuevoEstado == 1 ? 'activada' : 'inactivada';
            return $this->json([
                'success' => true, 
                'message' => "Ubicación {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado de la ubicación'
            ], 500);
        }
    }

    /**
     * Exportar ubicaciones a Excel
     */
    public function exportar()
    {
        $this->requirePermission('ubicaciones');

        try {
            $filters = [
                'ubicacion_descripcion' => $this->get('ubicacion_descripcion'),
                'ubicacion_estado' => $this->get('ubicacion_estado')
            ];

            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $ubicaciones = $result['data'];

            if (empty($ubicaciones)) {
                $this->redirect('/ubicaciones', 'No hay datos para exportar', 'error');
                return;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Ubicaciones');

            $headers = [
                'A1' => 'Descripción',
                'B1' => 'Estado'
            ];

            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            $worksheet->getStyle('A1:B1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:B1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:B1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            $row = 2;
            foreach ($ubicaciones as $ubicacion) {
                $estadoTexto = $ubicacion['ubicacion_estado'] == 1 ? 'Activo' : 'Inactivo';
                $worksheet->setCellValue('A' . $row, $ubicacion['ubicacion_descripcion']);
                $worksheet->setCellValue('B' . $row, $estadoTexto);
                $row++;
            }

            $worksheet->getColumnDimension('A')->setWidth(50);
            $worksheet->getColumnDimension('B')->setWidth(15);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $fecha = date('Y-m-d');
            $nombreArchivo = "ubicaciones_{$fecha}.xlsx";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar ubicaciones: " . $e->getMessage());
            $this->redirect('/ubicaciones', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar ubicaciones a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('ubicaciones');

        try {
            $filters = [
                'ubicacion_descripcion' => $this->get('ubicacion_descripcion'),
                'ubicacion_estado' => $this->get('ubicacion_estado')
            ];

            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $ubicaciones = $result['data'];

            if (empty($ubicaciones)) {
                $this->redirect('/ubicaciones', 'No hay datos para exportar', 'error');
                return;
            }

            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Ubicaciones');
            $pdf->SetSubject('Exportación de Ubicaciones');
            $pdf->SetKeywords('ubicaciones, listado, exportación');

            $pdf->SetMargins(15, 15, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, 'Listado de Ubicaciones', 0, 1, 'C');
            $pdf->Ln(5);

            $filtrosTexto = [];
            if (!empty($filters['ubicacion_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['ubicacion_descripcion'];
            }
            if (isset($filters['ubicacion_estado']) && $filters['ubicacion_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['ubicacion_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            $pdf->SetFont('helvetica', '', 9);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($ubicaciones);
            $pdf->Cell(0, 10, $infoFormato, 0, 1, 'L');
            $pdf->Ln(5);
            
            $html = '<style>
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                }
                th { 
                    background-color: #E3F2FD; 
                    border: 1px solid #333; 
                    padding: 8px; 
                    text-align: left; 
                    font-weight: bold; 
                }
                td { 
                    border: 1px solid #666; 
                    padding: 6px; 
                }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
                .numero { text-align: center; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th style="width: 70%;">Descripción</th>
                        <th style="width: 30%;" class="numero">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($ubicaciones as $ubicacion) {
                $estadoTexto = $ubicacion['ubicacion_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $ubicacion['ubicacion_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td style="width: 70%;">' . htmlspecialchars($ubicacion['ubicacion_descripcion']) . '</td>
                    <td style="width: 30%;" class="numero ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $fecha = date('Y-m-d');
            $nombreArchivo = "ubicaciones_{$fecha}.pdf";

            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar ubicaciones a PDF: " . $e->getMessage());
            $this->redirect('/ubicaciones', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

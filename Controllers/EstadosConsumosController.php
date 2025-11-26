<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EstadoConsumo;

/**
 * Controlador para el manejo de estados de consumo
 */
class EstadosConsumosController extends Controller
{
    protected $estadoConsumoModel;

    public function __construct()
    {
        parent::__construct();
        $this->estadoConsumoModel = new EstadoConsumo();
    }

    /**
     * Listar estados de consumo
     */
    public function index()
    {
        $this->requirePermission('estadosconsumo');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'estadoconsumo_descripcion' => $this->get('estadoconsumo_descripcion'),
            'estadoconsumo_estado' => $this->get('estadoconsumo_estado')
        ];

        $result = $this->estadoConsumoModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Estados de Consumo',
            'estados' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosconsumos/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo estado de consumo
     */
    public function create()
    {
        $this->requirePermission('estadosconsumo');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Estado de Consumo',
            'estado' => [],
            'isEdit' => false,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosconsumos/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo estado de consumo
     */
    public function store()
    {
        $this->requirePermission('estadosconsumo');

        $data = [
            'estadoconsumo_descripcion' => $this->post('estadoconsumo_descripcion'),
            'estadoconsumo_estado' => 1 // Activo por defecto
        ];

        // Validaciones básicas
        if (empty($data['estadoconsumo_descripcion'])) {
            $this->redirect('/estadosconsumo/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            $id = $this->estadoConsumoModel->create($data);
            if (!$id) {
                throw new \Exception('Error al crear el estado de consumo');
            }

            $this->redirect('/estadosconsumo', 'Estado de consumo creado correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect('/estadosconsumo/create', 'Error al crear el estado de consumo: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar estado de consumo específico
     */
    public function show($id)
    {
        $this->requirePermission('estadosconsumo');

        $estado = $this->estadoConsumoModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de uso del estado
        $estadisticas = $this->estadoConsumoModel->getEstadisticasUso($id);

        $data = [
            'title' => 'Detalle de Estado de Consumo',
            'estado' => $estado,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosconsumos/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('estadosconsumo');

        $estado = $this->estadoConsumoModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas de uso del estado
        $estadisticas = $this->estadoConsumoModel->getEstadisticasUso($id);

        $data = [
            'title' => 'Editar Estado de Consumo',
            'estado' => $estado,
            'estadisticas' => $estadisticas,
            'isEdit' => true,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosconsumos/formulario', $data, 'main');
    }

    /**
     * Actualizar estado de consumo
     */
    public function update($id)
    {
        $this->requirePermission('estadosconsumo');

        $estado = $this->estadoConsumoModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        $data = [
            'estadoconsumo_descripcion' => $this->post('estadoconsumo_descripcion')
        ];

        if (empty($data['estadoconsumo_descripcion'])) {
            $this->redirect("/estadosconsumo/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            if (!$this->estadoConsumoModel->update($id, $data)) {
                throw new \Exception('Error al actualizar el estado de consumo');
            }

            $this->redirect('/estadosconsumo', 'Estado de consumo actualizado correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect("/estadosconsumo/$id/edit", 'Error al actualizar el estado de consumo: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de estado de consumo
     */
    public function delete($id)
    {
        $this->requirePermission('estadosconsumo');

        $estado = $this->estadoConsumoModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        // Verificar si el estado está en uso
        $estadisticas = $this->estadoConsumoModel->getEstadisticasUso($id);
        if ($estadisticas['total_consumos'] > 0) {
            $this->redirect('/estadosconsumo', 'No se puede eliminar un estado que está siendo utilizado', 'error');
            return;
        }

        if ($this->estadoConsumoModel->softDelete($id, 'estadoconsumo_estado')) {
            $this->redirect('/estadosconsumo', 'Estado de consumo eliminado correctamente', 'exito');
        } else {
            $this->redirect('/estadosconsumo', 'Error al eliminar el estado de consumo', 'error');
        }
    }

    /**
     * Restaurar estado de consumo
     */
    public function restore($id)
    {
        $this->requirePermission('estadosconsumo');

        if ($this->estadoConsumoModel->restore($id, 'estadoconsumo_estado')) {
            $this->redirect('/estadosconsumo', 'Estado de consumo restaurado correctamente', 'exito');
        } else {
            $this->redirect('/estadosconsumo', 'Error al restaurar el estado de consumo', 'error');
        }
    }

    /**
     * Cambiar estado (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('estadosconsumo');

        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        $estado = $this->estadoConsumoModel->find($id);
        if (!$estado) {
            return $this->json(['success' => false, 'message' => 'Estado de consumo no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido'], 400);
        }

        // Actualizar el estado
        $data = ['estadoconsumo_estado' => $nuevoEstado];
        $resultado = $this->estadoConsumoModel->update($id, $data);

        if ($resultado) {
            $accion = $nuevoEstado == 1 ? 'activado' : 'desactivado';
            return $this->json([
                'success' => true, 
                'message' => "Estado de consumo {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado'
            ], 500);
        }
    }

    /**
     * Exportar estados de consumo a Excel
     */
    public function exportar()
    {
        $this->requirePermission('estadosconsumo');

        try {
            $filters = [
                'estadoconsumo_descripcion' => $this->get('estadoconsumo_descripcion'),
                'estadoconsumo_estado' => $this->get('estadoconsumo_estado')
            ];

            $result = $this->estadoConsumoModel->getAllWithDetailsForExport($filters);
            $estados = $result['data'];

            if (empty($estados)) {
                $this->redirect('/estadosconsumo', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Estados de Consumo');

            // Definir encabezados
            $headers = [
                'A1' => 'Descripción',
                'B1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:B1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:B1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:B1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($estados as $estado) {
                $estadoTexto = $estado['estadoconsumo_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $estado['estadoconsumo_descripcion']);
                $worksheet->setCellValue('B' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(50);
            $worksheet->getColumnDimension('B')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $fecha = date('Y-m-d');
            $nombreArchivo = "estados_consumo_{$fecha}.xlsx";

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
            error_log("Error al exportar estados de consumo: " . $e->getMessage());
            $this->redirect('/estadosconsumo', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar estados de consumo a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('estadosconsumo');

        try {
            $filters = [
                'estadoconsumo_descripcion' => $this->get('estadoconsumo_descripcion'),
                'estadoconsumo_estado' => $this->get('estadoconsumo_estado')
            ];

            $result = $this->estadoConsumoModel->getAllWithDetailsForExport($filters);
            $estados = $result['data'];

            if (empty($estados)) {
                $this->redirect('/estadosconsumo', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Estados de Consumo');
            $pdf->SetSubject('Exportación de Estados de Consumo');

            $pdf->SetMargins(15, 20, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 10);

            $pdf->AddPage();

            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, 'Listado de Estados de Consumo', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros
            $filtrosTexto = [];
            if (!empty($filters['estadoconsumo_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['estadoconsumo_descripcion'];
            }
            if (isset($filters['estadoconsumo_estado']) && $filters['estadoconsumo_estado'] !== '') {
                $filtrosTexto[] = 'Estado: ' . ($filters['estadoconsumo_estado'] == 1 ? 'Activo' : 'Inactivo');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($estados), 0, 1, 'L');
            $pdf->Ln(5);

            // Crear tabla HTML
            $html = '<style>
                table { border-collapse: collapse; width: 100%; }
                th { background-color: #E3F2FD; border: 1px solid #333; padding: 8px; text-align: center; font-weight: bold; }
                td { border: 1px solid #666; padding: 6px; }
                .descripcion { width: 70%; }
                .estado { text-align: center; width: 30%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="descripcion">Descripción</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($estados as $estado) {
                $estadoTexto = $estado['estadoconsumo_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $estado['estadoconsumo_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($estado['estadoconsumo_descripcion']) . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $fecha = date('Y-m-d');
            $nombreArchivo = "estados_consumo_{$fecha}.pdf";

            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar estados de consumo a PDF: " . $e->getMessage());
            $this->redirect('/estadosconsumo', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

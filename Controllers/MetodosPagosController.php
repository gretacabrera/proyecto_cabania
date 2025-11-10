<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MetodoPago;

/**
 * Controlador para el manejo de métodos de pago
 */
class MetodosPagosController extends Controller
{
    protected $modelo;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new MetodoPago();
    }

    /**
     * Listar métodos de pago
     */
    public function index()
    {
        $this->requirePermission('metodosdepago');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'metododepago_descripcion' => $this->get('metododepago_descripcion'),
            'metododepago_estado' => $this->get('metododepago_estado')
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Métodos de Pago',
            'metodos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/metodosdepago/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo método de pago
     */
    public function create()
    {
        $this->requirePermission('metodosdepago');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Método de Pago',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/metodosdepago/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo método de pago
     */
    public function store()
    {
        $this->requirePermission('metodosdepago');

        $data = [
            'metododepago_descripcion' => $this->post('metododepago_descripcion'),
            'metododepago_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['metododepago_descripcion'])) {
            $this->redirect('/metodosdepago/create', 'Complete los campos obligatorios', 'error');
        }

        try {
            $id = $this->modelo->create($data);
            if ($id) {
                $this->redirect('/metodosdepago', 'Método de pago creado correctamente', 'exito');
            } else {
                $this->redirect('/metodosdepago/create', 'Error al crear el método de pago', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/metodosdepago/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar método de pago específico
     */
    public function show($id)
    {
        $this->requirePermission('metodosdepago');

        $metodo = $this->modelo->find($id);
        if (!$metodo) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del método de pago
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Detalle de Método de Pago',
            'metodo' => $metodo,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/metodosdepago/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('metodosdepago');

        $metodo = $this->modelo->find($id);
        if (!$metodo) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas del método de pago
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Editar Método de Pago',
            'metodo' => $metodo,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/metodosdepago/formulario', $data, 'main');
    }

    /**
     * Actualizar método de pago
     */
    public function update($id)
    {
        $this->requirePermission('metodosdepago');

        $metodo = $this->modelo->find($id);
        if (!$metodo) {
            return $this->view->error(404);
        }

        $data = [
            'metododepago_descripcion' => $this->post('metododepago_descripcion')
        ];

        if (empty($data['metododepago_descripcion'])) {
            $this->redirect("/metodosdepago/$id/edit", 'Complete los campos obligatorios', 'error');
        }

        try {
            if ($this->modelo->update($id, $data)) {
                $this->redirect('/metodosdepago', 'Método de pago actualizado correctamente', 'exito');
            } else {
                $this->redirect("/metodosdepago/$id/edit", 'Error al actualizar el método de pago', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/metodosdepago/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de método de pago
     */
    public function delete($id)
    {
        $this->requirePermission('metodosdepago');

        $metodo = $this->modelo->find($id);
        if (!$metodo) {
            return $this->view->error(404);
        }

        if ($this->modelo->softDelete($id, 'metododepago_estado')) {
            $this->redirect('/metodosdepago', 'Método de pago eliminado correctamente', 'exito');
        } else {
            $this->redirect('/metodosdepago', 'Error al eliminar el método de pago', 'error');
        }
    }

    /**
     * Restaurar método de pago
     */
    public function restore($id)
    {
        $this->requirePermission('metodosdepago');

        if ($this->modelo->restore($id, 'metododepago_estado')) {
            $this->redirect('/metodosdepago', 'Método de pago restaurado correctamente', 'exito');
        } else {
            $this->redirect('/metodosdepago', 'Error al restaurar el método de pago', 'error');
        }
    }

    /**
     * Cambiar estado de método de pago (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('metodosdepago');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el método de pago existe
        $metodo = $this->modelo->find($id);
        if (!$metodo) {
            return $this->json(['success' => false, 'message' => 'Método de pago no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido'], 400);
        }

        // Actualizar el estado
        $data = ['metododepago_estado' => $nuevoEstado];
        $resultado = $this->modelo->update($id, $data);

        if ($resultado) {
            $estadoTexto = $nuevoEstado == 1 ? 'activo' : 'inactivo';
            return $this->json([
                'success' => true, 
                'message' => "Método de pago marcado como {$estadoTexto} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del método de pago'
            ], 500);
        }
    }

    /**
     * Exportar métodos de pago a Excel
     */
    public function exportar()
    {
        $this->requirePermission('metodosdepago');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'metododepago_descripcion' => $this->get('metododepago_descripcion'),
                'metododepago_estado' => $this->get('metododepago_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $metodos = $result['data'];

            if (empty($metodos)) {
                $this->redirect('/metodosdepago', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Métodos de Pago');

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
            foreach ($metodos as $metodo) {
                $estadoTexto = $metodo['metododepago_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $metodo['metododepago_descripcion']);
                $worksheet->setCellValue('B' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(40);
            $worksheet->getColumnDimension('B')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "metodos_pago_{$fecha}.xlsx";

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
            error_log("Error al exportar métodos de pago: " . $e->getMessage());
            $this->redirect('/metodosdepago', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar métodos de pago a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('metodosdepago');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'metododepago_descripcion' => $this->get('metododepago_descripcion'),
                'metododepago_estado' => $this->get('metododepago_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $metodos = $result['data'];

            if (empty($metodos)) {
                $this->redirect('/metodosdepago', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Métodos de Pago');
            $pdf->SetSubject('Exportación de Métodos de Pago');

            // Configurar márgenes
            $pdf->SetMargins(15, 20, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);

            // Configurar auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 25);

            // Establecer fuente
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 10);

            // Agregar página
            $pdf->AddPage();

            // Título del documento
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, 'Listado de Métodos de Pago', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de generación
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($metodos), 0, 1, 'L');
            $pdf->Ln(5);

            // Crear tabla HTML
            $html = '<style>
                table { border-collapse: collapse; width: 100%; }
                th { background-color: #E3F2FD; border: 1px solid #333; padding: 8px; text-align: center; font-weight: bold; }
                td { border: 1px solid #666; padding: 6px; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th style="width: 70%;">Descripción</th>
                        <th style="width: 30%;">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($metodos as $metodo) {
                $estadoTexto = $metodo['metododepago_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $metodo['metododepago_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td>' . htmlspecialchars($metodo['metododepago_descripcion']) . '</td>
                    <td class="' . $estadoClase . '" style="text-align: center;">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "metodos_pago_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar métodos de pago a PDF: " . $e->getMessage());
            $this->redirect('/metodosdepago', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}


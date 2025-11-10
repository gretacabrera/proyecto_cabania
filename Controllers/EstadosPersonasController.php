<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EstadoPersona;

/**
 * Controlador para el manejo de estados de personas
 */
class EstadosPersonasController extends Controller
{
    protected $estadoPersonaModel;

    public function __construct()
    {
        parent::__construct();
        $this->estadoPersonaModel = new EstadoPersona();
    }

    /**
     * Listar estados de personas
     */
    public function index()
    {
        $this->requirePermission('estadospersonas');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'estadopersona_descripcion' => $this->get('estadopersona_descripcion'),
            'estadopersona_estado' => $this->get('estadopersona_estado')
        ];

        $result = $this->estadoPersonaModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Estados de Personas',
            'estados' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadospersonas/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo estado
     */
    public function create()
    {
        $this->requirePermission('estadospersonas');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Estado de Persona',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadospersonas/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo estado
     */
    public function store()
    {
        $this->requirePermission('estadospersonas');

        $data = [
            'estadopersona_descripcion' => $this->post('estadopersona_descripcion'),
            'estadopersona_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['estadopersona_descripcion'])) {
            $this->redirect('/estadospersonas/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            $id = $this->estadoPersonaModel->create($data);
            if ($id) {
                $this->redirect('/estadospersonas', 'Estado creado correctamente', 'exito');
            } else {
                $this->redirect('/estadospersonas/create', 'Error al crear el estado', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/estadospersonas/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar estado específico
     */
    public function show($id)
    {
        $this->requirePermission('estadospersonas');

        $estado = $this->estadoPersonaModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del estado
        $estadisticas = $this->estadoPersonaModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Estado de Persona',
            'estado' => $estado,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadospersonas/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('estadospersonas');

        $estado = $this->estadoPersonaModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $data = [
            'title' => 'Editar Estado de Persona',
            'estado' => $estado,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadospersonas/formulario', $data, 'main');
    }

    /**
     * Actualizar estado
     */
    public function update($id)
    {
        $this->requirePermission('estadospersonas');

        $estado = $this->estadoPersonaModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        $data = [
            'estadopersona_descripcion' => $this->post('estadopersona_descripcion')
        ];

        if (empty($data['estadopersona_descripcion'])) {
            $this->redirect("/estadospersonas/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            if ($this->estadoPersonaModel->update($id, $data)) {
                $this->redirect('/estadospersonas', 'Estado actualizado correctamente', 'exito');
            } else {
                $this->redirect("/estadospersonas/$id/edit", 'Error al actualizar el estado', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/estadospersonas/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de estado
     */
    public function delete($id)
    {
        $this->requirePermission('estadospersonas');

        $estado = $this->estadoPersonaModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        if ($this->estadoPersonaModel->softDelete($id, 'estadopersona_estado')) {
            $this->redirect('/estadospersonas', 'Estado eliminado correctamente', 'exito');
        } else {
            $this->redirect('/estadospersonas', 'Error al eliminar el estado', 'error');
        }
    }

    /**
     * Restaurar estado
     */
    public function restore($id)
    {
        $this->requirePermission('estadospersonas');

        if ($this->estadoPersonaModel->restore($id, 'estadopersona_estado')) {
            $this->redirect('/estadospersonas', 'Estado restaurado correctamente', 'exito');
        } else {
            $this->redirect('/estadospersonas', 'Error al restaurar el estado', 'error');
        }
    }

    /**
     * Cambiar estado (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('estadospersonas');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el estado existe
        $estado = $this->estadoPersonaModel->find($id);
        if (!$estado) {
            return $this->json(['success' => false, 'message' => 'Estado no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido'], 400);
        }

        // Actualizar el estado
        $data = ['estadopersona_estado' => $nuevoEstado];
        $resultado = $this->estadoPersonaModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = $nuevoEstado == 1 ? 'activo' : 'inactivo';
            return $this->json([
                'success' => true, 
                'message' => "Estado marcado como {$estadoTexto} correctamente",
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
     * Exportar estados a Excel
     */
    public function exportar()
    {
        $this->requirePermission('estadospersonas');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'estadopersona_descripcion' => $this->get('estadopersona_descripcion'),
                'estadopersona_estado' => $this->get('estadopersona_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->estadoPersonaModel->getAllWithDetailsForExport($filters);
            $estados = $result['data'];

            if (empty($estados)) {
                $this->redirect('/estadospersonas', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Estados de Personas');

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
                $estadoTexto = $estado['estadopersona_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $estado['estadopersona_descripcion']);
                $worksheet->setCellValue('B' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(60);
            $worksheet->getColumnDimension('B')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "estados_personas_{$fecha}.xlsx";

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
            error_log("Error al exportar estados de personas: " . $e->getMessage());
            $this->redirect('/estadospersonas', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar estados a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('estadospersonas');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'estadopersona_descripcion' => $this->get('estadopersona_descripcion'),
                'estadopersona_estado' => $this->get('estadopersona_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->estadoPersonaModel->getAllWithDetailsForExport($filters);
            $estados = $result['data'];

            if (empty($estados)) {
                $this->redirect('/estadospersonas', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Estados de Personas');
            $pdf->SetSubject('Exportación de Estados de Personas');

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
            $pdf->Cell(0, 15, 'Listado de Estados de Personas', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados
            $filtrosTexto = [];
            if (!empty($filters['estadopersona_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['estadopersona_descripcion'];
            }
            if (isset($filters['estadopersona_estado']) && $filters['estadopersona_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['estadopersona_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($estados), 0, 1, 'L');
            $pdf->Ln(5);

            // Crear tabla HTML
            $html = '<style>
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                }
                th { 
                    background-color: #E3F2FD; 
                    border: 1px solid #333; 
                    padding: 8px; 
                    text-align: center; 
                    font-weight: bold; 
                }
                td { 
                    border: 1px solid #666; 
                    padding: 6px; 
                    vertical-align: middle;
                }
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

            // Llenar datos
            foreach ($estados as $estado) {
                $estadoTexto = $estado['estadopersona_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $estado['estadopersona_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($estado['estadopersona_descripcion']) . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "estados_personas_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar estados de personas a PDF: " . $e->getMessage());
            $this->redirect('/estadospersonas', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TipoServicio;

/**
 * Controlador para el manejo de tipos de servicios
 */
class TiposServiciosController extends Controller
{
    protected $tipoServicioModel;

    public function __construct()
    {
        parent::__construct();
        $this->tipoServicioModel = new TipoServicio();
    }

    /**
     * Listar tipos de servicios
     */
    public function index()
    {
        $this->requirePermission('tiposservicios');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'tiposervicio_descripcion' => $this->get('tiposervicio_descripcion'),
            'tiposervicio_estado' => $this->get('tiposervicio_estado')
        ];

        $result = $this->tipoServicioModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Tipos de Servicios',
            'tiposservicios' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/tiposservicios/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo tipo de servicio
     */
    public function create()
    {
        $this->requirePermission('tiposservicios');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Tipo de Servicio',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/tiposservicios/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo tipo de servicio
     */
    public function store()
    {
        $this->requirePermission('tiposservicios');

        $data = [
            'tiposervicio_descripcion' => $this->post('tiposervicio_descripcion'),
            'tiposervicio_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['tiposervicio_descripcion'])) {
            $this->redirect('/tiposservicios/create', 'Complete los campos obligatorios', 'error');
        }

        try {
            $id = $this->tipoServicioModel->create($data);
            if ($id) {
                $this->redirect('/tiposservicios', 'Tipo de servicio creado correctamente', 'exito');
            } else {
                $this->redirect('/tiposservicios/create', 'Error al crear el tipo de servicio', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/tiposservicios/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar tipo de servicio específico
     */
    public function show($id)
    {
        $this->requirePermission('tiposservicios');

        $tiposervicio = $this->tipoServicioModel->find($id);
        if (!$tiposervicio) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del tipo de servicio
        $estadisticas = $this->tipoServicioModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Tipo de Servicio',
            'tiposervicio' => $tiposervicio,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/tiposservicios/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('tiposservicios');

        $tiposervicio = $this->tipoServicioModel->find($id);
        if (!$tiposervicio) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas para el formulario de edición
        $estadisticas = $this->tipoServicioModel->getStatistics($id);

        $data = [
            'title' => 'Editar Tipo de Servicio',
            'tiposervicio' => $tiposervicio,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/tiposservicios/formulario', $data, 'main');
    }

    /**
     * Actualizar tipo de servicio
     */
    public function update($id)
    {
        $this->requirePermission('tiposservicios');

        $tiposervicio = $this->tipoServicioModel->find($id);
        if (!$tiposervicio) {
            return $this->view->error(404);
        }

        $data = [
            'tiposervicio_descripcion' => $this->post('tiposervicio_descripcion')
        ];

        if (empty($data['tiposervicio_descripcion'])) {
            $this->redirect("/tiposservicios/$id/edit", 'Complete los campos obligatorios', 'error');
        }

        try {
            if ($this->tipoServicioModel->update($id, $data)) {
                $this->redirect('/tiposservicios', 'Tipo de servicio actualizado correctamente', 'exito');
            } else {
                $this->redirect("/tiposservicios/$id/edit", 'Error al actualizar el tipo de servicio', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/tiposservicios/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de tipo de servicio
     */
    public function delete($id)
    {
        $this->requirePermission('tiposservicios');

        $tiposervicio = $this->tipoServicioModel->find($id);
        if (!$tiposervicio) {
            return $this->view->error(404);
        }

        if ($this->tipoServicioModel->softDelete($id, 'tiposervicio_estado')) {
            $this->redirect('/tiposservicios', 'Tipo de servicio eliminado correctamente', 'exito');
        } else {
            $this->redirect('/tiposservicios', 'Error al eliminar el tipo de servicio', 'error');
        }
    }

    /**
     * Restaurar tipo de servicio
     */
    public function restore($id)
    {
        $this->requirePermission('tiposservicios');

        if ($this->tipoServicioModel->restore($id, 'tiposervicio_estado')) {
            $this->redirect('/tiposservicios', 'Tipo de servicio restaurado correctamente', 'exito');
        } else {
            $this->redirect('/tiposservicios', 'Error al restaurar el tipo de servicio', 'error');
        }
    }

    /**
     * Cambiar estado de tipo de servicio (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('tiposservicios');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el tipo de servicio existe
        $tiposervicio = $this->tipoServicioModel->find($id);
        if (!$tiposervicio) {
            return $this->json(['success' => false, 'message' => 'Tipo de servicio no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['tiposervicio_estado' => $nuevoEstado];
        $resultado = $this->tipoServicioModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactivo', 'activo'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizado';
            return $this->json([
                'success' => true, 
                'message' => "Tipo de servicio marcado como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del tipo de servicio'
            ], 500);
        }
    }

    /**
     * Exportar tipos de servicios a Excel
     */
    public function exportar()
    {
        $this->requirePermission('tiposservicios');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'tiposervicio_descripcion' => $this->get('tiposervicio_descripcion'),
                'tiposervicio_estado' => $this->get('tiposervicio_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->tipoServicioModel->getAllWithDetailsForExport($filters);
            $tiposservicios = $result['data'];

            if (empty($tiposservicios)) {
                $this->redirect('/tiposservicios', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Tipos de Servicios');

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
            foreach ($tiposservicios as $tiposervicio) {
                // Mapear estado a texto
                $estadoTexto = $tiposervicio['tiposervicio_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $tiposervicio['tiposervicio_descripcion']);
                $worksheet->setCellValue('B' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(50);
            $worksheet->getColumnDimension('B')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "tipos_servicios_{$fecha}.xlsx";

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
            error_log("Error al exportar tipos de servicios: " . $e->getMessage());
            $this->redirect('/tiposservicios', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar tipos de servicios a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('tiposservicios');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'tiposervicio_descripcion' => $this->get('tiposervicio_descripcion'),
                'tiposervicio_estado' => $this->get('tiposervicio_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->tipoServicioModel->getAllWithDetailsForExport($filters);
            $tiposservicios = $result['data'];

            if (empty($tiposservicios)) {
                $this->redirect('/tiposservicios', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Tipos de Servicios');
            $pdf->SetSubject('Exportación de Tipos de Servicios');
            $pdf->SetKeywords('tipos servicios, listado, exportación');

            // Configurar márgenes
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
            $pdf->Cell(0, 15, 'Listado de Tipos de Servicios', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['tiposervicio_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['tiposervicio_descripcion'];
            }
            if (isset($filters['tiposervicio_estado']) && $filters['tiposervicio_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['tiposervicio_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($tiposservicios);
            $pdf->Cell(0, 10, $infoFormato, 0, 1, 'L');
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
                    padding: 5px; 
                    text-align: left; 
                    font-weight: bold; 
                    font-size: 9px;
                }
                td { 
                    border: 1px solid #666; 
                    padding: 4px; 
                    font-size: 8px; 
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
            foreach ($tiposservicios as $tiposervicio) {
                $estadoTexto = $tiposervicio['tiposervicio_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $tiposervicio['tiposervicio_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($tiposervicio['tiposervicio_descripcion']) . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "tipos_servicios_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar tipos de servicios a PDF: " . $e->getMessage());
            $this->redirect('/tiposservicios', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

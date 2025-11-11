<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\NivelDanio;

/**
 * Controlador para el manejo de niveles de daño
 */
class NivelDanioController extends Controller
{
    protected $modelo;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new NivelDanio();
    }

    /**
     * Listar niveles de daño
     */
    public function index()
    {
        $this->requirePermission('niveldanio');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'niveldanio_descripcion' => $this->get('niveldanio_descripcion'),
            'niveldanio_estado' => $this->get('niveldanio_estado')
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Niveles de Daño',
            'registros' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/niveldanio/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo nivel de daño
     */
    public function create()
    {
        $this->requirePermission('niveldanio');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Nivel de Daño',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/niveldanio/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo nivel de daño
     */
    public function store()
    {
        $this->requirePermission('niveldanio');

        $data = [
            'niveldanio_descripcion' => $this->post('niveldanio_descripcion'),
            'niveldanio_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['niveldanio_descripcion'])) {
            $this->redirect('/niveldanio/create', 'Complete los campos obligatorios', 'error');
        }

        try {
            $id = $this->modelo->create($data);
            if ($id) {
                $this->redirect('/niveldanio', 'Nivel de daño creado correctamente', 'exito');
            } else {
                $this->redirect('/niveldanio/create', 'Error al crear el nivel de daño', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/niveldanio/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar nivel de daño específico
     */
    public function show($id)
    {
        $this->requirePermission('niveldanio');

        $registro = $this->modelo->find($id);
        if (!$registro) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del nivel de daño
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Detalle de Nivel de Daño',
            'registro' => $registro,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/niveldanio/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('niveldanio');

        $registro = $this->modelo->find($id);
        if (!$registro) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas para el formulario
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Editar Nivel de Daño',
            'registro' => $registro,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/niveldanio/formulario', $data, 'main');
    }

    /**
     * Actualizar nivel de daño
     */
    public function update($id)
    {
        $this->requirePermission('niveldanio');

        $registro = $this->modelo->find($id);
        if (!$registro) {
            return $this->view->error(404);
        }

        $data = [
            'niveldanio_descripcion' => $this->post('niveldanio_descripcion')
        ];

        if (empty($data['niveldanio_descripcion'])) {
            $this->redirect("/niveldanio/$id/edit", 'Complete los campos obligatorios', 'error');
        }

        try {
            if ($this->modelo->update($id, $data)) {
                $this->redirect('/niveldanio', 'Nivel de daño actualizado correctamente', 'exito');
            } else {
                $this->redirect("/niveldanio/$id/edit", 'Error al actualizar el nivel de daño', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/niveldanio/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de nivel de daño
     */
    public function delete($id)
    {
        $this->requirePermission('niveldanio');

        $registro = $this->modelo->find($id);
        if (!$registro) {
            return $this->view->error(404);
        }

        if ($this->modelo->softDelete($id, 'niveldanio_estado')) {
            $this->redirect('/niveldanio', 'Nivel de daño eliminado correctamente', 'exito');
        } else {
            $this->redirect('/niveldanio', 'Error al eliminar el nivel de daño', 'error');
        }
    }

    /**
     * Restaurar nivel de daño
     */
    public function restore($id)
    {
        $this->requirePermission('niveldanio');

        if ($this->modelo->restore($id, 'niveldanio_estado')) {
            $this->redirect('/niveldanio', 'Nivel de daño restaurado correctamente', 'exito');
        } else {
            $this->redirect('/niveldanio', 'Error al restaurar el nivel de daño', 'error');
        }
    }

    /**
     * Cambiar estado de nivel de daño (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('niveldanio');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el nivel de daño existe
        $registro = $this->modelo->find($id);
        if (!$registro) {
            return $this->json(['success' => false, 'message' => 'Nivel de daño no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['niveldanio_estado' => $nuevoEstado];
        $resultado = $this->modelo->update($id, $data);

        if ($resultado) {
            $estadoTexto = $nuevoEstado == 1 ? 'activo' : 'inactivo';
            return $this->json([
                'success' => true, 
                'message' => "Nivel de daño marcado como {$estadoTexto} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del nivel de daño'
            ], 500);
        }
    }

    /**
     * Exportar niveles de daño a Excel
     */
    public function exportar()
    {
        $this->requirePermission('niveldanio');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'niveldanio_descripcion' => $this->get('niveldanio_descripcion'),
                'niveldanio_estado' => $this->get('niveldanio_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/niveldanio', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Niveles de Daño');

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
            foreach ($datos as $registro) {
                $estadoTexto = $registro['niveldanio_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $registro['niveldanio_descripcion']);
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
            $nombreArchivo = "niveles_danio_{$fecha}.xlsx";

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
            error_log("Error al exportar niveles de daño: " . $e->getMessage());
            $this->redirect('/niveldanio', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar niveles de daño a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('niveldanio');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'niveldanio_descripcion' => $this->get('niveldanio_descripcion'),
                'niveldanio_estado' => $this->get('niveldanio_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/niveldanio', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Niveles de Daño');
            $pdf->SetSubject('Exportación de Niveles de Daño');
            $pdf->SetKeywords('niveles, daño, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Niveles de Daño', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados
            $filtrosTexto = [];
            if (!empty($filters['niveldanio_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['niveldanio_descripcion'];
            }
            if (isset($filters['niveldanio_estado']) && $filters['niveldanio_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['niveldanio_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($datos);
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
                    font-size: 10px;
                }
                td { 
                    border: 1px solid #666; 
                    padding: 4px; 
                    font-size: 9px; 
                }
                .estado { text-align: center; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th style="width: 80%;">Descripción</th>
                        <th style="width: 20%;" class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($datos as $registro) {
                $estadoTexto = '';
                $estadoClase = '';
                if ($registro['niveldanio_estado'] == 1) {
                    $estadoTexto = 'Activo';
                    $estadoClase = 'estado-activo';
                } else {
                    $estadoTexto = 'Inactivo';
                    $estadoClase = 'estado-inactivo';
                }

                $html .= '<tr>
                    <td style="width: 80%;">' . htmlspecialchars($registro['niveldanio_descripcion']) . '</td>
                    <td style="width: 20%;" class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "niveles_danio_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar niveles de daño a PDF: " . $e->getMessage());
            $this->redirect('/niveldanio', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

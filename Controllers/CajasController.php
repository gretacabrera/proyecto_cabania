<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Caja;
use App\Models\Usuario;

/**
 * Controlador para el manejo de cajas
 */
class CajasController extends Controller
{
    protected $cajaModel;
    protected $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->cajaModel = new Caja();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Listar cajas
     */
    public function index()
    {
        $this->requirePermission('cajas');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'caja_descripcion' => $this->get('caja_descripcion'),
            'rela_usuario' => $this->get('rela_usuario'),
            'caja_estado' => $this->get('caja_estado')
        ];

        $result = $this->cajaModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Cajas',
            'cajas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'usuarios' => $this->usuarioModel->getAllActive(),
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva caja
     */
    public function create()
    {
        $this->requirePermission('cajas');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nueva Caja',
            'usuarios' => $this->usuarioModel->getUsuariosCajeros(),
            'caja' => [],
            'isEdit' => false,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas/formulario', $data, 'main');
    }

    /**
     * Guardar nueva caja
     */
    public function store()
    {
        $this->requirePermission('cajas');

        $data = [
            'caja_descripcion' => $this->post('caja_descripcion'),
            'rela_usuario' => (int) $this->post('rela_usuario'),
            'caja_estado' => 1 // Activa por defecto
        ];

        // Validaciones básicas
        if (empty($data['caja_descripcion']) || empty($data['rela_usuario'])) {
            $this->redirect('/cajas/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            $id = $this->cajaModel->create($data);
            if (!$id) {
                throw new \Exception('Error al crear la caja');
            }

            $this->redirect('/cajas', 'Caja creada correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect('/cajas/create', 'Error al crear la caja: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar caja específica
     */
    public function show($id)
    {
        $this->requirePermission('cajas');

        $caja = $this->cajaModel->findWithDetails($id);
        if (!$caja) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de la caja
        $estadisticas = $this->cajaModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Caja',
            'caja' => $caja,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('cajas');

        $caja = $this->cajaModel->findWithDetails($id);
        if (!$caja) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas de la caja
        $estadisticas = $this->cajaModel->getStatistics($id);

        $data = [
            'title' => 'Editar Caja',
            'caja' => $caja,
            'estadisticas' => $estadisticas,
            'usuarios' => $this->usuarioModel->getUsuariosCajeros(),
            'isEdit' => true,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas/formulario', $data, 'main');
    }

    /**
     * Actualizar caja
     */
    public function update($id)
    {
        $this->requirePermission('cajas');

        $caja = $this->cajaModel->find($id);
        if (!$caja) {
            return $this->view->error(404);
        }

        $data = [
            'caja_descripcion' => $this->post('caja_descripcion'),
            'rela_usuario' => (int) $this->post('rela_usuario')
        ];

        if (empty($data['caja_descripcion']) || empty($data['rela_usuario'])) {
            $this->redirect("/cajas/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            if (!$this->cajaModel->update($id, $data)) {
                throw new \Exception('Error al actualizar la caja');
            }

            $this->redirect('/cajas', 'Caja actualizada correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect("/cajas/$id/edit", 'Error al actualizar la caja: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de caja
     */
    public function delete($id)
    {
        $this->requirePermission('cajas');

        $caja = $this->cajaModel->find($id);
        if (!$caja) {
            return $this->view->error(404);
        }

        if ($this->cajaModel->softDelete($id, 'caja_estado')) {
            $this->redirect('/cajas', 'Caja eliminada correctamente', 'exito');
        } else {
            $this->redirect('/cajas', 'Error al eliminar la caja', 'error');
        }
    }

    /**
     * Restaurar caja
     */
    public function restore($id)
    {
        $this->requirePermission('cajas');

        if ($this->cajaModel->restore($id, 'caja_estado')) {
            $this->redirect('/cajas', 'Caja restaurada correctamente', 'exito');
        } else {
            $this->redirect('/cajas', 'Error al restaurar la caja', 'error');
        }
    }

    /**
     * Cambiar estado de caja (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('cajas');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que la caja existe
        $caja = $this->cajaModel->find($id);
        if (!$caja) {
            return $this->json(['success' => false, 'message' => 'Caja no encontrada'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactiva), 1 (activa)'], 400);
        }

        // Actualizar el estado
        $data = ['caja_estado' => $nuevoEstado];
        $resultado = $this->cajaModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = $nuevoEstado === 1 ? 'activa' : 'inactiva';
            return $this->json([
                'success' => true, 
                'message' => "Caja marcada como {$estadoTexto} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado de la caja'
            ], 500);
        }
    }

    /**
     * Exportar cajas a Excel
     */
    public function exportar()
    {
        $this->requirePermission('cajas');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'caja_descripcion' => $this->get('caja_descripcion'),
                'rela_usuario' => $this->get('rela_usuario'),
                'caja_estado' => $this->get('caja_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->cajaModel->getAllWithDetailsForExport($filters);
            $cajas = $result['data'];

            if (empty($cajas)) {
                $this->redirect('/cajas', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Cajas');

            // Definir encabezados
            $headers = [
                'A1' => 'Descripción',
                'B1' => 'Usuario Responsable',
                'C1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:C1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:C1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($cajas as $caja) {
                $estadoTexto = $caja['caja_estado'] == 1 ? 'Activa' : 'Inactiva';

                $worksheet->setCellValue('A' . $row, $caja['caja_descripcion']);
                $worksheet->setCellValue('B' . $row, $caja['usuario_nombre'] ?? '');
                $worksheet->setCellValue('C' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(40);
            $worksheet->getColumnDimension('B')->setWidth(30);
            $worksheet->getColumnDimension('C')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "cajas_{$fecha}.xlsx";

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
            error_log("Error al exportar cajas: " . $e->getMessage());
            $this->redirect('/cajas', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar cajas a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('cajas');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'caja_descripcion' => $this->get('caja_descripcion'),
                'rela_usuario' => $this->get('rela_usuario'),
                'caja_estado' => $this->get('caja_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->cajaModel->getAllWithDetailsForExport($filters);
            $cajas = $result['data'];

            if (empty($cajas)) {
                $this->redirect('/cajas', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Cajas');
            $pdf->SetSubject('Exportación de Cajas');
            $pdf->SetKeywords('cajas, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Cajas', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['caja_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['caja_descripcion'];
            }
            if (isset($filters['caja_estado']) && $filters['caja_estado'] !== '') {
                $estadosTexto = ['Inactiva', 'Activa'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['caja_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($cajas) . ' | Formato: A4 Vertical';
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
                .descripcion { width: 50%; }
                .usuario { width: 35%; }
                .estado { text-align: center; width: 15%; }
                .estado-activa { color: #28a745; font-weight: bold; }
                .estado-inactiva { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="descripcion">Descripción</th>
                        <th class="usuario">Usuario Responsable</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($cajas as $caja) {
                $estadoTexto = $caja['caja_estado'] == 1 ? 'Activa' : 'Inactiva';
                $estadoClase = $caja['caja_estado'] == 1 ? 'estado-activa' : 'estado-inactiva';

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($caja['caja_descripcion']) . '</td>
                    <td class="usuario">' . htmlspecialchars($caja['usuario_nombre'] ?? '') . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "cajas_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar cajas a PDF: " . $e->getMessage());
            $this->redirect('/cajas', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

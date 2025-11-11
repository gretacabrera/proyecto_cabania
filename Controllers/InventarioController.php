<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Inventario;

/**
 * Controlador para el manejo de inventario
 */
class InventarioController extends Controller
{
    protected $inventarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->inventarioModel = new Inventario();
    }

    /**
     * Listar inventarios
     */
    public function index()
    {
        $this->requirePermission('inventario');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'inventario_descripcion' => $this->get('inventario_descripcion'),
            'inventario_stock_min' => $this->get('inventario_stock_min'),
            'inventario_estado' => $this->get('inventario_estado')
        ];

        $result = $this->inventarioModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Inventario',
            'inventarios' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/inventario/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo inventario
     */
    public function create()
    {
        $this->requirePermission('inventario');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Inventario',
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/inventario/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo inventario
     */
    public function store()
    {
        $this->requirePermission('inventario');

        $data = [
            'inventario_descripcion' => $this->post('inventario_descripcion'),
            'inventario_stock' => $this->post('inventario_stock'),
            'inventario_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['inventario_descripcion'])) {
            $this->redirect('/inventario/create', 'Complete los campos obligatorios', 'error');
        }

        // Validar que el stock sea un número válido
        if (!is_numeric($data['inventario_stock']) || $data['inventario_stock'] < 0) {
            $this->redirect('/inventario/create', 'El stock debe ser un número válido mayor o igual a 0', 'error');
        }

        try {
            $id = $this->inventarioModel->create($data);
            if ($id) {
                $this->redirect('/inventario', 'Inventario creado correctamente', 'exito');
            } else {
                $this->redirect('/inventario/create', 'Error al crear el inventario', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/inventario/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar inventario específico
     */
    public function show($id)
    {
        $this->requirePermission('inventario');

        $inventario = $this->inventarioModel->find($id);
        if (!$inventario) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del inventario
        $estadisticas = $this->inventarioModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Inventario',
            'inventario' => $inventario,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/inventario/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('inventario');

        $inventario = $this->inventarioModel->find($id);
        if (!$inventario) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $data = [
            'title' => 'Editar Inventario',
            'inventario' => $inventario,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/inventario/formulario', $data, 'main');
    }

    /**
     * Actualizar inventario
     */
    public function update($id)
    {
        $this->requirePermission('inventario');

        $inventario = $this->inventarioModel->find($id);
        if (!$inventario) {
            return $this->view->error(404);
        }

        $data = [
            'inventario_descripcion' => $this->post('inventario_descripcion'),
            'inventario_stock' => $this->post('inventario_stock')
        ];

        if (empty($data['inventario_descripcion'])) {
            $this->redirect("/inventario/$id/edit", 'Complete los campos obligatorios', 'error');
        }

        // Validar que el stock sea un número válido
        if (!is_numeric($data['inventario_stock']) || $data['inventario_stock'] < 0) {
            $this->redirect("/inventario/$id/edit", 'El stock debe ser un número válido mayor o igual a 0', 'error');
        }

        try {
            if ($this->inventarioModel->update($id, $data)) {
                $this->redirect('/inventario', 'Inventario actualizado correctamente', 'exito');
            } else {
                $this->redirect("/inventario/$id/edit", 'Error al actualizar el inventario', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/inventario/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de inventario
     */
    public function delete($id)
    {
        $this->requirePermission('inventario');

        $inventario = $this->inventarioModel->find($id);
        if (!$inventario) {
            return $this->view->error(404);
        }

        if ($this->inventarioModel->softDelete($id, 'inventario_estado')) {
            $this->redirect('/inventario', 'Inventario eliminado correctamente', 'exito');
        } else {
            $this->redirect('/inventario', 'Error al eliminar el inventario', 'error');
        }
    }

    /**
     * Restaurar inventario
     */
    public function restore($id)
    {
        $this->requirePermission('inventario');

        if ($this->inventarioModel->restore($id, 'inventario_estado')) {
            $this->redirect('/inventario', 'Inventario restaurado correctamente', 'exito');
        } else {
            $this->redirect('/inventario', 'Error al restaurar el inventario', 'error');
        }
    }

    /**
     * Cambiar estado de inventario (AJAX)
     */
    public function cambiarEstado($id)
    {
        error_log("Petición recibida en cambiarEstado - ID: $id");
        error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("URL completa: " . $_SERVER['REQUEST_URI']);
        
        $this->requirePermission('inventario');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            error_log("Error: No es una petición AJAX");
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el inventario existe
        $inventario = $this->inventarioModel->find($id);
        if (!$inventario) {
            error_log("Error: Inventario no encontrado - ID: $id");
            return $this->json(['success' => false, 'message' => 'Inventario no encontrado'], 404);
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
        $data = ['inventario_estado' => $nuevoEstado];
        $resultado = $this->inventarioModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactivo', 'activo'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizado';
            error_log("Estado cambiado exitosamente - ID: $id, Nuevo estado: $nuevoEstado");
            return $this->json([
                'success' => true, 
                'message' => "Inventario marcado como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            error_log("Error al actualizar el estado en la base de datos - ID: $id");
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del inventario'
            ], 500);
        }
    }

    /**
     * Exportar inventarios a Excel
     */
    public function exportar()
    {
        $this->requirePermission('inventario');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'inventario_descripcion' => $this->get('inventario_descripcion'),
                'inventario_stock_min' => $this->get('inventario_stock_min'),
                'inventario_estado' => $this->get('inventario_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->inventarioModel->getAllWithDetailsForExport($filters);
            $inventarios = $result['data'];

            if (empty($inventarios)) {
                $this->redirect('/inventario', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Inventario');

            // Definir encabezados
            $headers = [
                'A1' => 'Descripción',
                'B1' => 'Stock',
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
            foreach ($inventarios as $inventario) {
                // Mapear estado a texto
                $estadoTexto = $inventario['inventario_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $inventario['inventario_descripcion']);
                $worksheet->setCellValue('B' . $row, $inventario['inventario_stock']);
                $worksheet->setCellValue('C' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(50);
            $worksheet->getColumnDimension('B')->setWidth(15);
            $worksheet->getColumnDimension('C')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "inventario_{$fecha}.xlsx";

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
            error_log("Error al exportar inventario: " . $e->getMessage());
            $this->redirect('/inventario', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar inventarios a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('inventario');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'inventario_descripcion' => $this->get('inventario_descripcion'),
                'inventario_stock_min' => $this->get('inventario_stock_min'),
                'inventario_estado' => $this->get('inventario_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->inventarioModel->getAllWithDetailsForExport($filters);
            $inventarios = $result['data'];

            if (empty($inventarios)) {
                $this->redirect('/inventario', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Inventario');
            $pdf->SetSubject('Exportación de Inventario');
            $pdf->SetKeywords('inventario, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Inventario', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['inventario_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['inventario_descripcion'];
            }
            if (!empty($filters['inventario_stock_min'])) {
                $filtrosTexto[] = 'Stock mín.: ' . $filters['inventario_stock_min'];
            }
            if (isset($filters['inventario_estado']) && $filters['inventario_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['inventario_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($inventarios) . ' | Formato: A4 Vertical';
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
                    font-size: 9px;
                    word-wrap: break-word;
                }
                td { 
                    border: 1px solid #666; 
                    padding: 2px; 
                    font-size: 8px; 
                    vertical-align: top;
                    word-wrap: break-word;
                    overflow: hidden;
                }
                .descripcion { width: 60%; }
                .numero { text-align: center; width: 20%; }
                .estado { text-align: center; width: 20%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="descripcion">Descripción</th>
                        <th class="numero">Stock</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($inventarios as $inventario) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = $inventario['inventario_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $inventario['inventario_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($inventario['inventario_descripcion']) . '</td>
                    <td class="numero">' . $inventario['inventario_stock'] . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "inventario_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar inventario a PDF: " . $e->getMessage());
            $this->redirect('/inventario', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

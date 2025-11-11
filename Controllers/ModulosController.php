<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Menu;
use App\Models\Modulo;

/**
 * Controlador para el manejo de módulos
 */
class ModulosController extends Controller
{
    protected $moduloModel;

    public function __construct()
    {
        parent::__construct();
        $this->moduloModel = new Modulo();
    }

    /**
     * Listar módulos
     */
    public function index()
    {
        $this->requirePermission('modulos');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'modulo_descripcion' => $this->get('modulo_descripcion'),
            'modulo_ruta' => $this->get('modulo_ruta'),
            'rela_menu' => $this->get('rela_menu'),
            'modulo_estado' => $this->get('modulo_estado')
        ];

        $result = $this->moduloModel->getWithDetails($page, $perPage, $filters);

        // Obtener menús para el filtro
        $menuModel = new Menu();
        $menus = $menuModel->getActive();

        $data = [
            'title' => 'Gestión de Módulos',
            'modulos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'menus' => $menus,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/modulos/listado', $data, 'main');
    }


    /**
     * Mostrar formulario de nuevo módulo
     */
    public function create()
    {
        $this->requirePermission('modulos');

        if ($this->isPost()) {
            return $this->store();
        }

        // Obtener menús activos para el select
        $menuModel = new Menu();
        $menus = $menuModel->getActive();

        $data = [
            'title' => 'Nuevo Módulo',
            'menus' => $menus,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/modulos/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo módulo
     */
    public function store()
    {
        $this->requirePermission('modulos');

        $data = [
            'modulo_descripcion' => $this->post('modulo_descripcion'),
            'modulo_ruta' => $this->post('modulo_ruta'),
            'rela_menu' => $this->post('rela_menu') ?: null,
            'modulo_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['modulo_descripcion']) || empty($data['modulo_ruta'])) {
            $this->redirect('/modulos/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            $id = $this->moduloModel->create($data);
            if ($id) {
                $this->redirect('/modulos', 'Módulo creado correctamente', 'exito');
            } else {
                $this->redirect('/modulos/create', 'Error al crear el módulo', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/modulos/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar módulo específico
     */
    public function show($id)
    {
        $this->requirePermission('modulos');

        $modulo = $this->moduloModel->find($id);
        if (!$modulo) {
            return $this->view->error(404);
        }

        // Obtener nombre del menú si existe relación
        if ($modulo['rela_menu']) {
            $menuModel = new Menu();
            $menus = $menuModel->getActive();
            foreach ($menus as $menu) {
                if ($menu['id_menu'] == $modulo['rela_menu']) {
                    $modulo['menu_nombre'] = $menu['menu_nombre'];
                    break;
                }
            }
        }

        // Obtener estadísticas del módulo
        $estadisticas = $this->moduloModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Módulo',
            'modulo' => $modulo,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/modulos/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('modulos');

        $modulo = $this->moduloModel->find($id);
        if (!$modulo) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener menús activos para el select
        $menuModel = new Menu();
        $menus = $menuModel->getActive();

        // Obtener estadísticas del módulo
        $estadisticas = $this->moduloModel->getStatistics($id);

        $data = [
            'title' => 'Editar Módulo',
            'modulo' => $modulo,
            'menus' => $menus,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/modulos/formulario', $data, 'main');
    }

    /**
     * Actualizar módulo
     */
    public function update($id)
    {
        $this->requirePermission('modulos');

        $modulo = $this->moduloModel->find($id);
        if (!$modulo) {
            return $this->view->error(404);
        }

        $data = [
            'modulo_descripcion' => $this->post('modulo_descripcion'),
            'modulo_ruta' => $this->post('modulo_ruta'),
            'rela_menu' => $this->post('rela_menu') ?: null
        ];

        if (empty($data['modulo_descripcion']) || empty($data['modulo_ruta'])) {
            $this->redirect("/modulos/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            if ($this->moduloModel->update($id, $data)) {
                $this->redirect('/modulos', 'Módulo actualizado correctamente', 'exito');
            } else {
                $this->redirect("/modulos/$id/edit", 'Error al actualizar el módulo', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/modulos/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de módulo
     */
    public function delete($id)
    {
        $this->requirePermission('modulos');

        $modulo = $this->moduloModel->find($id);
        if (!$modulo) {
            return $this->view->error(404);
        }

        if ($this->moduloModel->softDelete($id, 'modulo_estado')) {
            $this->redirect('/modulos', 'Módulo eliminado correctamente', 'exito');
        } else {
            $this->redirect('/modulos', 'Error al eliminar el módulo', 'error');
        }
    }

    /**
     * Restaurar módulo
     */
    public function restore($id)
    {
        $this->requirePermission('modulos');

        if ($this->moduloModel->restore($id, 'modulo_estado')) {
            $this->redirect('/modulos', 'Módulo restaurado correctamente', 'exito');
        } else {
            $this->redirect('/modulos', 'Error al restaurar el módulo', 'error');
        }
    }

    /**
     * Cambiar estado de módulo (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('modulos');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el módulo existe
        $modulo = $this->moduloModel->find($id);
        if (!$modulo) {
            return $this->json(['success' => false, 'message' => 'Módulo no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['modulo_estado' => $nuevoEstado];
        $resultado = $this->moduloModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactivo', 'activo'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizado';
            return $this->json([
                'success' => true, 
                'message' => "Módulo marcado como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del módulo'
            ], 500);
        }
    }

    /**
     * Exportar módulos a Excel
     */
    public function exportar()
    {
        $this->requirePermission('modulos');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'modulo_descripcion' => $this->get('modulo_descripcion'),
                'modulo_ruta' => $this->get('modulo_ruta'),
                'rela_menu' => $this->get('rela_menu'),
                'modulo_estado' => $this->get('modulo_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->moduloModel->getAllWithDetailsForExport($filters);
            $modulos = $result['data'];

            if (empty($modulos)) {
                $this->redirect('/modulos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Módulos');

            // Definir encabezados
            $headers = [
                'A1' => 'Descripción',
                'B1' => 'Ruta',
                'C1' => 'Menú',
                'D1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:D1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:D1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($modulos as $modulo) {
                // Mapear estado a texto
                $estadoTexto = $modulo['modulo_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $modulo['modulo_descripcion']);
                $worksheet->setCellValue('B' . $row, $modulo['modulo_ruta']);
                $worksheet->setCellValue('C' . $row, $modulo['menu_nombre'] ?? 'Sin menú');
                $worksheet->setCellValue('D' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(30);
            $worksheet->getColumnDimension('B')->setWidth(30);
            $worksheet->getColumnDimension('C')->setWidth(25);
            $worksheet->getColumnDimension('D')->setWidth(12);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "modulos_{$fecha}.xlsx";

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
            error_log("Error al exportar módulos: " . $e->getMessage());
            $this->redirect('/modulos', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar módulos a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('modulos');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'modulo_descripcion' => $this->get('modulo_descripcion'),
                'modulo_ruta' => $this->get('modulo_ruta'),
                'rela_menu' => $this->get('rela_menu'),
                'modulo_estado' => $this->get('modulo_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->moduloModel->getAllWithDetailsForExport($filters);
            $modulos = $result['data'];

            if (empty($modulos)) {
                $this->redirect('/modulos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Módulos');
            $pdf->SetSubject('Exportación de Módulos');
            $pdf->SetKeywords('módulos, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Módulos', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['modulo_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['modulo_descripcion'];
            }
            if (!empty($filters['modulo_ruta'])) {
                $filtrosTexto[] = 'Ruta: ' . $filters['modulo_ruta'];
            }
            if (isset($filters['modulo_estado']) && $filters['modulo_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['modulo_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($modulos);
            $pdf->Cell(0, 10, $infoFormato, 0, 1, 'L');
            $pdf->Ln(5);
            
            // Crear tabla HTML
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
                .ruta { width: 30%; }
                .menu { width: 25%; }
                .estado { text-align: center; width: 15%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="descripcion">Descripción</th>
                        <th class="ruta">Ruta</th>
                        <th class="menu">Menú</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($modulos as $modulo) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = $modulo['modulo_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $modulo['modulo_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($modulo['modulo_descripcion']) . '</td>
                    <td class="ruta">' . htmlspecialchars($modulo['modulo_ruta']) . '</td>
                    <td class="menu">' . htmlspecialchars($modulo['menu_nombre'] ?? 'Sin menú') . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "modulos_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar módulos a PDF: " . $e->getMessage());
            $this->redirect('/modulos', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}


<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Menu;

/**
 * Controlador para el manejo de menús
 */
class MenusController extends Controller
{
    protected $modelo;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new Menu();
    }

    /**
     * Listar menús
     */
    public function index()
    {
        $this->requirePermission('menus');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'menu_nombre' => $this->get('menu_nombre'),
            'menu_estado' => $this->get('menu_estado')
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Menús',
            'menus' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/menus/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo menú
     */
    public function create()
    {
        $this->requirePermission('menus');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Menú',
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/menus/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo menú
     */
    public function store()
    {
        $this->requirePermission('menus');

        $data = [
            'menu_nombre' => $this->post('menu_nombre'),
            'menu_orden' => $this->post('menu_orden'),
            'menu_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['menu_nombre'])) {
            $this->redirect('/menus/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            $id = $this->modelo->create($data);
            if ($id) {
                $this->redirect('/menus', 'Menú creado correctamente', 'exito');
            } else {
                $this->redirect('/menus/create', 'Error al crear el menú', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/menus/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar menú específico
     */
    public function show($id)
    {
        $this->requirePermission('menus');

        $menu = $this->modelo->find($id);
        if (!$menu) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del menú
        $estadisticas = $this->modelo->getStatistics($id);
        
        // Obtener módulos asociados
        $moduloModel = new \App\Models\Modulo();
        $modulos = $moduloModel->getByMenuId($id);

        $data = [
            'title' => 'Detalle de Menú',
            'menu' => $menu,
            'estadisticas' => $estadisticas,
            'modulos' => $modulos,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/menus/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('menus');

        $menu = $this->modelo->find($id);
        if (!$menu) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Editar Menú',
            'menu' => $menu,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/menus/formulario', $data, 'main');
    }

    /**
     * Actualizar menú
     */
    public function update($id)
    {
        $this->requirePermission('menus');

        $menu = $this->modelo->find($id);
        if (!$menu) {
            return $this->view->error(404);
        }

        $data = [
            'menu_nombre' => $this->post('menu_nombre'),
            'menu_orden' => $this->post('menu_orden')
        ];

        if (empty($data['menu_nombre'])) {
            $this->redirect("/menus/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            if ($this->modelo->update($id, $data)) {
                $this->redirect('/menus', 'Menú actualizado correctamente', 'exito');
            } else {
                $this->redirect("/menus/$id/edit", 'Error al actualizar el menú', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/menus/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de menú
     */
    public function delete($id)
    {
        $this->requirePermission('menus');

        $menu = $this->modelo->find($id);
        if (!$menu) {
            return $this->view->error(404);
        }

        if ($this->modelo->softDelete($id, 'menu_estado')) {
            $this->redirect('/menus', 'Menú eliminado correctamente', 'exito');
        } else {
            $this->redirect('/menus', 'Error al eliminar el menú', 'error');
        }
    }

    /**
     * Restaurar menú
     */
    public function restore($id)
    {
        $this->requirePermission('menus');

        if ($this->modelo->restore($id, 'menu_estado')) {
            $this->redirect('/menus', 'Menú restaurado correctamente', 'exito');
        } else {
            $this->redirect('/menus', 'Error al restaurar el menú', 'error');
        }
    }

    /**
     * Cambiar estado de menú (AJAX)
     */
    public function cambiarEstado($id)
    {
        // Log para debugging
        error_log("Petición recibida en cambiarEstado - ID: $id");
        error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("URL completa: " . $_SERVER['REQUEST_URI']);
        
        $this->requirePermission('menus');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            error_log("Error: No es una petición AJAX");
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el menú existe
        $menu = $this->modelo->find($id);
        if (!$menu) {
            error_log("Error: Menú no encontrado - ID: $id");
            return $this->json(['success' => false, 'message' => 'Menú no encontrado'], 404);
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
        $data = ['menu_estado' => $nuevoEstado];
        $resultado = $this->modelo->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactivo', 'activo'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizado';
            error_log("Estado cambiado exitosamente - ID: $id, Nuevo estado: $nuevoEstado");
            return $this->json([
                'success' => true, 
                'message' => "Menú marcado como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            error_log("Error al actualizar el estado en la base de datos - ID: $id");
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del menú'
            ], 500);
        }
    }

    /**
     * Exportar menús a Excel
     */
    public function exportar()
    {
        $this->requirePermission('menus');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'menu_nombre' => $this->get('menu_nombre'),
                'menu_estado' => $this->get('menu_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $menus = $result['data'];

            if (empty($menus)) {
                $this->redirect('/menus', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Menús');

            // Definir encabezados
            $headers = [
                'A1' => 'Nombre',
                'B1' => 'Orden',
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
            foreach ($menus as $menu) {
                // Mapear estado a texto
                $estadoTexto = $menu['menu_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $menu['menu_nombre']);
                $worksheet->setCellValue('B' . $row, $menu['menu_orden']);
                $worksheet->setCellValue('C' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(40);
            $worksheet->getColumnDimension('B')->setWidth(12);
            $worksheet->getColumnDimension('C')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "menus_{$fecha}.xlsx";

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
            error_log("Error al exportar menús: " . $e->getMessage());
            $this->redirect('/menus', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar menús a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('menus');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'menu_nombre' => $this->get('menu_nombre'),
                'menu_estado' => $this->get('menu_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $menus = $result['data'];

            if (empty($menus)) {
                $this->redirect('/menus', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Menús');
            $pdf->SetSubject('Exportación de Menús');
            $pdf->SetKeywords('menús, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Menús', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['menu_nombre'])) {
                $filtrosTexto[] = 'Nombre: ' . $filters['menu_nombre'];
            }
            if (isset($filters['menu_estado']) && $filters['menu_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['menu_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }
            
            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($menus) . ' | Formato: A4 Vertical';
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
                .nombre { width: 60%; }
                .orden { text-align: center; width: 20%; }
                .estado { text-align: center; width: 20%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="nombre">Nombre</th>
                        <th class="orden">Orden</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($menus as $menu) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = $menu['menu_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $menu['menu_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="nombre">' . htmlspecialchars($menu['menu_nombre']) . '</td>
                    <td class="orden">' . $menu['menu_orden'] . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "menus_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar menús a PDF: " . $e->getMessage());
            $this->redirect('/menus', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}


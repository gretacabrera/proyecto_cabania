<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CostoDanio;
use App\Models\Inventario;
use App\Models\NivelDanio;

/**
 * Controlador para el manejo de costos por daño
 */
class CostosDanioController extends Controller
{
    protected $costoDanioModel;
    protected $inventarioModel;
    protected $nivelDanioModel;

    public function __construct()
    {
        parent::__construct();
        $this->costoDanioModel = new CostoDanio();
        $this->inventarioModel = new Inventario();
        $this->nivelDanioModel = new NivelDanio();
    }

    /**
     * Listar costos por daño
     */
    public function index()
    {
        $this->requirePermission('costodanio');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'inventario' => $this->get('inventario'),
            'niveldanio' => $this->get('niveldanio'),
            'importe_min' => $this->get('importe_min'),
            'importe_max' => $this->get('importe_max'),
            'estado' => $this->get('estado')
        ];

        $result = $this->costoDanioModel->getWithDetails($page, $perPage, $filters);

        // Obtener listas para filtros
        $inventarios = $this->inventarioModel->getActive();
        $nivelesDanio = $this->nivelDanioModel->getActive();

        $data = [
            'title' => 'Gestión de Costos por Daño',
            'costos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'inventarios' => $inventarios,
            'nivelesDanio' => $nivelesDanio,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/costodanio/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo costo por daño
     */
    public function create()
    {
        $this->requirePermission('costodanio');

        if ($this->isPost()) {
            return $this->store();
        }

        // Obtener listas para selects
        $inventarios = $this->inventarioModel->getActive();
        $nivelesDanio = $this->nivelDanioModel->getActive();

        $data = [
            'title' => 'Nuevo Costo por Daño',
            'inventarios' => $inventarios,
            'nivelesDanio' => $nivelesDanio,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/costodanio/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo costo por daño
     */
    public function store()
    {
        $this->requirePermission('costodanio');

        $data = [
            'rela_inventario' => $this->post('rela_inventario'),
            'rela_niveldanio' => $this->post('rela_niveldanio'),
            'costodanio_importe' => $this->post('costodanio_importe'),
            'costodanio_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['rela_inventario']) || empty($data['rela_niveldanio']) || empty($data['costodanio_importe'])) {
            $this->redirect('/costodanio/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        // Validar que el importe sea un número positivo
        if ($data['costodanio_importe'] <= 0) {
            $this->redirect('/costodanio/create', 'El importe debe ser mayor a 0', 'error');
            return;
        }

        try {
            $id = $this->costoDanioModel->create($data);
            if ($id) {
                $this->redirect('/costodanio', 'Costo por daño creado correctamente', 'exito');
            } else {
                $this->redirect('/costodanio/create', 'Error al crear el costo por daño', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/costodanio/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar costo por daño específico
     */
    public function show($id)
    {
        $this->requirePermission('costodanio');

        $costo = $this->costoDanioModel->findWithDetails($id);
        if (!$costo) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del costo
        $estadisticas = $this->costoDanioModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Costo por Daño',
            'costo' => $costo,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/costodanio/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('costodanio');

        $costo = $this->costoDanioModel->find($id);
        if (!$costo) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener listas para selects
        $inventarios = $this->inventarioModel->getActive();
        $nivelesDanio = $this->nivelDanioModel->getActive();

        // Obtener estadísticas del costo
        $estadisticas = $this->costoDanioModel->getStatistics($id);

        $data = [
            'title' => 'Editar Costo por Daño',
            'costo' => $costo,
            'inventarios' => $inventarios,
            'nivelesDanio' => $nivelesDanio,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/costodanio/formulario', $data, 'main');
    }

    /**
     * Actualizar costo por daño
     */
    public function update($id)
    {
        $this->requirePermission('costodanio');

        $costo = $this->costoDanioModel->find($id);
        if (!$costo) {
            return $this->view->error(404);
        }

        $data = [
            'rela_inventario' => $this->post('rela_inventario'),
            'rela_niveldanio' => $this->post('rela_niveldanio'),
            'costodanio_importe' => $this->post('costodanio_importe')
        ];

        // Validaciones básicas
        if (empty($data['rela_inventario']) || empty($data['rela_niveldanio']) || empty($data['costodanio_importe'])) {
            $this->redirect("/costodanio/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        // Validar que el importe sea un número positivo
        if ($data['costodanio_importe'] <= 0) {
            $this->redirect("/costodanio/$id/edit", 'El importe debe ser mayor a 0', 'error');
            return;
        }

        try {
            if ($this->costoDanioModel->update($id, $data)) {
                $this->redirect('/costodanio', 'Costo por daño actualizado correctamente', 'exito');
            } else {
                $this->redirect("/costodanio/$id/edit", 'Error al actualizar el costo por daño', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/costodanio/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de costo por daño
     */
    public function delete($id)
    {
        $this->requirePermission('costodanio');

        $costo = $this->costoDanioModel->find($id);
        if (!$costo) {
            return $this->view->error(404);
        }

        if ($this->costoDanioModel->softDelete($id, 'costodanio_estado')) {
            $this->redirect('/costodanio', 'Costo por daño eliminado correctamente', 'exito');
        } else {
            $this->redirect('/costodanio', 'Error al eliminar el costo por daño', 'error');
        }
    }

    /**
     * Restaurar costo por daño
     */
    public function restore($id)
    {
        $this->requirePermission('costodanio');

        if ($this->costoDanioModel->restore($id, 'costodanio_estado')) {
            $this->redirect('/costodanio', 'Costo por daño restaurado correctamente', 'exito');
        } else {
            $this->redirect('/costodanio', 'Error al restaurar el costo por daño', 'error');
        }
    }

    /**
     * Cambiar estado de costo por daño (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('costodanio');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el costo existe
        $costo = $this->costoDanioModel->find($id);
        if (!$costo) {
            return $this->json(['success' => false, 'message' => 'Costo por daño no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['costodanio_estado' => $nuevoEstado];
        $resultado = $this->costoDanioModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = $nuevoEstado == 1 ? 'activo' : 'inactivo';
            return $this->json([
                'success' => true, 
                'message' => "Costo por daño marcado como {$estadoTexto} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del costo por daño'
            ], 500);
        }
    }

    /**
     * Exportar costos por daño a Excel
     */
    public function exportar()
    {
        $this->requirePermission('costodanio');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'inventario' => $this->get('inventario'),
                'niveldanio' => $this->get('niveldanio'),
                'importe_min' => $this->get('importe_min'),
                'importe_max' => $this->get('importe_max'),
                'estado' => $this->get('estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->costoDanioModel->getAllWithDetailsForExport($filters);
            $costos = $result['data'];

            if (empty($costos)) {
                $this->redirect('/costodanio', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Costos por Daño');

            // Definir encabezados
            $headers = [
                'A1' => 'Inventario',
                'B1' => 'Nivel de Daño',
                'C1' => 'Importe',
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
            foreach ($costos as $costo) {
                $estadoTexto = $costo['costodanio_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $costo['inventario_descripcion']);
                $worksheet->setCellValue('B' . $row, $costo['niveldanio_descripcion']);
                $worksheet->setCellValue('C' . $row, number_format($costo['costodanio_importe'], 2));
                $worksheet->setCellValue('D' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(35);
            $worksheet->getColumnDimension('B')->setWidth(25);
            $worksheet->getColumnDimension('C')->setWidth(15);
            $worksheet->getColumnDimension('D')->setWidth(12);

            // Aplicar formato a la columna de importe
            $worksheet->getStyle('C2:C' . ($row - 1))->getNumberFormat()->setFormatCode('$#,##0.00');

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "costos_danio_{$fecha}.xlsx";

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
            error_log("Error al exportar costos por daño: " . $e->getMessage());
            $this->redirect('/costodanio', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar costos por daño a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('costodanio');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'inventario' => $this->get('inventario'),
                'niveldanio' => $this->get('niveldanio'),
                'importe_min' => $this->get('importe_min'),
                'importe_max' => $this->get('importe_max'),
                'estado' => $this->get('estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->costoDanioModel->getAllWithDetailsForExport($filters);
            $costos = $result['data'];

            if (empty($costos)) {
                $this->redirect('/costodanio', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Costos por Daño');
            $pdf->SetSubject('Exportación de Costos por Daño');
            $pdf->SetKeywords('costos, daño, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Costos por Daño', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['inventario'])) {
                $filtrosTexto[] = 'Inventario: ' . $filters['inventario'];
            }
            if (!empty($filters['niveldanio'])) {
                $filtrosTexto[] = 'Nivel de Daño: ' . $filters['niveldanio'];
            }
            if (!empty($filters['importe_min'])) {
                $filtrosTexto[] = 'Importe mín.: $' . number_format($filters['importe_min'], 2);
            }
            if (!empty($filters['importe_max'])) {
                $filtrosTexto[] = 'Importe máx.: $' . number_format($filters['importe_max'], 2);
            }
            if (isset($filters['estado']) && $filters['estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($costos) . ' | Formato: A4 Vertical';
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
                    padding: 3px; 
                    font-size: 8px; 
                    vertical-align: top;
                    word-wrap: break-word;
                    overflow: hidden;
                }
                .inventario { width: 40%; }
                .niveldanio { width: 30%; text-align: center; }
                .importe { text-align: right; width: 20%; }
                .estado { text-align: center; width: 10%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="inventario">Inventario</th>
                        <th class="niveldanio">Nivel de Daño</th>
                        <th class="importe">Importe</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($costos as $costo) {
                $estadoTexto = $costo['costodanio_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $costo['costodanio_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="inventario">' . htmlspecialchars($costo['inventario_descripcion']) . '</td>
                    <td class="niveldanio">' . htmlspecialchars($costo['niveldanio_descripcion']) . '</td>
                    <td class="importe">$' . number_format($costo['costodanio_importe'], 2, '.', ',') . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "costos_danio_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar costos por daño a PDF: " . $e->getMessage());
            $this->redirect('/costodanio', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

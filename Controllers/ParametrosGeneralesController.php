<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ParametroGeneral;

/**
 * Controlador para el manejo de parámetros generales del sistema
 */
class ParametrosGeneralesController extends Controller
{
    protected $modelo;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new ParametroGeneral();
    }

    /**
     * Listar parámetros generales
     */
    public function index()
    {
        $this->requirePermission('parametrosgenerales');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'parametrogeneral_codigo' => $this->get('parametrogeneral_codigo'),
            'parametrogeneral_descripcion' => $this->get('parametrogeneral_descripcion'),
            'parametrogeneral_estado' => $this->get('parametrogeneral_estado')
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Parámetros Generales',
            'parametros' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/parametrosgenerales/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo parámetro
     */
    public function create()
    {
        $this->requirePermission('parametrosgenerales');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Parámetro General',
            'parametro' => [],
            'isEdit' => false,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/parametrosgenerales/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo parámetro
     */
    public function store()
    {
        $this->requirePermission('parametrosgenerales');

        $data = [
            'parametrogeneral_codigo' => strtoupper($this->post('parametrogeneral_codigo')),
            'parametrogeneral_descripcion' => $this->post('parametrogeneral_descripcion'),
            'parametrogeneral_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['parametrogeneral_codigo']) || empty($data['parametrogeneral_descripcion'])) {
            $this->redirect('/parametrosgenerales/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        // Validar que el código no exista
        if ($this->modelo->codigoExiste($data['parametrogeneral_codigo'])) {
            $this->redirect('/parametrosgenerales/create', 'El código ya existe en el sistema', 'error');
            return;
        }

        try {
            $id = $this->modelo->create($data);
            if ($id) {
                $this->redirect('/parametrosgenerales', 'Parámetro creado exitosamente', 'success');
            } else {
                $this->redirect('/parametrosgenerales/create', 'Error al crear el parámetro', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/parametrosgenerales/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar detalle de parámetro
     */
    public function show($id)
    {
        $this->requirePermission('parametrosgenerales');

        $parametro = $this->modelo->find($id);
        
        if (!$parametro) {
            $this->redirect('/parametrosgenerales', 'Parámetro no encontrado', 'error');
            return;
        }

        $data = [
            'title' => 'Detalle de Parámetro',
            'parametro' => $parametro,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/parametrosgenerales/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('parametrosgenerales');

        $parametro = $this->modelo->find($id);
        
        if (!$parametro) {
            $this->redirect('/parametrosgenerales', 'Parámetro no encontrado', 'error');
            return;
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $data = [
            'title' => 'Editar Parámetro General',
            'parametro' => $parametro,
            'isEdit' => true,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/parametrosgenerales/formulario', $data, 'main');
    }

    /**
     * Actualizar parámetro
     */
    public function update($id)
    {
        $this->requirePermission('parametrosgenerales');

        $parametro = $this->modelo->find($id);
        
        if (!$parametro) {
            $this->redirect('/parametrosgenerales', 'Parámetro no encontrado', 'error');
            return;
        }

        $data = [
            'parametrogeneral_codigo' => strtoupper($this->post('parametrogeneral_codigo')),
            'parametrogeneral_descripcion' => $this->post('parametrogeneral_descripcion')
        ];

        // Validaciones básicas
        if (empty($data['parametrogeneral_codigo']) || empty($data['parametrogeneral_descripcion'])) {
            $this->redirect('/parametrosgenerales/' . $id . '/edit', 'Complete los campos obligatorios', 'error');
            return;
        }

        // Validar que el código no exista (excepto el actual)
        if ($this->modelo->codigoExiste($data['parametrogeneral_codigo'], $id)) {
            $this->redirect('/parametrosgenerales/' . $id . '/edit', 'El código ya existe en el sistema', 'error');
            return;
        }

        try {
            $success = $this->modelo->update($id, $data);
            if ($success) {
                $this->redirect('/parametrosgenerales/' . $id, 'Parámetro actualizado exitosamente', 'success');
            } else {
                $this->redirect('/parametrosgenerales/' . $id . '/edit', 'Error al actualizar el parámetro', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/parametrosgenerales/' . $id . '/edit', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar parámetro (baja lógica)
     */
    public function delete($id)
    {
        $this->requirePermission('parametrosgenerales');

        $parametro = $this->modelo->find($id);
        
        if (!$parametro) {
            return $this->json(['success' => false, 'message' => 'Parámetro no encontrado'], 404);
        }

        try {
            $success = $this->modelo->update($id, ['parametrogeneral_estado' => 0]);
            
            if ($success) {
                return $this->json(['success' => true, 'message' => 'Parámetro eliminado exitosamente']);
            } else {
                return $this->json(['success' => false, 'message' => 'Error al eliminar el parámetro'], 500);
            }
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Restaurar parámetro
     */
    public function restore($id)
    {
        $this->requirePermission('parametrosgenerales');

        $parametro = $this->modelo->find($id);
        
        if (!$parametro) {
            return $this->json(['success' => false, 'message' => 'Parámetro no encontrado'], 404);
        }

        try {
            $success = $this->modelo->update($id, ['parametrogeneral_estado' => 1]);
            
            if ($success) {
                return $this->json(['success' => true, 'message' => 'Parámetro restaurado exitosamente']);
            } else {
                return $this->json(['success' => false, 'message' => 'Error al restaurar el parámetro'], 500);
            }
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cambiar estado de parámetro
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('parametrosgenerales');

        $parametro = $this->modelo->find($id);
        
        if (!$parametro) {
            return $this->json(['success' => false, 'message' => 'Parámetro no encontrado'], 404);
        }

        $nuevoEstado = $parametro['parametrogeneral_estado'] == 1 ? 0 : 1;

        try {
            $success = $this->modelo->update($id, ['parametrogeneral_estado' => $nuevoEstado]);
            
            if ($success) {
                $mensaje = $nuevoEstado == 1 ? 'Parámetro activado exitosamente' : 'Parámetro desactivado exitosamente';
                return $this->json(['success' => true, 'message' => $mensaje, 'nuevo_estado' => $nuevoEstado]);
            } else {
                return $this->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
            }
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exportar a Excel
     */
    public function exportar()
    {
        $this->requirePermission('parametrosgenerales');

        $filters = [
            'parametrogeneral_codigo' => $this->get('parametrogeneral_codigo'),
            'parametrogeneral_descripcion' => $this->get('parametrogeneral_descripcion'),
            'parametrogeneral_estado' => $this->get('parametrogeneral_estado')
        ];

        $result = $this->modelo->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/parametros-generales', 'No hay datos para exportar', 'error');
            return;
        }

        try {
            require_once 'vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Título
            $sheet->setCellValue('A1', 'LISTADO DE PARÁMETROS GENERALES');
            $sheet->mergeCells('A1:C1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Encabezados
            $headers = ['Código', 'Descripción', 'Estado'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '3', $header);
                $sheet->getStyle($col . '3')->getFont()->setBold(true);
                $sheet->getStyle($col . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
                $col++;
            }
            
            // Datos
            $row = 4;
            foreach ($datos as $parametro) {
                $sheet->setCellValue('A' . $row, $parametro['parametrogeneral_codigo']);
                $sheet->setCellValue('B' . $row, $parametro['parametrogeneral_descripcion']);
                $sheet->setCellValue('C' . $row, $parametro['parametrogeneral_estado'] == 1 ? 'Activo' : 'Inactivo');
                $row++;
            }
            
            // Ajustar columnas
            foreach (range('A', 'C') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Descargar archivo
            $filename = 'parametrosgenerales_' . date('Ymd_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            $this->redirect('/parametrosgenerales', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('parametrosgenerales');

        $filters = [
            'parametrogeneral_codigo' => $this->get('parametrogeneral_codigo'),
            'parametrogeneral_descripcion' => $this->get('parametrogeneral_descripcion'),
            'parametrogeneral_estado' => $this->get('parametrogeneral_estado')
        ];

        $result = $this->modelo->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/parametros-generales', 'No hay datos para exportar', 'error');
            return;
        }

        try {
            require_once 'vendor/autoload.php';
            
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Gestión de Cabañas');
            $pdf->SetTitle('Listado de Parámetros Generales');
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            $pdf->AddPage();
            
            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'LISTADO DE PARÁMETROS GENERALES', 0, 1, 'C');
            $pdf->Ln(5);
            
            // Información de filtros aplicados
            if (!empty(array_filter($filters))) {
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 5, 'Filtros aplicados: ' . count(array_filter($filters)), 0, 1, 'L');
                $pdf->Ln(3);
            }
            
            // Tabla
            $pdf->SetFont('helvetica', '', 9);
            
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr style="background-color:#E0E0E0;font-weight:bold;">
                        <th width="20%">Código</th>
                        <th width="60%">Descripción</th>
                        <th width="20%">Estado</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($datos as $parametro) {
                $estado = $parametro['parametrogeneral_estado'] == 1 ? 'Activo' : 'Inactivo';
                
                $html .= '<tr>
                    <td width="20%">' . htmlspecialchars($parametro['parametrogeneral_codigo']) . '</td>
                    <td width="60%">' . htmlspecialchars($parametro['parametrogeneral_descripcion']) . '</td>
                    <td width="20%" align="center">' . $estado . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Información de total
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 5, 'Total de registros: ' . count($datos), 0, 1, 'R');
            
            $filename = 'parametrosgenerales_' . date('Ymd_His') . '.pdf';
            $pdf->Output($filename, 'D');
            exit;
            
        } catch (\Exception $e) {
            $this->redirect('/parametrosgenerales', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Perfil;

/**
 * Controlador para la gestión de perfiles de usuario
 */
class PerfilesController extends Controller
{
    protected $perfilModel;

    public function __construct()
    {
        parent::__construct();
        $this->perfilModel = new Perfil();
    }

    /**
     * Listar todos los perfiles
     */
    public function index()
    {
        $this->requirePermission('perfiles');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'perfil_descripcion' => $this->get('perfil_descripcion'),
            'perfil_estado' => $this->get('perfil_estado')
        ];

        $result = $this->perfilModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Perfiles',
            'perfiles' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/perfiles/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo perfil
     */
    public function create()
    {
        $this->requirePermission('perfiles');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Perfil',
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/perfiles/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo perfil
     */
    public function store()
    {
        $this->requirePermission('perfiles');

        $data = [
            'perfil_descripcion' => $this->post('perfil_descripcion'),
            'perfil_estado' => 1
        ];

        $perfilId = $this->perfilModel->create($data);
        
        if ($perfilId) {
            $this->redirect('/perfiles', 'Perfil creado exitosamente', 'success');
        } else {
            $this->redirect('/perfiles/create', 'Error al crear el perfil', 'error');
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('perfiles');

        if ($this->isPost()) {
            return $this->update($id);
        }

        $perfil = $this->perfilModel->find($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
            return;
        }

        // Obtener estadísticas del perfil
        $estadisticas = $this->perfilModel->getStatistics($id);

        $data = [
            'title' => 'Editar Perfil',
            'perfil' => $perfil,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/perfiles/formulario', $data, 'main');
    }

    /**
     * Actualizar perfil existente
     */
    public function update($id)
    {
        $this->requirePermission('perfiles');

        $perfil = $this->perfilModel->find($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
            return;
        }

        $data = [
            'perfil_descripcion' => $this->post('perfil_descripcion'),
            'perfil_estado' => $this->post('perfil_estado', 1)
        ];

        if ($this->perfilModel->update($id, $data)) {
            $this->redirect('/perfiles', 'Perfil actualizado exitosamente', 'success');
        } else {
            $this->redirect("/perfiles/{$id}/edit", 'Error al actualizar el perfil', 'error');
        }
    }

    /**
     * Ver detalle del perfil
     */
    public function show($id)
    {
        $this->requirePermission('perfiles');

        $perfil = $this->perfilModel->find($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
            return;
        }

        // Obtener estadísticas del perfil
        $estadisticas = $this->perfilModel->getStatistics($id);

        $data = [
            'title' => 'Detalle del Perfil',
            'perfil' => $perfil,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/perfiles/detalle', $data, 'main');
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        $this->requirePermission('perfiles');

        $perfil = $this->perfilModel->find($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
            return;
        }

        $data = ['perfil_estado' => 0];
        
        if ($this->perfilModel->update($id, $data)) {
            $this->redirect('/perfiles', 'Perfil eliminado exitosamente', 'success');
        } else {
            $this->redirect('/perfiles', 'Error al eliminar el perfil', 'error');
        }
    }

    /**
     * Restaurar perfil
     */
    public function restore($id)
    {
        $this->requirePermission('perfiles');

        $perfil = $this->perfilModel->find($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
            return;
        }

        $data = ['perfil_estado' => 1];
        
        if ($this->perfilModel->update($id, $data)) {
            $this->redirect('/perfiles', 'Perfil restaurado exitosamente', 'success');
        } else {
            $this->redirect('/perfiles', 'Error al restaurar el perfil', 'error');
        }
    }

    /**
     * Exportar perfiles a Excel
     */
    public function exportar()
    {
        $this->requirePermission('perfiles');

        try {
            $filters = [
                'perfil_descripcion' => $this->get('perfil_descripcion'),
                'perfil_estado' => $this->get('perfil_estado')
            ];

            $result = $this->perfilModel->getAllWithDetailsForExport($filters);
            $perfiles = $result['data'];

            if (empty($perfiles)) {
                $this->redirect('/perfiles', 'No hay datos para exportar', 'error');
                return;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Perfiles');

            $headers = [
                'A1' => 'Descripción',
                'B1' => 'Estado'
            ];

            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            $worksheet->getStyle('A1:B1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:B1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:B1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            $row = 2;
            foreach ($perfiles as $perfil) {
                $estadoTexto = $perfil['perfil_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $perfil['perfil_descripcion']);
                $worksheet->setCellValue('B' . $row, $estadoTexto);

                $row++;
            }

            $worksheet->getColumnDimension('A')->setWidth(40);
            $worksheet->getColumnDimension('B')->setWidth(15);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $fecha = date('Y-m-d');
            $nombreArchivo = "perfiles_{$fecha}.xlsx";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar perfiles: " . $e->getMessage());
            $this->redirect('/perfiles', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar perfiles a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('perfiles');

        try {
            $filters = [
                'perfil_descripcion' => $this->get('perfil_descripcion'),
                'perfil_estado' => $this->get('perfil_estado')
            ];

            $result = $this->perfilModel->getAllWithDetailsForExport($filters);
            $perfiles = $result['data'];

            if (empty($perfiles)) {
                $this->redirect('/perfiles', 'No hay datos para exportar', 'error');
                return;
            }

            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            $pdf->SetCreator('Sistema de Gestión de Cabañas');
            $pdf->SetAuthor('Sistema');
            $pdf->SetTitle('Lista de Perfiles');
            $pdf->SetSubject('Perfiles');

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);

            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Lista de Perfiles', 0, 1, 'C');
            $pdf->Ln(5);

            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 5, 'Fecha: ' . date('d/m/Y'), 0, 1, 'R');
            $pdf->Cell(0, 5, 'Total de registros: ' . count($perfiles), 0, 1, 'R');
            $pdf->Ln(5);

            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr style="background-color:#E3F2FD;">
                        <th width="80%"><b>Descripción</b></th>
                        <th width="20%"><b>Estado</b></th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($perfiles as $perfil) {
                $estadoTexto = $perfil['perfil_estado'] == 1 ? 'Activo' : 'Inactivo';
                $html .= '<tr>
                    <td width="80%">' . htmlspecialchars($perfil['perfil_descripcion']) . '</td>
                    <td width="20%">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $fecha = date('Y-m-d');
            $nombreArchivo = "perfiles_{$fecha}.pdf";

            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar PDF de perfiles: " . $e->getMessage());
            $this->redirect('/perfiles', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Clonar perfil
     */
    public function clone($id)
    {
        $this->requirePermission('perfiles');

        $perfil = $this->perfilModel->find($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
            return;
        }

        if ($this->isPost()) {
            $newName = $this->post('perfil_descripcion');
            
            if (empty($newName)) {
                $this->redirect("/perfiles/{$id}/clone", 'Debe especificar un nombre', 'error');
                return;
            }

            $newPerfilId = $this->perfilModel->clonePerfil($id, $newName);
            
            if ($newPerfilId) {
                $this->redirect('/perfiles', 'Perfil clonado exitosamente', 'success');
            } else {
                $this->redirect("/perfiles/{$id}/clone", 'Error al clonar el perfil', 'error');
            }
            return;
        }

        $data = [
            'title' => 'Clonar Perfil',
            'perfil' => $perfil,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/perfiles/clonar', $data, 'main');
    }
}

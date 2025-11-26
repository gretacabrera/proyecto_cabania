<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Proveedor;
use App\Models\Persona;
use App\Models\PersonaJuridica;
use App\Models\Contacto;

/**
 * Controlador para el manejo de proveedores
 */
class ProveedoresController extends Controller
{
    protected $modelo;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new Proveedor();
    }

    /**
     * Listar proveedores
     */
    public function index()
    {
        $this->requirePermission('proveedores');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'denominacion' => $this->get('denominacion'),
            'cuit' => $this->get('cuit'),
            'estado' => $this->get('estado')
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Proveedores',
            'proveedores' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/proveedores/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo proveedor
     */
    public function create()
    {
        $this->requirePermission('proveedores');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Proveedor',
            'proveedor' => [],
            'isEdit' => false,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/proveedores/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo proveedor
     */
    public function store()
    {
        $this->requirePermission('proveedores');

        // Datos del formulario
        $denominacion = $this->post('persona_denominacion');
        $cuit = $this->post('personajuridica_cuit');
        $direccion = $this->post('persona_direccion');
        $correo = $this->post('contacto_correo');
        $telefono = $this->post('contacto_telefono');

        // Limpiar CUIT (remover guiones para guardar solo números)
        $cuit = preg_replace('/[^0-9]/', '', $cuit);

        // Validaciones básicas
        if (empty($denominacion) || empty($cuit) || empty($correo) || empty($telefono)) {
            $this->redirect('/proveedores/create', 'Complete todos los campos obligatorios', 'error');
            return;
        }

        try {
            $this->modelo->beginTransaction();

            // 1. Crear persona jurídica
            $personaJuridicaModel = new PersonaJuridica();
            $idPersonaJuridica = $personaJuridicaModel->create([
                'personajuridica_cuit' => $cuit
            ]);

            if (!$idPersonaJuridica) {
                throw new \Exception('Error al crear persona jurídica');
            }

            // 2. Crear persona
            $personaModel = new Persona();
            $idPersona = $personaModel->create([
                'persona_denominacion' => $denominacion,
                'rela_personafisica' => null,
                'rela_personajuridica' => $idPersonaJuridica,
                'persona_direccion' => $direccion,
                'rela_estadopersona' => 1
            ]);

            if (!$idPersona) {
                throw new \Exception('Error al crear persona');
            }

            // 3. Crear contactos
            $contactoModel = new Contacto();
            
            // Email
            $idContactoCorreo = $contactoModel->create([
                'contacto_descripcion' => $correo,
                'rela_persona' => $idPersona,
                'rela_tipocontacto' => 1,
                'contacto_estado' => 1
            ]);

            if (!$idContactoCorreo) {
                throw new \Exception('Error al crear contacto de correo');
            }

            // Teléfono
            $idContactoTelefono = $contactoModel->create([
                'contacto_descripcion' => $telefono,
                'rela_persona' => $idPersona,
                'rela_tipocontacto' => 2,
                'contacto_estado' => 1
            ]);

            if (!$idContactoTelefono) {
                throw new \Exception('Error al crear contacto de teléfono');
            }

            // 4. Crear proveedor
            $idProveedor = $this->modelo->create([
                'rela_persona' => $idPersona,
                'proveedor_estado' => 1
            ]);

            if (!$idProveedor) {
                throw new \Exception('Error al crear proveedor');
            }

            $this->modelo->commit();
            $this->redirect('/proveedores', 'Proveedor creado correctamente', 'exito');
        } catch (\Exception $e) {
            $this->modelo->rollback();
            $this->redirect('/proveedores/create', 'Error al crear proveedor: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar proveedor específico
     */
    public function show($id)
    {
        $this->requirePermission('proveedores');

        $proveedor = $this->modelo->getProveedorCompleto($id);
        if (!$proveedor) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del proveedor
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Detalle de Proveedor',
            'proveedor' => $proveedor,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/proveedores/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('proveedores');

        $proveedor = $this->modelo->getProveedorCompleto($id);
        if (!$proveedor) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas del proveedor
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Editar Proveedor',
            'proveedor' => $proveedor,
            'estadisticas' => $estadisticas,
            'isEdit' => true,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/proveedores/formulario', $data, 'main');
    }

    /**
     * Actualizar proveedor
     */
    public function update($id)
    {
        $this->requirePermission('proveedores');

        $proveedor = $this->modelo->getProveedorCompleto($id);
        if (!$proveedor) {
            return $this->view->error(404);
        }

        // Datos del formulario
        $denominacion = $this->post('persona_denominacion');
        $cuit = $this->post('personajuridica_cuit');
        $direccion = $this->post('persona_direccion');
        $correo = $this->post('contacto_correo');
        $telefono = $this->post('contacto_telefono');

        // Limpiar CUIT (remover guiones para guardar solo números)
        $cuit = preg_replace('/[^0-9]/', '', $cuit);

        // Validaciones básicas
        if (empty($denominacion) || empty($cuit) || empty($correo) || empty($telefono)) {
            $this->redirect('/proveedores/' . $id . '/edit', 'Complete todos los campos obligatorios', 'error');
            return;
        }

        try {
            $this->modelo->beginTransaction();

            // 1. Actualizar persona jurídica
            $personaJuridicaModel = new PersonaJuridica();
            $personaJuridicaModel->update($proveedor['id_personajuridica'], [
                'personajuridica_cuit' => $cuit
            ]);

            // 2. Actualizar persona
            $personaModel = new Persona();
            $personaModel->update($proveedor['id_persona'], [
                'persona_denominacion' => $denominacion,
                'persona_direccion' => $direccion
            ]);

            // 3. Actualizar contactos
            $contactoModel = new Contacto();
            
            if (!empty($proveedor['id_contacto_correo'])) {
                $contactoModel->update($proveedor['id_contacto_correo'], [
                    'contacto_descripcion' => $correo
                ]);
            }

            if (!empty($proveedor['id_contacto_telefono'])) {
                $contactoModel->update($proveedor['id_contacto_telefono'], [
                    'contacto_descripcion' => $telefono
                ]);
            }

            $this->modelo->commit();
            $this->redirect('/proveedores/' . $id, 'Proveedor actualizado correctamente', 'exito');
        } catch (\Exception $e) {
            $this->modelo->rollback();
            $this->redirect('/proveedores/' . $id . '/edit', 'Error al actualizar proveedor: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar proveedor (baja lógica)
     */
    public function delete($id)
    {
        $this->requirePermission('proveedores');

        if (!$this->isPost()) {
            return $this->view->error(405);
        }

        $proveedor = $this->modelo->find($id);
        if (!$proveedor) {
            return $this->json(['success' => false, 'message' => 'Proveedor no encontrado']);
        }

        $result = $this->modelo->update($id, ['proveedor_estado' => 0]);
        
        if ($result) {
            $this->redirect('/proveedores', 'Proveedor eliminado correctamente', 'exito');
        } else {
            $this->redirect('/proveedores', 'Error al eliminar el proveedor', 'error');
        }
    }

    /**
     * Restaurar proveedor
     */
    public function restore($id)
    {
        $this->requirePermission('proveedores');

        if (!$this->isPost()) {
            return $this->view->error(405);
        }

        $proveedor = $this->modelo->find($id);
        if (!$proveedor) {
            return $this->json(['success' => false, 'message' => 'Proveedor no encontrado']);
        }

        $result = $this->modelo->update($id, ['proveedor_estado' => 1]);
        
        if ($result) {
            $this->redirect('/proveedores', 'Proveedor restaurado correctamente', 'exito');
        } else {
            $this->redirect('/proveedores', 'Error al restaurar el proveedor', 'error');
        }
    }

    /**
     * Cambiar estado del proveedor (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('proveedores');

        if (!$this->isPost()) {
            return $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $proveedor = $this->modelo->find($id);
        if (!$proveedor) {
            return $this->json(['success' => false, 'message' => 'Proveedor no encontrado']);
        }

        $nuevoEstado = $proveedor['proveedor_estado'] == 1 ? 0 : 1;
        $result = $this->modelo->update($id, ['proveedor_estado' => $nuevoEstado]);

        if ($result) {
            return $this->json([
                'success' => true, 
                'message' => 'Estado actualizado correctamente',
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json(['success' => false, 'message' => 'Error al actualizar el estado']);
        }
    }

    /**
     * Exportar a Excel
     */
    public function exportar()
    {
        $this->requirePermission('proveedores');

        $filters = [
            'persona_denominacion' => $this->get('persona_denominacion'),
            'personajuridica_cuit' => $this->get('personajuridica_cuit'),
            'proveedor_estado' => $this->get('proveedor_estado')
        ];

        $result = $this->modelo->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/proveedores', 'No hay datos para exportar', 'error');
            return;
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $headers = ['Denominación', 'CUIT', 'Dirección', 'Correo', 'Teléfono', 'Estado'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $col++;
            }

            // Datos
            $row = 2;
            foreach ($datos as $proveedor) {
                $sheet->setCellValue('A' . $row, $proveedor['persona_denominacion']);
                $sheet->setCellValue('B' . $row, $proveedor['personajuridica_cuit']);
                $sheet->setCellValue('C' . $row, $proveedor['persona_direccion']);
                $sheet->setCellValue('D' . $row, $proveedor['contacto_correo']);
                $sheet->setCellValue('E' . $row, $proveedor['contacto_telefono']);
                $sheet->setCellValue('F' . $row, $proveedor['proveedor_estado'] == 1 ? 'Activo' : 'Inactivo');
                $row++;
            }

            // Ajustar ancho de columnas
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Descargar archivo
            $filename = 'proveedores_' . date('Y-m-d_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            $this->redirect('/proveedores', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('proveedores');

        $filters = [
            'persona_denominacion' => $this->get('persona_denominacion'),
            'personajuridica_cuit' => $this->get('personajuridica_cuit'),
            'proveedor_estado' => $this->get('proveedor_estado')
        ];

        $result = $this->modelo->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/proveedores', 'No hay datos para exportar', 'error');
            return;
        }

        try {
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            $pdf->SetCreator('Sistema de Cabañas');
            $pdf->SetAuthor('Sistema');
            $pdf->SetTitle('Listado de Proveedores');
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            $pdf->AddPage();
            
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Listado de Proveedores', 0, 1, 'C');
            $pdf->Ln(5);
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 5, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
            $pdf->Cell(0, 5, 'Total de registros: ' . $result['total'], 0, 1, 'R');
            $pdf->Ln(5);
            
            // Tabla
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <th width="20%">Denominación</th>
                        <th width="15%">CUIT</th>
                        <th width="20%">Dirección</th>
                        <th width="20%">Correo</th>
                        <th width="15%">Teléfono</th>
                        <th width="10%">Estado</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($datos as $proveedor) {
                $estado = $proveedor['proveedor_estado'] == 1 ? 'Activo' : 'Inactivo';
                $html .= '<tr>
                    <td>' . htmlspecialchars($proveedor['persona_denominacion']) . '</td>
                    <td>' . htmlspecialchars($proveedor['personajuridica_cuit']) . '</td>
                    <td>' . htmlspecialchars($proveedor['persona_direccion']) . '</td>
                    <td>' . htmlspecialchars($proveedor['contacto_correo']) . '</td>
                    <td>' . htmlspecialchars($proveedor['contacto_telefono']) . '</td>
                    <td>' . $estado . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            
            $pdf->writeHTML($html, true, false, true, false, '');
            
            $pdf->Output('proveedores_' . date('Y-m-d_His') . '.pdf', 'D');
            exit;
        } catch (\Exception $e) {
            $this->redirect('/proveedores', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

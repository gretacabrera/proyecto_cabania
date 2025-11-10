<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TipoContacto;

/**
 * Controlador para el manejo de tipos de contactos
 */
class TiposContactosController extends Controller
{
    protected $tipoContactoModel;

    public function __construct()
    {
        parent::__construct();
        $this->tipoContactoModel = new TipoContacto();
    }

    /**
     * Listar tipos de contactos
     */
    public function index()
    {
        $this->requirePermission('tiposcontactos');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'tipocontacto_descripcion' => $this->get('tipocontacto_descripcion'),
            'tipocontacto_estado' => $this->get('tipocontacto_estado')
        ];

        $result = $this->tipoContactoModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Tipos de Contactos',
            'tipos_contactos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/tiposcontactos/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo tipo de contacto
     */
    public function create()
    {
        $this->requirePermission('tiposcontactos');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Tipo de Contacto',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/tiposcontactos/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo tipo de contacto
     */
    public function store()
    {
        $this->requirePermission('tiposcontactos');

        $data = [
            'tipocontacto_descripcion' => $this->post('tipocontacto_descripcion'),
            'tipocontacto_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['tipocontacto_descripcion'])) {
            $this->redirect('/tipos-contactos/create', 'Complete los campos obligatorios', 'error');
        }

        try {
            $id = $this->tipoContactoModel->create($data);
            if ($id) {
                $this->redirect('/tipos-contactos', 'Tipo de contacto creado correctamente', 'exito');
            } else {
                $this->redirect('/tipos-contactos/create', 'Error al crear el tipo de contacto', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/tipos-contactos/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar tipo de contacto específico
     */
    public function show($id)
    {
        $this->requirePermission('tiposcontactos');

        $tipoContacto = $this->tipoContactoModel->find($id);
        if (!$tipoContacto) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del tipo de contacto
        $estadisticas = $this->tipoContactoModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Tipo de Contacto',
            'tipo_contacto' => $tipoContacto,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/tiposcontactos/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('tiposcontactos');

        $tipoContacto = $this->tipoContactoModel->find($id);
        if (!$tipoContacto) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas del tipo de contacto
        $estadisticas = $this->tipoContactoModel->getStatistics($id);

        $data = [
            'title' => 'Editar Tipo de Contacto',
            'tipo_contacto' => $tipoContacto,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/tiposcontactos/formulario', $data, 'main');
    }

    /**
     * Actualizar tipo de contacto
     */
    public function update($id)
    {
        $this->requirePermission('tiposcontactos');

        $tipoContacto = $this->tipoContactoModel->find($id);
        if (!$tipoContacto) {
            return $this->view->error(404);
        }

        $data = [
            'tipocontacto_descripcion' => $this->post('tipocontacto_descripcion')
        ];

        if (empty($data['tipocontacto_descripcion'])) {
            $this->redirect("/tipos-contactos/$id/edit", 'Complete los campos obligatorios', 'error');
        }

        try {
            if ($this->tipoContactoModel->update($id, $data)) {
                $this->redirect('/tipos-contactos', 'Tipo de contacto actualizado correctamente', 'exito');
            } else {
                $this->redirect("/tipos-contactos/$id/edit", 'Error al actualizar el tipo de contacto', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/tipos-contactos/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de tipo de contacto
     */
    public function delete($id)
    {
        $this->requirePermission('tiposcontactos');

        $tipoContacto = $this->tipoContactoModel->find($id);
        if (!$tipoContacto) {
            return $this->view->error(404);
        }

        if ($this->tipoContactoModel->softDelete($id, 'tipocontacto_estado')) {
            $this->redirect('/tipos-contactos', 'Tipo de contacto eliminado correctamente', 'exito');
        } else {
            $this->redirect('/tipos-contactos', 'Error al eliminar el tipo de contacto', 'error');
        }
    }

    /**
     * Restaurar tipo de contacto
     */
    public function restore($id)
    {
        $this->requirePermission('tiposcontactos');

        if ($this->tipoContactoModel->restore($id, 'tipocontacto_estado')) {
            $this->redirect('/tipos-contactos', 'Tipo de contacto restaurado correctamente', 'exito');
        } else {
            $this->redirect('/tipos-contactos', 'Error al restaurar el tipo de contacto', 'error');
        }
    }

    /**
     * Cambiar estado de tipo de contacto (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('tiposcontactos');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el tipo de contacto existe
        $tipoContacto = $this->tipoContactoModel->find($id);
        if (!$tipoContacto) {
            return $this->json(['success' => false, 'message' => 'Tipo de contacto no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['tipocontacto_estado' => $nuevoEstado];
        $resultado = $this->tipoContactoModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactivo', 'activo'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizado';
            return $this->json([
                'success' => true, 
                'message' => "Tipo de contacto marcado como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del tipo de contacto'
            ], 500);
        }
    }

    /**
     * Exportar tipos de contactos a Excel
     */
    public function exportar()
    {
        $this->requirePermission('tiposcontactos');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'tipocontacto_descripcion' => $this->get('tipocontacto_descripcion'),
                'tipocontacto_estado' => $this->get('tipocontacto_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->tipoContactoModel->getAllWithDetailsForExport($filters);
            $tiposContactos = $result['data'];

            if (empty($tiposContactos)) {
                $this->redirect('/tiposcontactos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Tipos de Contactos');

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
            foreach ($tiposContactos as $tipoContacto) {
                // Mapear estado a texto
                $estadoTexto = '';
                switch ($tipoContacto['tipocontacto_estado']) {
                    case 0:
                        $estadoTexto = 'Inactivo';
                        break;
                    case 1:
                        $estadoTexto = 'Activo';
                        break;
                    default:
                        $estadoTexto = 'Desconocido';
                }

                $worksheet->setCellValue('A' . $row, $tipoContacto['tipocontacto_descripcion']);
                $worksheet->setCellValue('B' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(40);
            $worksheet->getColumnDimension('B')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "tiposcontactos_{$fecha}.xlsx";

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
            error_log("Error al exportar tipos de contactos: " . $e->getMessage());
            $this->redirect('/tipos-contactos', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar tipos de contactos a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('tiposcontactos');

        try {
            // Obtener todos los filtros de la URL (mismos que se usan en index)
            $filters = [
                'tipocontacto_descripcion' => $this->get('tipocontacto_descripcion'),
                'tipocontacto_estado' => $this->get('tipocontacto_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->tipoContactoModel->getAllWithDetailsForExport($filters);
            $tiposContactos = $result['data'];

            if (empty($tiposContactos)) {
                $this->redirect('/tiposcontactos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Tipos de Contactos');
            $pdf->SetSubject('Exportación de Tipos de Contactos');
            $pdf->SetKeywords('tipos de contactos, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Tipos de Contactos', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['tipocontacto_descripcion'])) {
                $filtrosTexto[] = 'Descripción: ' . $filters['tipocontacto_descripcion'];
            }
            if (isset($filters['tipocontacto_estado']) && $filters['tipocontacto_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['tipocontacto_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($tiposContactos) . ' | Formato: A4 Vertical';
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
                    padding: 5px; 
                    text-align: center; 
                    font-weight: bold; 
                    font-size: 10px;
                    word-wrap: break-word;
                }
                td { 
                    border: 1px solid #666; 
                    padding: 4px; 
                    font-size: 9px; 
                    vertical-align: top;
                    word-wrap: break-word;
                    overflow: hidden;
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
            foreach ($tiposContactos as $tipoContacto) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = '';
                $estadoClase = '';
                switch ($tipoContacto['tipocontacto_estado']) {
                    case 0:
                        $estadoTexto = 'Inactivo';
                        $estadoClase = 'estado-inactivo';
                        break;
                    case 1:
                        $estadoTexto = 'Activo';
                        $estadoClase = 'estado-activo';
                        break;
                    default:
                        $estadoTexto = 'Desconocido';
                        $estadoClase = '';
                }

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($tipoContacto['tipocontacto_descripcion']) . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "tiposcontactos_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar tipos de contactos a PDF: " . $e->getMessage());
            $this->redirect('/tipos-contactos', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

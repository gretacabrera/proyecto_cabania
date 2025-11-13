<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Huesped;
use App\Models\Persona;

/**
 * Controlador para el manejo de huéspedes
 */
class HuespedesController extends Controller
{
    protected $huespedModel;
    protected $personaModel;

    public function __construct()
    {
        parent::__construct();
        $this->huespedModel = new Huesped();
        $this->personaModel = new Persona();
    }

    /**
     * Listar huéspedes
     */
    public function index()
    {
        $this->requirePermission('huespedes');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'persona_nombre' => $this->get('persona_nombre'),
            'persona_apellido' => $this->get('persona_apellido'),
            'huesped_ubicacion' => $this->get('huesped_ubicacion'),
            'huesped_estado' => $this->get('huesped_estado')
        ];

        $result = $this->huespedModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Huéspedes',
            'huespedes' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/huespedes/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo huésped
     */
    public function create()
    {
        $this->requirePermission('huespedes');

        if ($this->isPost()) {
            return $this->store();
        }

        // Obtener condiciones de salud activas
        $condicionSaludModel = new \App\Models\CondicionSalud();
        $condicionesSalud = $condicionSaludModel->findAll("condicionsalud_estado = 1", "condicionsalud_descripcion ASC");

        // Obtener reservas futuras (fecha fin mayor a la fecha/hora actual)
        $reservaModel = new \App\Models\Reserva();
        $reservas = $reservaModel->findAll("reserva_fhfin > NOW()", "reserva_fhfin ASC");

        $data = [
            'title' => 'Nuevo Huésped',
            'condicionesSalud' => $condicionesSalud,
            'reservas' => $reservas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/huespedes/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo huésped (persona + huésped + condiciones de salud + reserva en transacción)
     */
    public function store()
    {
        $this->requirePermission('huespedes');

        // Datos de persona
        $personaData = [
            'persona_nombre' => $this->post('persona_nombre'),
            'persona_apellido' => $this->post('persona_apellido'),
            'persona_fechanac' => $this->post('persona_fechanac'),
            'persona_direccion' => $this->post('persona_direccion'),
            'rela_estadopersona' => 1 // Estado activo por defecto
        ];

        // Condiciones de salud seleccionadas
        $condicionesSeleccionadas = $this->post('condiciones_salud', []);
        
        // Reserva opcional
        $idReserva = $this->post('rela_reserva');

        // Validaciones
        if (empty($personaData['persona_nombre'])) {
            $this->redirect('/huespedes/create', 'El nombre es obligatorio', 'error');
            return;
        }
        if (empty($personaData['persona_apellido'])) {
            $this->redirect('/huespedes/create', 'El apellido es obligatorio', 'error');
            return;
        }
        if (empty($personaData['persona_fechanac'])) {
            $this->redirect('/huespedes/create', 'La fecha de nacimiento es obligatoria', 'error');
            return;
        }
        if (empty($personaData['persona_direccion'])) {
            $this->redirect('/huespedes/create', 'La dirección es obligatoria', 'error');
            return;
        }

        try {
            // Iniciar transacción
            $this->huespedModel->beginTransaction();

            // 1. Crear persona
            $idPersona = $this->personaModel->create($personaData);
            if (!$idPersona) {
                throw new \Exception('Error al crear la persona');
            }

            // 2. Crear huésped (sin ubicación en creación)
            $huespedData = [
                'rela_persona' => $idPersona,
                'huesped_ubicacion' => null,
                'huesped_estado' => 1
            ];
            $idHuesped = $this->huespedModel->create($huespedData);
            if (!$idHuesped) {
                throw new \Exception('Error al crear el huésped');
            }

            // 3. Obtener TODAS las condiciones de salud activas
            $condicionSaludModel = new \App\Models\CondicionSalud();
            $todasCondiciones = $condicionSaludModel->findAll("condicionsalud_estado = 1");
            
            // 4. Guardar TODAS las condiciones con estado según selección
            if (!$this->huespedModel->saveCondicionesSalud($idHuesped, $todasCondiciones, $condicionesSeleccionadas)) {
                throw new \Exception('Error al asignar condiciones de salud');
            }

            // 5. Asociar reserva si fue seleccionada
            if (!empty($idReserva)) {
                if (!$this->huespedModel->asociarReserva($idHuesped, $idReserva)) {
                    throw new \Exception('Error al asociar la reserva');
                }
            }

            // Commit de la transacción
            $this->huespedModel->commit();

            $this->redirect('/huespedes', 'Huésped creado exitosamente', 'success');
        } catch (\Exception $e) {
            // Rollback en caso de error
            $this->huespedModel->rollback();
            $this->redirect('/huespedes/create', 'Error al crear el huésped: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar huésped específico
     */
    public function show($id)
    {
        $this->requirePermission('huespedes');

        $huesped = $this->huespedModel->findWithPersona($id);
        if (!$huesped) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del huésped
        $estadisticas = $this->huespedModel->getStatistics($id);

        // Obtener condiciones de salud del huésped
        $condicionesHuesped = $this->huespedModel->getCondicionesSalud($id);
        
        // Cargar todas las condiciones de salud para mostrar las que tiene
        $condicionSaludModel = new \App\Models\CondicionSalud();
        $todasCondiciones = $condicionSaludModel->findAll("condicionsalud_estado = 1", "condicionsalud_descripcion ASC");

        // Obtener reserva asociada (si tiene)
        $reservaAsociada = null;
        $reservaActualId = $this->huespedModel->getReservaAsociada($id);
        if ($reservaActualId) {
            $reservaModel = new \App\Models\Reserva();
            $reservaAsociada = $reservaModel->find($reservaActualId);
        }

        $data = [
            'title' => 'Detalle de Huésped',
            'huesped' => $huesped,
            'estadisticas' => $estadisticas,
            'condicionesHuesped' => $condicionesHuesped,
            'todasCondiciones' => $todasCondiciones,
            'reservaAsociada' => $reservaAsociada,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/huespedes/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('huespedes');

        $huesped = $this->huespedModel->findWithPersona($id);
        if (!$huesped) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas del huésped
        $estadisticas = $this->huespedModel->getStatistics($id);
        
        // Obtener todas las condiciones de salud activas
        $condicionSaludModel = new \App\Models\CondicionSalud();
        $condicionesSalud = $condicionSaludModel->findAll("condicionsalud_estado = 1", "condicionsalud_descripcion ASC");
        
        // Obtener condiciones de salud asignadas al huésped
        $condicionesHuesped = $this->huespedModel->getCondicionesSalud($id);

        // Obtener reservas futuras (fecha fin mayor a la fecha/hora actual)
        $reservaModel = new \App\Models\Reserva();
        $reservas = $reservaModel->findAll("reserva_fhfin > NOW()", "reserva_fhfin ASC");

        // Obtener reserva actual del huésped (si tiene alguna asociada)
        $reservaActualId = $this->huespedModel->getReservaAsociada($id);

        $data = [
            'title' => 'Editar Huésped',
            'huesped' => $huesped,
            'estadisticas' => $estadisticas,
            'condicionesSalud' => $condicionesSalud,
            'condicionesHuesped' => $condicionesHuesped,
            'reservas' => $reservas,
            'reservaActualId' => $reservaActualId,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/huespedes/formulario', $data, 'main');
    }

    /**
     * Actualizar huésped
     */
    public function update($id)
    {
        $this->requirePermission('huespedes');

        $huesped = $this->huespedModel->find($id);
        if (!$huesped) {
            return $this->view->error(404);
        }

        // Datos de la persona
        $personaData = [
            'persona_nombre' => $this->post('persona_nombre'),
            'persona_apellido' => $this->post('persona_apellido'),
            'persona_fechanac' => $this->post('persona_fechanac'),
            'persona_direccion' => $this->post('persona_direccion')
        ];

        // Datos del huésped (solo ubicación es editable)
        $huespedData = [
            'huesped_ubicacion' => $this->post('huesped_ubicacion')
        ];

        // Condiciones de salud seleccionadas
        $condicionesSeleccionadas = $this->post('condiciones_salud', []);

        // Validaciones
        if (empty($personaData['persona_nombre'])) {
            $this->redirect("/huespedes/$id/edit", 'El nombre es obligatorio', 'error');
            return;
        }
        if (empty($personaData['persona_apellido'])) {
            $this->redirect("/huespedes/$id/edit", 'El apellido es obligatorio', 'error');
            return;
        }
        if (empty($personaData['persona_fechanac'])) {
            $this->redirect("/huespedes/$id/edit", 'La fecha de nacimiento es obligatoria', 'error');
            return;
        }
        if (empty($personaData['persona_direccion'])) {
            $this->redirect("/huespedes/$id/edit", 'La dirección es obligatoria', 'error');
            return;
        }

        try {
            // Iniciar transacción
            $this->huespedModel->beginTransaction();

            // 1. Actualizar datos de la persona
            $idPersona = $huesped['rela_persona'];
            if (!$this->personaModel->update($idPersona, $personaData)) {
                throw new \Exception('Error al actualizar los datos de la persona');
            }

            // 2. Actualizar datos del huésped (ubicación)
            if (!$this->huespedModel->update($id, $huespedData)) {
                throw new \Exception('Error al actualizar el huésped');
            }

            // 3. Obtener TODAS las condiciones de salud activas
            $condicionSaludModel = new \App\Models\CondicionSalud();
            $todasCondiciones = $condicionSaludModel->findAll("condicionsalud_estado = 1");

            // 4. Actualizar condiciones de salud
            if (!$this->huespedModel->updateCondicionesSalud($id, $todasCondiciones, $condicionesSeleccionadas)) {
                throw new \Exception('Error al actualizar condiciones de salud');
            }

            // 5. Actualizar reserva asociada (opcional)
            $nuevaReservaId = $this->post('rela_reserva');
            if (!empty($nuevaReservaId)) {
                // Primero eliminar asociación anterior si existe
                $this->huespedModel->eliminarReservaAsociada($id);
                
                // Luego asociar la nueva reserva
                if (!$this->huespedModel->asociarReserva($id, $nuevaReservaId)) {
                    throw new \Exception('Error al asociar reserva');
                }
            } else {
                // Si no seleccionó reserva, eliminar asociación existente
                $this->huespedModel->eliminarReservaAsociada($id);
            }

            // Commit de la transacción
            $this->huespedModel->commit();

            $this->redirect('/huespedes', 'Huésped actualizado correctamente', 'exito');
        } catch (\Exception $e) {
            // Rollback en caso de error
            $this->huespedModel->rollback();
            $this->redirect("/huespedes/$id/edit", 'Error al actualizar el huésped: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de huésped
     */
    public function delete($id)
    {
        $this->requirePermission('huespedes');

        $huesped = $this->huespedModel->find($id);
        if (!$huesped) {
            return $this->view->error(404);
        }

        if ($this->huespedModel->softDelete($id, 'huesped_estado')) {
            $this->redirect('/huespedes', 'Huésped eliminado correctamente', 'exito');
        } else {
            $this->redirect('/huespedes', 'Error al eliminar el huésped', 'error');
        }
    }

    /**
     * Restaurar huésped
     */
    public function restore($id)
    {
        $this->requirePermission('huespedes');

        if ($this->huespedModel->restore($id, 'huesped_estado')) {
            $this->redirect('/huespedes', 'Huésped restaurado correctamente', 'exito');
        } else {
            $this->redirect('/huespedes', 'Error al restaurar el huésped', 'error');
        }
    }

    /**
     * Cambiar estado de huésped (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('huespedes');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el huésped existe
        $huesped = $this->huespedModel->find($id);
        if (!$huesped) {
            return $this->json(['success' => false, 'message' => 'Huésped no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['huesped_estado' => $nuevoEstado];
        $resultado = $this->huespedModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactivo', 'activo'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizado';
            return $this->json([
                'success' => true, 
                'message' => "Huésped marcado como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del huésped'
            ], 500);
        }
    }

    /**
     * Exportar huéspedes a Excel
     */
    public function exportar()
    {
        $this->requirePermission('huespedes');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'persona_nombre' => $this->get('persona_nombre'),
                'persona_apellido' => $this->get('persona_apellido'),
                'huesped_ubicacion' => $this->get('huesped_ubicacion'),
                'huesped_estado' => $this->get('huesped_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->huespedModel->getAllWithDetailsForExport($filters);
            $huespedes = $result['data'];

            if (empty($huespedes)) {
                $this->redirect('/huespedes', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Huéspedes');

            // Definir encabezados
            $headers = [
                'A1' => 'Nombre',
                'B1' => 'Apellido',
                'C1' => 'Fecha Nacimiento',
                'D1' => 'Dirección',
                'E1' => 'Ubicación',
                'F1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:F1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($huespedes as $huesped) {
                // Mapear estado a texto
                $estadoTexto = $huesped['huesped_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $huesped['persona_nombre']);
                $worksheet->setCellValue('B' . $row, $huesped['persona_apellido']);
                $worksheet->setCellValue('C' . $row, $huesped['persona_fechanac']);
                $worksheet->setCellValue('D' . $row, $huesped['persona_direccion']);
                $worksheet->setCellValue('E' . $row, $huesped['huesped_ubicacion'] ?? '');
                $worksheet->setCellValue('F' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(20);
            $worksheet->getColumnDimension('B')->setWidth(20);
            $worksheet->getColumnDimension('C')->setWidth(15);
            $worksheet->getColumnDimension('D')->setWidth(30);
            $worksheet->getColumnDimension('E')->setWidth(25);
            $worksheet->getColumnDimension('F')->setWidth(12);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "huespedes_{$fecha}.xlsx";

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
            error_log("Error al exportar huéspedes: " . $e->getMessage());
            $this->redirect('/huespedes', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar huéspedes a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('huespedes');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'persona_nombre' => $this->get('persona_nombre'),
                'persona_apellido' => $this->get('persona_apellido'),
                'huesped_ubicacion' => $this->get('huesped_ubicacion'),
                'huesped_estado' => $this->get('huesped_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->huespedModel->getAllWithDetailsForExport($filters);
            $huespedes = $result['data'];

            if (empty($huespedes)) {
                $this->redirect('/huespedes', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Huéspedes');
            $pdf->SetSubject('Exportación de Huéspedes');
            $pdf->SetKeywords('huéspedes, listado, exportación');

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
            $pdf->Cell(0, 15, 'Listado de Huéspedes', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['persona_nombre'])) {
                $filtrosTexto[] = 'Nombre: ' . $filters['persona_nombre'];
            }
            if (!empty($filters['persona_apellido'])) {
                $filtrosTexto[] = 'Apellido: ' . $filters['persona_apellido'];
            }
            if (!empty($filters['huesped_ubicacion'])) {
                $filtrosTexto[] = 'Ubicación: ' . $filters['huesped_ubicacion'];
            }
            if (isset($filters['huesped_estado']) && $filters['huesped_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['huesped_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($huespedes) . ' | Formato: A4 Vertical';
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
                .nombre { width: 30%; }
                .fecha { text-align: center; width: 15%; }
                .direccion { width: 30%; }
                .ubicacion { width: 15%; }
                .estado { text-align: center; width: 10%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="nombre">Nombre Completo</th>
                        <th class="fecha">F. Nacimiento</th>
                        <th class="direccion">Dirección</th>
                        <th class="ubicacion">Ubicación</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($huespedes as $huesped) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = $huesped['huesped_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $huesped['huesped_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';
                
                // Formato: Apellido, Nombre
                $nombreCompleto = htmlspecialchars($huesped['persona_apellido']) . ', ' . htmlspecialchars($huesped['persona_nombre']);

                $html .= '<tr>
                    <td class="nombre">' . $nombreCompleto . '</td>
                    <td class="fecha">' . date('d/m/Y', strtotime($huesped['persona_fechanac'])) . '</td>
                    <td class="direccion">' . htmlspecialchars($huesped['persona_direccion']) . '</td>
                    <td class="ubicacion">' . htmlspecialchars($huesped['huesped_ubicacion'] ?? '-') . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "huespedes_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar huéspedes a PDF: " . $e->getMessage());
            $this->redirect('/huespedes', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

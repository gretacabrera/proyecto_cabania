<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Revision;
use App\Models\Reserva;
use App\Models\Inventario;
use App\Models\NivelDanio;
use App\Models\CostoDanio;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use TCPDF;

/**
 * Controlador para el manejo de revisiones
 */
class RevisionesController extends Controller
{
    protected $modelo;
    protected $reservaModel;
    protected $inventarioModel;
    protected $nivelDanioModel;
    protected $costoDanioModel;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new Revision();
        $this->reservaModel = new Reserva();
        $this->inventarioModel = new Inventario();
        $this->nivelDanioModel = new NivelDanio();
        $this->costoDanioModel = new CostoDanio();
    }

    /**
     * Listar revisiones
     */
    public function index()
    {
        $this->requirePermission('revisiones');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'reserva_id' => $this->get('reserva_id'),
            'cabania_nombre' => $this->get('cabania_nombre'),
            'estado' => $this->get('estado')
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Revisiones',
            'revisiones' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/revisiones/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva revisión
     */
    public function create()
    {
        $this->requirePermission('revisiones');

        if ($this->isPost()) {
            return $this->store();
        }

        // Obtener reservas pendientes de revisión (estado = 8)
        $reservasPendientes = $this->reservaModel->findAll("rela_estadoreserva = 8", "reserva_fhinicio DESC");

        // Obtener niveles de daño activos
        $nivelesDanio = $this->nivelDanioModel->findAll("niveldanio_estado = 1", "niveldanio_descripcion ASC");

        $data = [
            'title' => 'Nueva Revisión',
            'reservas' => $reservasPendientes,
            'nivelesDanio' => $nivelesDanio,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/revisiones/formulario', $data, 'main');
    }

    /**
     * Guardar nuevas revisiones (múltiples elementos en una transacción)
     */
    public function store()
    {
        $this->requirePermission('revisiones');

        try {
            $idReserva = (int) $this->post('reserva_id');
            $inventarios = $this->post('inventarios', []); // Array de inventarios revisados

            if (empty($idReserva)) {
                $this->redirect('/revisiones/create', 'Debe seleccionar una reserva', 'error');
                return;
            }

            if (empty($inventarios)) {
                $this->redirect('/revisiones/create', 'Debe seleccionar al menos un elemento con daño', 'error');
                return;
            }

            // Validar que la reserva esté en estado "pendiente de revisión"
            $reserva = $this->reservaModel->find($idReserva);
            if (!$reserva || $reserva['rela_estadoreserva'] != 8) {
                $this->redirect('/revisiones/create', 'La reserva no está pendiente de revisión', 'error');
                return;
            }

            // Preparar array de revisiones para insertar
            $revisiones = [];
            foreach ($inventarios as $invCabaniaId => $nivelDanioId) {
                if ($nivelDanioId > 0) { // Solo incluir elementos con daño
                    // Obtener el inventario_cabania para obtener el inventario_id
                    $sql = "SELECT rela_inventario FROM inventario_cabania WHERE id_inventariocabania = ?";
                    $db = Database::getInstance();
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param('i', $invCabaniaId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $invCabania = $result->fetch_assoc();

                    if (!$invCabania) {
                        continue;
                    }

                    $inventarioId = $invCabania['rela_inventario'];

                    // Buscar el costo asociado al nivel de daño y al inventario
                    $costoDanio = $this->costoDanioModel->findWhere(
                        "rela_inventario = ? AND rela_niveldanio = ? AND costodanio_estado = 1",
                        [$inventarioId, $nivelDanioId]
                    );

                    $costo = $costoDanio ? $costoDanio['costodanio_importe'] : 0;

                    $revisiones[] = [
                        'inventariocabania_id' => $invCabaniaId,
                        'costo' => $costo
                    ];
                }
            }

            if (empty($revisiones)) {
                $this->redirect('/revisiones/create', 'No hay elementos con daño para registrar', 'error');
                return;
            }

            // Insertar revisiones en una transacción (incluye actualización de estado)
            $insertado = $this->modelo->insertMultiple($revisiones, $idReserva);

            if (!$insertado) {
                $this->redirect('/revisiones/create', 'Error al guardar las revisiones', 'error');
                return;
            }

            $this->redirect('/revisiones', 'Revisiones registradas exitosamente', 'success');
        } catch (\Exception $e) {
            $this->redirect('/revisiones/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar detalle de una revisión (agrupada por reserva)
     */
    public function show($id)
    {
        $this->requirePermission('revisiones');

        $idReserva = (int) $id;
        
        // Obtener la reserva
        $reserva = $this->reservaModel->find($idReserva);
        if (!$reserva) {
            $this->redirect('/revisiones', 'Reserva no encontrada', 'error');
            return;
        }

        // Obtener todas las revisiones de esta reserva
        $revisiones = $this->modelo->getByReserva($idReserva);

        // Obtener información de la cabaña
        $sql = "SELECT c.* FROM cabania c 
                INNER JOIN reserva r ON c.id_cabania = r.rela_cabania 
                WHERE r.id_reserva = ?";
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();
        $cabania = $result->fetch_assoc();

        // Calcular total de costos de revisión
        $totalCosto = $this->modelo->getTotalCostoByReserva($idReserva);

        $data = [
            'title' => 'Detalle de Revisión',
            'reserva' => $reserva,
            'revisiones' => $revisiones,
            'cabania' => $cabania,
            'totalCosto' => $totalCosto,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/revisiones/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('revisiones');

        $idReserva = (int) $id;

        if ($this->isPost()) {
            return $this->update($idReserva);
        }

        // Obtener la reserva
        $reserva = $this->reservaModel->find($idReserva);
        if (!$reserva) {
            $this->redirect('/revisiones', 'Reserva no encontrada', 'error');
            return;
        }

        // Obtener revisiones existentes
        $revisionesExistentes = $this->modelo->getByReserva($idReserva);

        // Obtener inventario de la cabaña
        $sql = "SELECT ic.id_inventariocabania, i.inventario_descripcion, i.id_inventario
                FROM inventario_cabania ic
                INNER JOIN inventario i ON ic.rela_inventario = i.id_inventario
                WHERE ic.rela_cabania = ? AND ic.inventariocabania_estado = 1
                ORDER BY i.inventario_descripcion ASC";
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $reserva['rela_cabania']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $inventarios = [];
        while ($row = $result->fetch_assoc()) {
            $inventarios[] = $row;
        }

        // Obtener niveles de daño activos
        $nivelesDanio = $this->nivelDanioModel->findAll("niveldanio_estado = 1", "niveldanio_descripcion ASC");

        $data = [
            'title' => 'Editar Revisión',
            'reserva' => $reserva,
            'revisiones' => $revisionesExistentes,
            'inventarios' => $inventarios,
            'nivelesDanio' => $nivelesDanio,
            'isEdit' => true,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/revisiones/formulario', $data, 'main');
    }

    /**
     * Actualizar revisiones
     */
    public function update($id)
    {
        $this->requirePermission('revisiones');

        try {
            $idReserva = (int) $id;
            $inventarios = $this->post('inventarios', []);

            if (empty($inventarios)) {
                $this->redirect('/revisiones/' . $idReserva . '/edit', 'No hay datos para actualizar', 'error');
                return;
            }

            $db = Database::getInstance();
            $db->beginTransaction();

            try {
                // Primero, anular todas las revisiones existentes
                $sqlAnular = "UPDATE revision SET revision_estado = 0 WHERE rela_reserva = ? AND revision_estado = 1";
                $stmt = $db->prepare($sqlAnular);
                $stmt->bind_param('i', $idReserva);
                $stmt->execute();
                
                $filasAnuladas = $stmt->affected_rows;
                error_log("Revisiones anuladas: " . $filasAnuladas);

                // Luego, insertar las nuevas revisiones (solo elementos con daño)
                $hayDanios = false;
                $revisionesInsertadas = 0;
                foreach ($inventarios as $invCabaniaId => $nivelDanioId) {
                    $nivelDanioId = (int) $nivelDanioId;
                    
                    if ($nivelDanioId > 0) {
                        $hayDanios = true;
                        
                        // Obtener el inventario_id
                        $sqlInv = "SELECT rela_inventario FROM inventario_cabania WHERE id_inventariocabania = ?";
                        $stmtInv = $db->prepare($sqlInv);
                        $stmtInv->bind_param('i', $invCabaniaId);
                        $stmtInv->execute();
                        $resultInv = $stmtInv->get_result();
                        $invData = $resultInv->fetch_assoc();

                        if (!$invData) {
                            continue;
                        }

                        $inventarioId = $invData['rela_inventario'];

                        // Obtener el costo del daño
                        $costoDanio = $this->costoDanioModel->findWhere(
                            "rela_inventario = ? AND rela_niveldanio = ? AND costodanio_estado = 1",
                            [$inventarioId, $nivelDanioId]
                        );

                        $costo = $costoDanio ? $costoDanio['costodanio_importe'] : 0;

                        // Insertar nueva revisión
                        $sqlInsert = "INSERT INTO revision (rela_reserva, rela_inventariocabania, revision_costo, revision_estado) 
                                     VALUES (?, ?, ?, 1)";
                        $stmtInsert = $db->prepare($sqlInsert);
                        $stmtInsert->bind_param('iid', $idReserva, $invCabaniaId, $costo);
                        $stmtInsert->execute();
                        $revisionesInsertadas++;
                    }
                }

                error_log("Nuevas revisiones insertadas: " . $revisionesInsertadas);

                if (!$hayDanios) {
                    $db->rollback();
                    $this->redirect('/revisiones/' . $idReserva . '/edit', 'Debe seleccionar al menos un elemento con daño', 'error');
                    return;
                }

                $db->commit();
                $this->redirect('/revisiones/' . $idReserva, 'Revisión actualizada exitosamente', 'success');
            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->redirect('/revisiones/' . $id . '/edit', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Anular todas las revisiones de una reserva
     */
    public function delete($id)
    {
        $this->requirePermission('revisiones');

        $idReserva = (int) $id;

        try {
            $this->modelo->anularByReserva($idReserva);
            
            // Actualizar estado de la reserva
            $this->modelo->actualizarEstadoReserva($idReserva);

            $this->redirect('/revisiones', 'Revisiones anuladas exitosamente', 'success');
        } catch (\Exception $e) {
            $this->redirect('/revisiones', 'Error al anular las revisiones', 'error');
        }
    }

    /**
     * Exportar a Excel
     */
    public function exportar()
    {
        $this->requirePermission('revisiones');

        $filters = [
            'reserva_id' => $this->get('reserva_id'),
            'cabania_nombre' => $this->get('cabania_nombre'),
            'estado' => $this->get('estado')
        ];

        $result = $this->modelo->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/revisiones', 'No hay datos para exportar', 'error');
            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título
        $sheet->setCellValue('A1', 'Reporte de Revisiones');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Encabezados
        $headers = ['ID Revisión', 'ID Reserva', 'Cabaña', 'Código', 'Elemento', 'Costo', 'Estado', 'Fecha Inicio'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $col++;
        }

        // Estilo de encabezados
        $sheet->getStyle('A3:H3')->getFont()->setBold(true);
        $sheet->getStyle('A3:H3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A3:H3')->getFont()->getColor()->setRGB('FFFFFF');

        // Datos
        $row = 4;
        foreach ($datos as $dato) {
            $sheet->setCellValue('A' . $row, $dato['id_revision']);
            $sheet->setCellValue('B' . $row, $dato['rela_reserva']);
            $sheet->setCellValue('C' . $row, $dato['cabania_nombre']);
            $sheet->setCellValue('D' . $row, $dato['cabania_codigo']);
            $sheet->setCellValue('E' . $row, $dato['inventario_descripcion']);
            $sheet->setCellValue('F' . $row, '$' . number_format($dato['revision_costo'], 2));
            $sheet->setCellValue('G' . $row, $dato['revision_estado'] == 1 ? 'Activo' : 'Anulado');
            $sheet->setCellValue('H' . $row, date('d/m/Y', strtotime($dato['reserva_fhinicio'])));
            $row++;
        }

        // Ajustar columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Bordes
        $sheet->getStyle('A3:H' . ($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $filename = 'revisiones_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('revisiones');

        $filters = [
            'reserva_id' => $this->get('reserva_id'),
            'cabania_nombre' => $this->get('cabania_nombre'),
            'estado' => $this->get('estado')
        ];

        $result = $this->modelo->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/revisiones', 'No hay datos para exportar', 'error');
            return;
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

        $pdf->SetCreator('Sistema de Cabañas');
        $pdf->SetAuthor('Sistema de Cabañas');
        $pdf->SetTitle('Reporte de Revisiones');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Reporte de Revisiones', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 10);
        $html = '<table border="1" cellpadding="4">
                    <thead>
                        <tr style="background-color: #4472C4; color: white;">
                            <th width="10%">ID</th>
                            <th width="15%">Reserva</th>
                            <th width="20%">Cabaña</th>
                            <th width="25%">Elemento</th>
                            <th width="15%">Costo</th>
                            <th width="15%">Estado</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($datos as $dato) {
            $html .= '<tr>
                        <td>' . $dato['id_revision'] . '</td>
                        <td>' . $dato['rela_reserva'] . '</td>
                        <td>' . $dato['cabania_nombre'] . '</td>
                        <td>' . $dato['inventario_descripcion'] . '</td>
                        <td>$' . number_format($dato['revision_costo'], 2) . '</td>
                        <td>' . ($dato['revision_estado'] == 1 ? 'Activo' : 'Anulado') . '</td>
                      </tr>';
        }

        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'revisiones_' . date('Y-m-d_His') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Obtener inventarios de una cabaña vía AJAX
     */
    public function getInventariosCabania()
    {
        $this->requirePermission('revisiones');

        $idReserva = (int) $this->get('reserva_id');

        if (!$idReserva) {
            echo json_encode(['success' => false, 'message' => 'ID de reserva no válido']);
            return;
        }

        // Obtener la cabaña de la reserva
        $reserva = $this->reservaModel->find($idReserva);
        if (!$reserva) {
            echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
            return;
        }

        // Obtener inventarios de la cabaña
        $sql = "SELECT ic.id_inventariocabania, i.inventario_descripcion, i.id_inventario
                FROM inventario_cabania ic
                INNER JOIN inventario i ON ic.rela_inventario = i.id_inventario
                WHERE ic.rela_cabania = ? AND ic.inventariocabania_estado = 1
                ORDER BY i.inventario_descripcion ASC";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $reserva['rela_cabania']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $inventarios = [];
        while ($row = $result->fetch_assoc()) {
            $inventarios[] = $row;
        }

        echo json_encode([
            'success' => true,
            'inventarios' => $inventarios
        ]);
    }

    /**
     * Obtener costo de daño vía AJAX
     */
    public function getCostoDanio()
    {
        $this->requirePermission('revisiones');

        $inventarioId = (int) $this->get('inventario_id');
        $nivelDanioId = (int) $this->get('nivel_danio_id');

        if (!$inventarioId || !$nivelDanioId) {
            echo json_encode(['success' => false, 'costo' => 0]);
            return;
        }

        $costoDanio = $this->costoDanioModel->findWhere(
            "rela_inventario = ? AND rela_niveldanio = ? AND costodanio_estado = 1",
            [$inventarioId, $nivelDanioId]
        );

        $costo = $costoDanio ? $costoDanio['costodanio_importe'] : 0;

        echo json_encode([
            'success' => true,
            'costo' => $costo
        ]);
    }
}

<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Reserva
 */
class Reserva extends Model
{
    protected $table = 'reserva';
    protected $primaryKey = 'id_reserva';

    /**
     * Obtener reservas con información completa
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['reserva_nro'])) {
            $where .= " AND r.reserva_nro LIKE ?";
            $params[] = '%' . $filters['reserva_nro'] . '%';
        }
        
        if (!empty($filters['estado'])) {
            $where .= " AND r.rela_estadoreserva = ?";
            $params[] = (int)$filters['estado'];
        }
        
        if (!empty($filters['cabania'])) {
            $where .= " AND r.rela_cabania = ?";
            $params[] = (int)$filters['cabania'];
        }
        
        if (!empty($filters['fecha_inicio'])) {
            $where .= " AND r.reserva_fhinicio >= ?";
            $params[] = $filters['fecha_inicio'];
        }
        
        if (!empty($filters['fecha_fin'])) {
            $where .= " AND r.reserva_fhfin <= ?";
            $params[] = $filters['fecha_fin'];
        }
        
        if (!empty($filters['persona'])) {
            $where .= " AND (pf.personafisica_nombre LIKE ? OR pf.personafisica_apellido LIKE ?)";
            $params[] = '%' . $filters['persona'] . '%';
            $params[] = '%' . $filters['persona'] . '%';
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "r.reserva_fhinicio DESC", $params);
    }

    /**
     * Obtener todas las reservas con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['reserva_nro'])) {
            $where .= " AND r.reserva_nro LIKE ?";
            $params[] = '%' . $filters['reserva_nro'] . '%';
        }
        
        if (!empty($filters['estado'])) {
            $where .= " AND r.rela_estadoreserva = ?";
            $params[] = (int)$filters['estado'];
        }
        
        if (!empty($filters['cabania'])) {
            $where .= " AND r.rela_cabania = ?";
            $params[] = (int)$filters['cabania'];
        }
        
        if (!empty($filters['fecha_alta'])) {
            $where .= " AND DATE(r.reserva_fechahora) = ?";
            $params[] = $filters['fecha_alta'];
        }
        
        if (!empty($filters['fecha_inicio'])) {
            $where .= " AND r.reserva_fhinicio >= ?";
            $params[] = $filters['fecha_inicio'];
        }
        
        if (!empty($filters['fecha_fin'])) {
            $where .= " AND r.reserva_fhfin <= ?";
            $params[] = $filters['fecha_fin'];
        }
        
        if (!empty($filters['persona'])) {
            $where .= " AND (pf.personafisica_nombre LIKE ? OR pf.personafisica_apellido LIKE ?)";
            $params[] = '%' . $filters['persona'] . '%';
            $params[] = '%' . $filters['persona'] . '%';
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(DISTINCT r.id_reserva) as total 
                     FROM reserva r
                     LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                     LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                     LEFT JOIN periodo pr ON r.rela_periodo = pr.id_periodo
                     LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                     LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                     LEFT JOIN persona p ON h.rela_persona = p.id_persona
                     LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT r.*, 
                           c.cabania_nombre, c.cabania_codigo, c.cabania_precio, c.cabania_capacidad,
                           er.estadoreserva_descripcion,
                           pr.periodo_descripcion,
                           MAX(pf.personafisica_nombre) as persona_nombre,
                           MAX(pf.personafisica_apellido) as persona_apellido
                    FROM reserva r
                    LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    LEFT JOIN periodo pr ON r.rela_periodo = pr.id_periodo
                    LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona
                    LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                    WHERE $where
                    GROUP BY r.id_reserva
                    ORDER BY r.reserva_fhinicio DESC";
        $dataResult = $this->queryWithParams($dataSql, $params);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Crear nueva reserva con estructura correcta
     */
    public function createReservation($data)
    {
        try {
            // Extraer persona_id para manejo separado
            $personaId = $data['rela_persona'] ?? null;
            
            // Generar reserva_nro (número correlativo)
            $result = $this->db->query("SELECT MAX(reserva_nro) as max_nro FROM reserva");
            $row = $result->fetch_assoc();
            $nextNro = ($row['max_nro'] ?? 0) + 1;
            
            // Preparar datos para tabla reserva (solo campos que existen)
            $reservaData = [
                'reserva_nro' => $nextNro,
                'reserva_fhinicio' => $data['reserva_fechainicio'] ?? $data['reserva_fhinicio'],
                'reserva_fhfin' => $data['reserva_fechafin'] ?? $data['reserva_fhfin'],
                'rela_cabania' => $data['rela_cabania'],
                'rela_estadoreserva' => $data['rela_estadoreserva'] ?? 1,
                'rela_periodo' => $data['rela_periodo'] ?? 1
            ];
            
            // Validar disponibilidad usando los nombres de campos correctos
            $fechaInicio = $reservaData['reserva_fhinicio'];
            $fechaFin = $reservaData['reserva_fhfin'];
            
            if (!$this->checkAvailability($data['rela_cabania'], $fechaInicio, $fechaFin)) {
                throw new \Exception("La cabaña no está disponible para las fechas seleccionadas");
            }
            
            // Crear la reserva
            $reservaId = $this->create($reservaData);
            
            if (!$reservaId) {
                throw new \Exception("Error al crear la reserva");
            }
            
            // Si hay persona_id, manejar la relación con huésped
            if ($personaId) {
                $this->createHuespedReservation($reservaId, $personaId);
            }
            
            return $reservaId;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Crear relación huesped-reserva
     */
    private function createHuespedReservation($reservaId, $personaId)
    {
        // Obtener o crear huésped
        $huespedModel = new \App\Models\Huesped();
        $huesped = $huespedModel->findByPersona($personaId);
        
        if (!$huesped) {
            // Crear nuevo huésped
            $huespedId = $huespedModel->create([
                'rela_persona' => $personaId,
                'huesped_estado' => 1
            ]);
        } else {
            $huespedId = $huesped['id_huesped'];
        }
        
        // Crear relación en huesped_reserva
        $stmt = $this->db->prepare("INSERT INTO huesped_reserva (rela_reserva, rela_huesped) VALUES (?, ?)");
        $stmt->bind_param("ii", $reservaId, $huespedId);
        
        if (!$stmt->execute()) {
            throw new \Exception("Error al vincular huésped con reserva");
        }
        
        return true;
    }

    /**
     * Verificar disponibilidad de cabaña
     * Solo considera reservas CONFIRMADAS (estado 2) y EN_PROGRESO (estado 3) como bloqueantes
     * Las reservas PENDIENTES (estado 1) no bloquean disponibilidad hasta confirmarse
     */
    public function checkAvailability($cabaniaId, $fechaInicio, $fechaFin, $excludeReservaId = null)
    {
        $sql = "SELECT COUNT(*) as conflictos 
                FROM reserva r 
                WHERE r.rela_cabania = ? 
                AND r.rela_estadoreserva IN (2, 3)
                AND (
                    (r.reserva_fhinicio <= ? AND r.reserva_fhfin >= ?) OR
                    (r.reserva_fhinicio <= ? AND r.reserva_fhfin >= ?) OR
                    (r.reserva_fhinicio >= ? AND r.reserva_fhfin <= ?)
                )";
        
        $params = [$cabaniaId, $fechaInicio, $fechaInicio, $fechaFin, $fechaFin, $fechaInicio, $fechaFin];
        
        if ($excludeReservaId) {
            $sql .= " AND r.id_reserva != ?";
            $params[] = $excludeReservaId;
        }
        
        $result = $this->query($sql, $params);
        $row = $result->fetch_assoc();
        
        return (int)$row['conflictos'] === 0;
    }

    /**
     * Obtener reservas por estado
     */
    public function getByStatus($statusId)
    {
        $sql = "SELECT r.*, 
                       c.cabania_nombre, c.cabania_codigo,
                       pf.personafisica_nombre as persona_nombre, 
                       pf.personafisica_apellido as persona_apellido,
                       (SELECT ct.contacto_descripcion FROM contacto ct 
                        LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                        WHERE tc.tipocontacto_descripcion = 'email' AND ct.rela_persona = p.id_persona 
                        LIMIT 1) as persona_email
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                WHERE r.rela_estadoreserva = ?
                ORDER BY r.reserva_fhinicio";
        
        $result = $this->query($sql, [$statusId]);
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        return $reservas;
    }

    /**
     * Obtener consumos de una reserva
     */
    public function getConsumptions($reservaId)
    {
        $sql = "SELECT c.*, 
                       COALESCE(p.producto_nombre, s.servicio_descripcion) as item_nombre,
                       COALESCE(p.producto_precio, s.servicio_precio) as item_precio
                FROM consumo c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                WHERE c.rela_reserva = ?
                AND c.rela_estadoconsumo IN (1, 2, 3)
                ORDER BY c.id_consumo";
        
        $result = $this->query($sql, [$reservaId]);
        
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Cambiar estado de reserva
     */
    public function changeStatus($reservaId, $newStatusId, $observaciones = '')
    {
        $updateData = [
            'rela_estadoreserva' => $newStatusId
        ];
        
        if ($observaciones) {
            $updateData['reserva_observaciones'] = $observaciones;
        }
        
        return $this->update($reservaId, $updateData);
    }

    /**
     * Obtener cabañas disponibles para fechas específicas
     */
    public function getAvailableCabins($startDate, $endDate)
    {
        // Solo las reservas PENDIENTES, CONFIRMADAS y EN_CURSO bloquean disponibilidad
        $estadosQueBloquean = [
            \App\Models\EstadoReserva::PENDIENTE,
            \App\Models\EstadoReserva::CONFIRMADA,
            \App\Models\EstadoReserva::EN_CURSO
        ];
        
        $estadosPlaceholders = str_repeat('?,', count($estadosQueBloquean) - 1) . '?';
        
        $sql = "SELECT c.* 
                FROM cabania c
                WHERE c.rela_estadocabania = 1
                AND c.id_cabania NOT IN (
                    SELECT DISTINCT r.rela_cabania 
                    FROM reserva r
                    WHERE r.rela_estadoreserva IN ($estadosPlaceholders)
                    AND (
                        (? BETWEEN r.reserva_fhinicio AND r.reserva_fhfin) OR
                        (? BETWEEN r.reserva_fhinicio AND r.reserva_fhfin) OR
                        (r.reserva_fhinicio BETWEEN ? AND ?) OR
                        (r.reserva_fhfin BETWEEN ? AND ?)
                    )
                )
                ORDER BY c.cabania_nombre";
        
        $params = array_merge($estadosQueBloquean, [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
        $result = $this->query($sql, $params);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return $records;
    }
    
    /**
     * Obtener total de consumos de una reserva
     */
    public function getConsumptionsTotal($reservaId)
    {
        $sql = "SELECT COALESCE(SUM(c.consumo_cantidad * COALESCE(p.producto_precio, s.servicio_precio, 0)), 0) as total
                FROM consumo c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                WHERE c.rela_reserva = ? AND c.rela_estadoconsumo IN (1, 2, 3)";
        
        $result = $this->query($sql, [$reservaId]);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Confirmar una reserva
     */
    public function confirm($id)
    {
        $confirmedStateId = $this->getStateIdByDescription('confirmada');
        if (!$confirmedStateId) {
            return ['success' => false, 'message' => 'Estado "confirmada" no encontrado'];
        }
        
        $result = $this->update($id, ['rela_estadoreserva' => $confirmedStateId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Reserva confirmada exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al confirmar la reserva'];
    }
    
    /**
     * Cancelar una reserva
     */
    public function cancel($id)
    {
        $canceledStateId = $this->getStateIdByDescription('cancelada');
        if (!$canceledStateId) {
            return ['success' => false, 'message' => 'Estado "cancelada" no encontrado'];
        }
        
        $result = $this->update($id, ['rela_estadoreserva' => $canceledStateId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Reserva cancelada exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al cancelar la reserva'];
    }
    
    /**
     * Obtener ID de estado por descripción
     */
    private function getStateIdByDescription($description)
    {
        $result = $this->query("SELECT id_estadoreserva FROM estadoreserva WHERE estadoreserva_descripcion = ?", [$description]);
        $row = $result->fetch_assoc();
        return $row ? $row['id_estadoreserva'] : null;
    }
    
    /**
     * Obtener reservas con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(DISTINCT r.id_reserva) as total 
                     FROM reserva r
                     LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                     LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                     LEFT JOIN periodo pr ON r.rela_periodo = pr.id_periodo
                     LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                     LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                     LEFT JOIN persona p ON h.rela_persona = p.id_persona
                     LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT r.*, 
                           c.cabania_nombre, c.cabania_codigo, c.cabania_precio, c.cabania_capacidad,
                           er.estadoreserva_descripcion,
                           pr.periodo_descripcion,
                           MAX(pf.personafisica_nombre) as persona_nombre,
                           MAX(pf.personafisica_apellido) as persona_apellido
                    FROM reserva r
                    LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    LEFT JOIN periodo pr ON r.rela_periodo = pr.id_periodo
                    LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona
                    LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                    WHERE $where
                    GROUP BY r.id_reserva
                    $orderClause LIMIT $limit OFFSET $offset";
        $dataResult = $this->queryWithParams($dataSql, $params);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        $totalPages = ceil($total / $perPage);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $limit
        ];
    }

    /**
     * Ejecutar query con parámetros preparados
     * Este método es un alias del método query() heredado del modelo base
     */
    private function queryWithParams($sql, $params = [])
    {
        return $this->query($sql, $params);
    }

    /**
     * Crear reserva con servicios en una sola transacción
     * Los servicios se crean al momento de la reserva para evitar recargar preferencias
     */
    public function createReservationWithServices($reservaData, $servicios = [])
    {
        return $this->db->transaction(function() use ($reservaData, $servicios) {
            try {
                // 1. Verificar disponibilidad de la cabaña
                $fechaInicio = $reservaData['reserva_fhinicio'];
                $fechaFin = $reservaData['reserva_fhfin'];
                
                if (!$this->checkAvailability($reservaData['rela_cabania'], $fechaInicio, $fechaFin)) {
                    throw new \Exception("La cabaña no está disponible para las fechas seleccionadas");
                }
                
                // 2. Generar reserva_nro (número correlativo)
                $result = $this->db->query("SELECT MAX(reserva_nro) as max_nro FROM reserva");
                $row = $result->fetch_assoc();
                $nextNro = ($row['max_nro'] ?? 0) + 1;
                $reservaData['reserva_nro'] = $nextNro;
                
                // 3. Extraer rela_persona antes de crear la reserva
                $personaId = null;
                if (isset($reservaData['rela_persona'])) {
                    $personaId = $reservaData['rela_persona'];
                    unset($reservaData['rela_persona']); // Remover porque no es campo de la tabla reserva
                }
                
                // 4. Crear la reserva (con reserva_nro, sin rela_persona)
                $reservaId = $this->create($reservaData);
                
                if (!$reservaId) {
                    throw new \Exception("Error al crear la reserva");
                }
                
                // 5. Crear relación con huésped si existe persona
                if ($personaId) {
                    $this->createHuespedReservation($reservaId, $personaId);
                }
                
                // 6. Crear servicios como consumos
                if (!empty($servicios)) {
                    $this->createServicesForReservation($reservaId, $servicios, $fechaInicio);
                }
                
                return $reservaId;
                
            } catch (\Exception $e) {
                throw $e; // El rollback se maneja automáticamente por el wrapper transaction()
            }
        });
    }

    /**
     * Crear servicios asociados a una reserva como consumos
     */
    private function createServicesForReservation($reservaId, $servicios, $fechaIngreso)
    {
        $consumoModel = new \App\Models\Consumo();
        
        foreach ($servicios as $servicio) {
            $consumoData = [
                'rela_reserva' => $reservaId,
                'rela_servicio' => $servicio['id'],
                'consumo_descripcion' => 'Servicio: ' . ($servicio['nombre'] ?? 'Servicio seleccionado'),
                'consumo_cantidad' => $servicio['cantidad'] ?? 1,
                'consumo_total' => $servicio['precio'],
                'rela_estadoconsumo' => 1
            ];
            
            $consumoId = $consumoModel->create($consumoData);
            if (!$consumoId) {
                throw new \Exception("Error creando consumo para servicio: " . ($servicio['nombre'] ?? 'desconocido'));
            }
        }
    }

    /**
     * Confirmar pago, actualizar estado de reserva y generar factura en una sola transacción
     * Proceso completo: Pago + Confirmación + Facturación + Cambio estado cabaña
     */
    public function confirmPayment($reservaId, $paymentData)
    {
        // Iniciar transacción
        $this->db->beginTransaction();
        
        try {
            error_log("INFO: Iniciando TRANSACCIÓN confirmPayment para reserva ID: $reservaId");
            
            // 1. Verificar que la reserva exista y esté en estado válido
            $reserva = $this->find($reservaId);
            if (!$reserva) {
                error_log("ERROR confirmPayment: Reserva no encontrada con ID: $reservaId");
                throw new \Exception("Reserva no encontrada con ID: $reservaId");
            }
            
            // Si la reserva ya está confirmada, no procesar nuevamente pero retornar éxito
            if ($reserva['rela_estadoreserva'] == 2) { // Estado CONFIRMADA
                error_log("WARNING: La reserva ya está CONFIRMADA, retornando datos existentes");
                
                // Buscar el pago existente a través de factura
                $pagos = $this->db->query("
                    SELECT p.*, f.rela_reserva 
                    FROM pago p 
                    INNER JOIN factura f ON p.rela_factura = f.id_factura 
                    WHERE f.rela_reserva = {$reservaId} 
                    ORDER BY p.id_pago DESC 
                    LIMIT 1
                ");
                $pago = $pagos ? $pagos->fetch_assoc() : null;
                
                $this->db->commit();
                
                return [
                    'success' => true,
                    'message' => 'La reserva ya estaba confirmada previamente',
                    'pago_id' => $pago['id_pago'] ?? null,
                    'factura_id' => $pago['rela_factura'] ?? null,
                    'reserva_id' => $reservaId,
                    'total_pagado' => $pago['pago_total'] ?? $reserva['reserva_monto'] ?? 0,
                    'fecha_confirmacion' => $pago['pago_fechahora'] ?? date('Y-m-d H:i:s'),
                    'already_confirmed' => true
                ];
            }
            
            if ($reserva['rela_estadoreserva'] != 1) { // Estado PENDIENTE
                throw new \Exception("La reserva no está en estado pendiente para procesar el pago. Estado actual: " . $reserva['rela_estadoreserva']);
            }

            error_log("INFO: Reserva encontrada y validada - Estado: PENDIENTE");

            // 2. Obtener datos completos de la reserva con consumos
            $reservaCompleta = $this->getReservaCompleteData($reservaId);
            if (!$reservaCompleta) {
                error_log("ERROR confirmPayment: getReservaCompleteData devolvió null para reserva ID: $reservaId");
                throw new \Exception("No se pudieron obtener los datos completos de la reserva ID: $reservaId. Por favor, intente nuevamente o contacte al soporte.");
            }

            error_log("INFO: Datos completos obtenidos - Total: " . $reservaCompleta['total_general']);

            // 3. Generar factura primero (DEBE existir antes del pago)
            $facturaId = $this->generateFactura($reservaId, $reservaCompleta);

            if (!$facturaId) {
                throw new \Exception("Error al generar la factura");
            }

            error_log("INFO: Factura generada exitosamente - ID: $facturaId");

            // 4. Registrar el pago con ID de factura
            $pagoModel = new \App\Models\Pago();
            $pagoId = $pagoModel->createPago($reservaId, [
                'total' => $reservaCompleta['total_general'],
                'metodo_pago_id' => $paymentData['metodo_pago_id'] ?? 1,
                'factura_id' => $facturaId // CRÍTICO: Pasar ID de factura
            ]);

            if (!$pagoId) {
                throw new \Exception("Error al registrar el pago");
            }

            error_log("INFO: Pago registrado exitosamente - ID: $pagoId, vinculado a Factura ID: $facturaId");

            // 5. Actualizar estado de la reserva a CONFIRMADA (estado 2)
            $updateResult = $this->update($reservaId, [
                'rela_estadoreserva' => 2 // CONFIRMADA
            ]);

            if (!$updateResult) {
                throw new \Exception("Error al actualizar el estado de la reserva a confirmada");
            }

            error_log("INFO: Estado de reserva actualizado a CONFIRMADA");

            // 7. COMMIT de la transacción
            $this->db->commit();
            error_log("INFO: TRANSACCIÓN CONFIRMADA exitosamente");

            // 8. Resultado exitoso
            $resultado = [
                'success' => true,
                'message' => 'Transacción completada: pago registrado, reserva confirmada, cabaña ocupada y factura generada',
                'pago_id' => $pagoId,
                'factura_id' => $facturaId,
                'reserva_id' => $reservaId,
                'total_pagado' => $reservaCompleta['total_general'],
                'fecha_confirmacion' => date('Y-m-d H:i:s')
            ];
            
            return $resultado;
            
        } catch (\Exception $e) {
            // ROLLBACK en caso de error
            error_log('ERROR en confirmPayment - Haciendo ROLLBACK: ' . $e->getMessage());
            error_log('ERROR confirmPayment stack trace: ' . $e->getTraceAsString());
            
            $this->db->rollback();
            
            throw $e;
        }
    }





    /**
     * Obtener datos completos de la reserva para facturación
     */
    private function getReservaCompleteData($reservaId)
    {
        try {
            // Primero verificar que la reserva existe básicamente
            $basicSql = "SELECT * FROM reserva WHERE id_reserva = ?";
            $basicResult = $this->query($basicSql, [$reservaId]);
            $basicReserva = $basicResult->fetch_assoc();
            
            if (!$basicReserva) {
                error_log("ERROR: Reserva ID $reservaId no existe en la tabla reserva");
                return null;
            }
            
            // Consulta corregida usando la estructura real de la BD: reserva → huesped_reserva → huesped → persona → contacto
            $sql = "SELECT r.*, 
                           c.cabania_nombre, c.cabania_precio, c.cabania_codigo,
                           pf.personafisica_nombre as persona_nombre, 
                           pf.personafisica_apellido as persona_apellido,
                           (SELECT ct.contacto_descripcion FROM contacto ct 
                            LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                            WHERE tc.tipocontacto_descripcion = 'email' AND ct.rela_persona = per.id_persona 
                            LIMIT 1) as persona_email,
                           er.estadoreserva_descripcion
                    FROM reserva r
                    LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    LEFT JOIN persona per ON h.rela_persona = per.id_persona
                    LEFT JOIN personafisica pf ON per.id_persona = pf.rela_persona
                    LEFT JOIN contacto cont ON per.id_persona = cont.rela_persona 
                        AND cont.rela_tipocontacto = 1 
                        AND cont.contacto_estado = 1
                    WHERE r.id_reserva = ?
                    LIMIT 1";

            $result = $this->query($sql, [$reservaId]);
            $reserva = $result->fetch_assoc();

            if (!$reserva) {
                error_log("ERROR: No se pudieron obtener datos completos para la reserva ID: $reservaId");
                return null;
            }

            // Verificar datos requeridos para el cálculo
            if (!$reserva['cabania_precio']) {
                error_log("ERROR: Precio de cabaña no encontrado para la reserva ID: $reservaId");
                return null;
            }
            
            // Calcular días de estadía
            $fechaInicio = new \DateTime($reserva['reserva_fhinicio'] ?? $reserva['reserva_fechainicio']);
            $fechaFin = new \DateTime($reserva['reserva_fhfin']);
            $dias = $fechaInicio->diff($fechaFin)->days;
            
            if ($dias <= 0) {
                error_log("ERROR: Días de estadía inválidos: $dias");
                return null;
            }
            
            // Calcular subtotal del alojamiento
            $precioNoche = floatval($reserva['cabania_precio']);
            $subtotalAlojamiento = $dias * $precioNoche;

            // Obtener consumos (servicios) de la reserva
            $consumos = $this->getConsumptions($reservaId);
            $totalServicios = 0;

            if (is_array($consumos)) {
                foreach ($consumos as $consumo) {
                    $totalServicios += floatval($consumo['consumo_total'] ?? 0);
                }
            } else {
                $consumos = [];
            }

            // Preparar datos completos
            $totalGeneral = $subtotalAlojamiento + $totalServicios;
            
            $reserva['dias_estancia'] = $dias;
            $reserva['subtotal_alojamiento'] = $subtotalAlojamiento;
            $reserva['total_servicios'] = $totalServicios;
            $reserva['total_general'] = $totalGeneral;
            $reserva['consumos'] = $consumos;

            return $reserva;

        } catch (\Exception $e) {
            error_log('Error obteniendo datos completos de reserva: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generar factura completa con detalles
     */
    private function generateFactura($reservaId, $reservaData)
    {
        try {
            $facturaModel = new \App\Models\Factura();

            // Preparar datos de la factura
            $datosFactura = [
                'subtotal' => $reservaData['subtotal_alojamiento'] + $reservaData['total_servicios'],
                'intereses' => 0, // Sin intereses por ahora
                'iva' => 0, // Sin IVA por ahora (se puede calcular si es necesario)
                'total' => $reservaData['total_general'],
                'tipo_comprobante' => 1 // Factura estándar
            ];

            // Preparar detalles de la factura
            $detalles = [];

            // 1. Detalle del alojamiento
            $detalles[] = [
                'descripcion' => "Alojamiento - {$reservaData['cabania_nombre']} ({$reservaData['cabania_codigo']})",
                'precio_unitario' => $reservaData['cabania_precio'],
                'cantidad' => $reservaData['dias_estancia'],
                'total' => $reservaData['subtotal_alojamiento']
            ];

            // 2. Detalles de servicios adicionales
            if (!empty($reservaData['consumos'])) {
                foreach ($reservaData['consumos'] as $consumo) {
                    // Usar item_nombre si existe, sino consumo_descripcion
                    $descripcion = !empty($consumo['item_nombre']) 
                        ? $consumo['item_nombre'] 
                        : $consumo['consumo_descripcion'];
                    
                    // Agregar tipo de item (Producto o Servicio)
                    if (!empty($consumo['rela_producto'])) {
                        $descripcion = "Producto: " . $descripcion;
                    } elseif (!empty($consumo['rela_servicio'])) {
                        $descripcion = "Servicio: " . $descripcion;
                    }
                    
                    $cantidad = floatval($consumo['consumo_cantidad']);
                    $precioUnitario = $cantidad > 0 
                        ? floatval($consumo['consumo_total']) / $cantidad 
                        : floatval($consumo['consumo_total']);
                    
                    $detalles[] = [
                        'descripcion' => $descripcion,
                        'precio_unitario' => $precioUnitario,
                        'cantidad' => $cantidad,
                        'total' => floatval($consumo['consumo_total'])
                    ];
                }
            }

            // Crear factura completa con número de factura automático
            $facturaId = $facturaModel->createFacturaCompleta($reservaId, $datosFactura, $detalles);

            return $facturaId;

        } catch (\Exception $e) {
            error_log('Error generando factura: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar si una reserva pertenece a un usuario específico
     */
    public function isReservaOwner($reservaId, $userId)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM reserva r 
                    LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva 
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped 
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona 
                    LEFT JOIN usuario u ON p.id_persona = u.rela_persona 
                    WHERE r.id_reserva = ? AND u.id_usuario = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $reservaId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close(); // Cerrar statement explícitamente
            
            return $row['count'] > 0;
        } catch (\Exception $e) {
            error_log('Error verificando propiedad de reserva: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el ID del usuario propietario de una reserva
     * (usuario del primer huésped de la reserva)
     */
    public function getUsuarioIdFromReserva($reservaId)
    {
        try {
            $sql = "SELECT u.id_usuario 
                    FROM reserva r 
                    LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva 
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped 
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona 
                    LEFT JOIN usuario u ON p.id_persona = u.rela_persona 
                    WHERE r.id_reserva = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $reservaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row ? (int)$row['id_usuario'] : null;
        } catch (\Exception $e) {
            error_log('Error obteniendo usuario de reserva: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener reservas cercanas de un usuario (próximos X días)
     * 
     * @param int $usuarioId ID del usuario
     * @param int $diasAnticipacion Días de anticipación (por defecto 7)
     * @return array Reservas cercanas con información completa
     */
    public function getReservasCercanasUsuario($usuarioId, $diasAnticipacion = 7)
    {
        try {
            $fechaHoy = date('Y-m-d');
            $fechaLimite = date('Y-m-d', strtotime("+{$diasAnticipacion} days"));
            
            $sql = "SELECT r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                           r.rela_estadoreserva,
                           c.cabania_nombre, c.cabania_codigo, c.cabania_precio,
                           er.estadoreserva_descripcion,
                           DATEDIFF(r.reserva_fhinicio, CURDATE()) as dias_hasta_checkin
                    FROM reserva r
                    INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    INNER JOIN persona p ON h.rela_persona = p.id_persona
                    INNER JOIN usuario u ON p.id_persona = u.rela_persona
                    INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    WHERE u.id_usuario = ?
                      AND r.reserva_fhinicio BETWEEN ? AND ?
                      AND r.rela_estadoreserva IN (1, 2) -- Pendiente o Confirmada
                    ORDER BY r.reserva_fhinicio ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iss", $usuarioId, $fechaHoy, $fechaLimite);
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reservas = [];
            while ($row = $result->fetch_assoc()) {
                $reservas[] = $row;
            }
            
            $stmt->close();
            
            return $reservas;
        } catch (\Exception $e) {
            error_log('ERROR getReservasCercanasUsuario: ' . $e->getMessage());
            error_log('ERROR Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Obtener reservas con pago pendiente de un usuario
     * 
     * @param int $usuarioId ID del usuario
     * @return array Reservas con pago pendiente
     */
    public function getReservasPagoPendienteUsuario($usuarioId)
    {
        try {
            $sql = "SELECT r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                           c.cabania_nombre, c.cabania_codigo, c.cabania_precio,
                           COALESCE(f.factura_total, 0) as factura_total,
                           COALESCE(SUM(DISTINCT pag.pago_total), 0) as monto_pagado,
                           COALESCE(SUM(DISTINCT con.consumo_total), 0) as total_consumos,
                           (COALESCE(f.factura_total, 0) + COALESCE(SUM(DISTINCT con.consumo_total), 0) - COALESCE(SUM(DISTINCT pag.pago_total), 0)) as monto_pendiente
                    FROM reserva r
                    INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    INNER JOIN persona p ON h.rela_persona = p.id_persona
                    INNER JOIN usuario u ON p.id_persona = u.rela_persona
                    INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN factura f ON r.id_reserva = f.rela_reserva
                    LEFT JOIN pago pag ON f.id_factura = pag.rela_factura
                    LEFT JOIN consumo con ON r.id_reserva = con.rela_reserva 
                                          AND con.rela_estadoconsumo IN (1, 2, 3)
                    WHERE u.id_usuario = ?
                      AND r.rela_estadoreserva = 4 -- Estado 'pendiente de pago'
                    GROUP BY r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                             c.cabania_nombre, c.cabania_codigo, c.cabania_precio,
                             f.factura_total
                    HAVING monto_pendiente > 0
                    ORDER BY r.reserva_fhinicio ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reservas = [];
            while ($row = $result->fetch_assoc()) {
                $reservas[] = $row;
            }
            
            $stmt->close();
            return $reservas;
        } catch (\Exception $e) {
            error_log('Error obteniendo reservas con pago pendiente: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener reservas con pago pendiente (datos completos para notificaciones)
     * Se usa para enviar notificaciones en cada carga de página
     * 
     * @param int $usuarioId ID del usuario
     * @return array Reservas con pago pendiente y todos los datos necesarios
     */
    public function getReservasConPagoPendiente($usuarioId)
    {
        try {
            $sql = "SELECT r.id_reserva, 
                           r.reserva_fechahora,
                           r.reserva_fhinicio, 
                           r.reserva_fhfin,
                           r.reserva_online,
                           r.rela_estadoreserva,
                           c.cabania_nombre, c.cabania_codigo, c.cabania_precio,
                           er.estadoreserva_descripcion,
                           COALESCE(f.factura_total, 0) as reserva_montototal,
                           COALESCE(f.factura_subtotal, 0) as reserva_montosenia,
                           COALESCE(SUM(DISTINCT pag.pago_total), 0) as monto_pagado,
                           -- Para reservas online (reserva_online=1), NO incluir consumos cargados posteriormente
                           -- Solo incluir consumos para reservas presenciales (reserva_online=0)
                           CASE 
                               WHEN r.reserva_online = 0 THEN COALESCE(SUM(DISTINCT con.consumo_total), 0)
                               ELSE 0
                           END as total_consumos,
                           -- Saldo pendiente: (Factura + Consumos según tipo) - Pagos
                           (COALESCE(f.factura_total, 0) + 
                            CASE 
                                WHEN r.reserva_online = 0 THEN COALESCE(SUM(DISTINCT con.consumo_total), 0)
                                ELSE 0
                            END - 
                            COALESCE(SUM(DISTINCT pag.pago_total), 0)) as saldo_pendiente
                    FROM reserva r
                    INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    INNER JOIN persona p ON h.rela_persona = p.id_persona
                    INNER JOIN usuario u ON p.id_persona = u.rela_persona
                    INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    LEFT JOIN factura f ON r.id_reserva = f.rela_reserva
                    LEFT JOIN pago pag ON f.id_factura = pag.rela_factura
                    LEFT JOIN consumo con ON r.id_reserva = con.rela_reserva 
                                          AND con.rela_estadoconsumo IN (1, 2, 3)
                    WHERE u.id_usuario = ?
                      AND r.rela_estadoreserva = 4 -- Estado 'pendiente de pago'
                    GROUP BY r.id_reserva, r.reserva_fechahora, r.reserva_fhinicio, r.reserva_fhfin, 
                             r.reserva_online, r.rela_estadoreserva,
                             c.cabania_nombre, c.cabania_codigo, c.cabania_precio,
                             er.estadoreserva_descripcion, f.factura_total, f.factura_subtotal
                    HAVING saldo_pendiente > 0
                    ORDER BY r.reserva_fhinicio ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reservas = [];
            while ($row = $result->fetch_assoc()) {
                // Calcular el saldo_pendiente manualmente
                $factura = floatval($row['reserva_montototal']);
                $consumos = floatval($row['total_consumos']);
                $pagado = floatval($row['monto_pagado']);
                $saldo = $factura + $consumos - $pagado;
                
                // Guardar el saldo calculado
                $row['saldo_pendiente'] = $saldo;
                $row['monto_pendiente'] = $saldo; // Alias para compatibilidad
                
                $reservas[] = $row;
            }
            
            $stmt->close();
            return $reservas;
        } catch (\Exception $e) {
            error_log('ERROR getReservasConPagoPendiente: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si una reserva tiene reintegro asociado
     * 
     * @param int $reservaId ID de la reserva
     * @return bool True si tiene reintegro, false en caso contrario
     */
    public function hasReintegro($reservaId)
    {
        $sql = "SELECT COUNT(*) as tiene_reintegro
                FROM reintegro
                WHERE rela_reserva = ?
                LIMIT 1";
        
        $result = $this->query($sql, [(int)$reservaId]);
        $row = $result->fetch_assoc();
        
        return (int)$row['tiene_reintegro'] > 0;
    }

    /**
     * Obtener reintegro de una reserva
     * 
     * @param int $reservaId ID de la reserva
     * @return array|null Datos del reintegro o null si no existe
     */
    public function getReintegro($reservaId)
    {
        $sql = "SELECT r.*,
                       res.id_reserva,
                       res.reserva_fhinicio,
                       res.reserva_fhfin,
                       c.cabania_nombre,
                       er.estadoreserva_descripcion,
                       CASE r.reintegro_estado
                           WHEN 0 THEN 'Pendiente'
                           WHEN 1 THEN 'Procesado'
                           WHEN 2 THEN 'Rechazado'
                           ELSE 'Desconocido'
                       END as estado_descripcion
                FROM reintegro r
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                LEFT JOIN cabania c ON res.rela_cabania = c.id_cabania
                LEFT JOIN estadoreserva er ON res.rela_estadoreserva = er.id_estadoreserva
                WHERE r.rela_reserva = ?
                LIMIT 1";
        
        $result = $this->query($sql, [(int)$reservaId]);
        return $result->fetch_assoc();
    }

    /**
     * Validar si se puede solicitar reintegro para una reserva
     * 
     * @param int $reservaId ID de la reserva
     * @return array ['valido' => bool, 'mensaje' => string, 'detalles' => array]
     */
    public function canReintegro($reservaId)
    {
        // Validar que la reserva exista
        $reserva = $this->find($reservaId);
        
        if (!$reserva) {
            return [
                'valido' => false,
                'mensaje' => 'La reserva no existe',
                'detalles' => []
            ];
        }
        
        // Validar que no tenga ya un reintegro
        if ($this->hasReintegro($reservaId)) {
            return [
                'valido' => false,
                'mensaje' => 'Esta reserva ya tiene un reintegro solicitado',
                'detalles' => ['reserva' => $reserva, 'reintegro_existente' => $this->getReintegro($reservaId)]
            ];
        }
        
        // Validar que la reserva esté cancelada (estado = 5)
        if ($reserva['rela_estadoreserva'] != 5) {
            return [
                'valido' => false,
                'mensaje' => 'Solo se pueden solicitar reintegros para reservas canceladas',
                'detalles' => ['reserva' => $reserva, 'estado_actual' => $reserva['rela_estadoreserva']]
            ];
        }
        
        // Validar margen de tiempo usando ParametroGeneral
        try {
            $parametrosModel = new ParametroGeneral();
            
            // Usar fecha de inicio de la reserva para validar
            $fechaInicio = $reserva['reserva_fhinicio'];
            
            $validacionMargen = $parametrosModel->validarMargenReintegro($fechaInicio);
            
            if (!$validacionMargen['valido']) {
                return [
                    'valido' => false,
                    'mensaje' => 'Ha excedido el tiempo permitido para solicitar reintegro (' . $validacionMargen['horas_limite'] . ' horas desde la fecha de inicio)',
                    'detalles' => array_merge(
                        $validacionMargen,
                        ['reserva' => $reserva]
                    )
                ];
            }
        } catch (\Exception $e) {
            error_log("Advertencia: No se pudo validar margen de reintegro: " . $e->getMessage());
            // Continuar si falla la validación de tiempo (comportamiento permisivo)
        }
        
        // Validar que la reserva tenga pagos registrados
        $pagoModel = new Pago();
        $totalPagado = $pagoModel->getTotalPagadoReserva($reservaId);
        
        if ($totalPagado <= 0) {
            return [
                'valido' => false,
                'mensaje' => 'No se puede solicitar reintegro porque no hay pagos registrados para esta reserva',
                'detalles' => ['reserva' => $reserva, 'total_pagado' => $totalPagado]
            ];
        }
        
        // Todas las validaciones pasaron
        return [
            'valido' => true,
            'mensaje' => 'Se puede solicitar el reintegro',
            'detalles' => [
                'reserva' => $reserva,
                'total_pagado' => $totalPagado,
                'monto_reintegro' => $this->calcularMontoReintegro($reservaId)
            ]
        ];
    }

    /**
     * Calcular monto de reintegro para una reserva
     * Usa el parámetro PREIN (Porcentaje de Reintegro) de parametrogeneral
     * 
     * @param int $reservaId ID de la reserva
     * @return float Monto a reintegrar (porcentaje del total pagado)
     */
    public function calcularMontoReintegro($reservaId)
    {
        try {
            // Obtener total pagado de la reserva
            $pagoModel = new Pago();
            $totalPagado = $pagoModel->getTotalPagadoReserva($reservaId);
            
            if ($totalPagado <= 0) {
                return 0.0;
            }
            
            // Obtener porcentaje de reintegro desde parámetros generales
            $parametrosModel = new ParametroGeneral();
            $montoReintegro = $parametrosModel->calcularMontoReintegro($totalPagado);
            
            return $montoReintegro;
            
        } catch (\Exception $e) {
            error_log("Error calculando monto de reintegro: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Obtener información completa de reserva con datos de reintegro
     * 
     * @param int $reservaId ID de la reserva
     * @return array|null Datos de reserva con información de reintegro
     */
    public function getReservaConInfoReintegro($reservaId)
    {
        // Usar find() con relaciones en lugar de findWithDetails que no existe
        $reserva = $this->find($reservaId);
        
        if (!$reserva) {
            return null;
        }
        
        // Agregar información de reintegro
        $reserva['tiene_reintegro'] = $this->hasReintegro($reservaId);
        $reserva['puede_reintegro'] = $this->canReintegro($reservaId);
        $reserva['monto_reintegro'] = $this->calcularMontoReintegro($reservaId);
        
        if ($reserva['tiene_reintegro']) {
            $reserva['reintegro'] = $this->getReintegro($reservaId);
        }
        
        return $reserva;
    }

    /**
     * Obtener estadísticas de reintegros
     * 
     * @param array $filters Filtros opcionales (estado, fechas)
     * @return array Estadísticas agregadas de reintegros
     */
    public function getEstadisticasReintegros($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (isset($filters['estado_reintegro']) && $filters['estado_reintegro'] !== '') {
            $where .= " AND rei.reintegro_estado = ?";
            $params[] = (int)$filters['estado_reintegro'];
        }
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND r.reserva_fhinicio >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND r.reserva_fhinicio <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        $sql = "SELECT 
                    COUNT(rei.id_reintegro) as total_reintegros,
                    SUM(rei.reintegro_monto) as total_monto_reintegrado,
                    AVG(rei.reintegro_monto) as promedio_monto_reintegro,
                    SUM(CASE WHEN rei.reintegro_estado = 0 THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN rei.reintegro_estado = 1 THEN 1 ELSE 0 END) as procesados,
                    SUM(CASE WHEN rei.reintegro_estado = 2 THEN 1 ELSE 0 END) as rechazados,
                    COUNT(DISTINCT r.rela_cabania) as cabanias_afectadas
                FROM reintegro rei
                INNER JOIN reserva r ON rei.rela_reserva = r.id_reserva
                WHERE {$where}";
        
        $result = $this->query($sql, $params);
        return $result->fetch_assoc();
    }

    /**
     * Obtener reservas canceladas elegibles para reintegro
     * (Canceladas dentro del margen de tiempo y sin reintegro solicitado)
     * 
     * @param int $limit Límite de resultados
     * @return array Reservas elegibles para reintegro
     */
    public function getReservasCanceladasElegiblesReintegro($limit = 10)
    {
        try {
            $parametrosModel = new ParametroGeneral();
            $horasMargen = $parametrosModel->getMargenHorasReintegro();
            
            $sql = "SELECT r.id_reserva,
                           r.reserva_fhinicio,
                           r.reserva_fhfin,
                           r.reserva_nro,
                           c.cabania_nombre,
                           c.cabania_codigo,
                           pf.personafisica_nombre as persona_nombre,
                           pf.personafisica_apellido as persona_apellido,
                           TIMESTAMPDIFF(HOUR, r.reserva_fhinicio, NOW()) as horas_desde_inicio,
                           (SELECT SUM(pag.pago_monto) FROM pago pag WHERE pag.rela_reserva = r.id_reserva) as total_pagado
                    FROM reserva r
                    INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                    INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    INNER JOIN persona p ON h.rela_persona = p.id_persona
                    INNER JOIN personafisica pf ON p.id_persona = pf.rela_persona
                    LEFT JOIN reintegro rei ON r.id_reserva = rei.rela_reserva
                    WHERE r.rela_estadoreserva = 5
                      AND rei.id_reintegro IS NULL
                      AND TIMESTAMPDIFF(HOUR, r.reserva_fhinicio, NOW()) <= ?
                    ORDER BY r.reserva_fhinicio DESC
                    LIMIT ?";
            
            $result = $this->query($sql, [(int)$horasMargen, (int)$limit]);
            
            $reservas = [];
            while ($row = $result->fetch_assoc()) {
                // Calcular monto de reintegro potencial
                $row['monto_reintegro_estimado'] = $parametrosModel->calcularMontoReintegro($row['total_pagado'] ?? 0);
                $reservas[] = $row;
            }
            
            return $reservas;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo reservas elegibles para reintegro: " . $e->getMessage());
            return [];
        }
    }

}

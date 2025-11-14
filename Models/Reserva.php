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
        $where = "er.estadoreserva_estado = 1";
        
        if (!empty($filters['estado'])) {
            $where .= " AND r.rela_estadoreserva = " . (int)$filters['estado'];
        }
        
        if (!empty($filters['cabania'])) {
            $where .= " AND r.rela_cabania = " . (int)$filters['cabania'];
        }
        
        if (!empty($filters['fecha_inicio'])) {
            $where .= " AND r.reserva_fhinicio >= '" . $this->db->escape($filters['fecha_inicio']) . "'";
        }
        
        if (!empty($filters['fecha_fin'])) {
            $where .= " AND r.reserva_fechafin <= '" . $this->db->escape($filters['fecha_fin']) . "'";
        }
        
        if (!empty($filters['persona'])) {
            $persona = $this->db->escape($filters['persona']);
            $where .= " AND (p.persona_nombre LIKE '%$persona%' OR p.persona_apellido LIKE '%$persona%')";
        }
        
        // Comentado: pe.persona_dni no existe en el esquema de la BD
        // if (!empty($filters['huesped_dni'])) {
        //     $dni = $this->db->escape($filters['huesped_dni']);
        //     $where .= " AND pe.persona_dni LIKE '%$dni%'";
        // }
        
        if (!empty($filters['huesped_nombre'])) {
            $nombre = $this->db->escape($filters['huesped_nombre']);
            $where .= " AND CONCAT(p.persona_nombre, ' ', p.persona_apellido) LIKE '%$nombre%'";
        }
        
        $sql = "SELECT r.*, 
                       c.cabania_nombre, c.cabania_codigo, c.cabania_precio, c.cabania_capacidad,
                       er.estadoreserva_descripcion,
                       pr.periodo_descripcion, pr.periodo_fechainicio, pr.periodo_fechafin,
                       GROUP_CONCAT(DISTINCT CONCAT(p.persona_nombre, ' ', p.persona_apellido) SEPARATOR ', ') as huespedes,
                       MAX((SELECT ct.contacto_descripcion FROM contacto ct 
                            LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                            WHERE tc.tipocontacto_descripcion = 'email' AND ct.rela_persona = p.id_persona 
                            LIMIT 1)) as persona_email,
                       MAX((SELECT ct.contacto_descripcion FROM contacto ct 
                            LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                            WHERE tc.tipocontacto_descripcion = 'telefono' AND ct.rela_persona = p.id_persona 
                            LIMIT 1)) as persona_telefono,
                       MAX(p.persona_nombre) as persona_nombre,
                       MAX(p.persona_apellido) as persona_apellido
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                LEFT JOIN periodo pr ON r.rela_periodo = pr.id_periodo
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                WHERE $where
                GROUP BY r.id_reserva
                ORDER BY r.reserva_fhinicio DESC";
        
        $countSql = "SELECT COUNT(DISTINCT r.id_reserva) as total 
                     FROM reserva r
                     LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                     LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                     LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                     LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                     LEFT JOIN persona p ON h.rela_persona = p.id_persona
                     WHERE $where";
        
        return $this->paginateCustom($sql, $countSql, $page, $perPage);
    }

    /**
     * Crear nueva reserva con estructura correcta
     */
    public function createReservation($data)
    {
        try {
            // Extraer persona_id para manejo separado
            $personaId = $data['rela_persona'] ?? null;
            
            // Preparar datos para tabla reserva (solo campos que existen)
            $reservaData = [
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
        
        error_log("DEBUG checkAvailability - SQL: " . $sql);
        error_log("DEBUG checkAvailability - Params: " . json_encode($params));
        
        $result = $this->query($sql, $params);
        $row = $result->fetch_assoc();
        
        error_log("DEBUG checkAvailability - Conflictos encontrados: " . $row['conflictos']);
        
        return (int)$row['conflictos'] === 0;
    }

    /**
     * Obtener reservas por estado
     */
    public function getByStatus($statusId)
    {
        $sql = "SELECT r.*, 
                       c.cabania_nombre, c.cabania_codigo,
                       p.persona_nombre, p.persona_apellido,
                       (SELECT ct.contacto_descripcion FROM contacto ct 
                        LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                        WHERE tc.tipocontacto_descripcion = 'email' AND ct.rela_persona = p.id_persona 
                        LIMIT 1) as persona_email
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
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
                AND c.consumo_estado = 1
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
                WHERE c.cabania_estado = 1
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
                WHERE c.rela_reserva = ? AND c.consumo_estado = 1";
        
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
     * Paginación personalizada
     */
    protected function paginateCustom($sql, $countSql, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countResult = $this->db->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $paginatedSql = $sql . " LIMIT $perPage OFFSET $offset";
        $result = $this->db->query($paginatedSql);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return [
            'data' => $records,
            'total' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalRecords / $perPage)
        ];
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
                
                // 2. Extraer rela_persona antes de crear la reserva
                $personaId = null;
                if (isset($reservaData['rela_persona'])) {
                    $personaId = $reservaData['rela_persona'];
                    unset($reservaData['rela_persona']); // Remover porque no es campo de la tabla reserva
                }
                
                // 3. Crear la reserva (sin rela_persona)
                $reservaId = $this->create($reservaData);
                
                if (!$reservaId) {
                    throw new \Exception("Error al crear la reserva");
                }
                
                // 4. Crear relación con huésped si existe persona
                if ($personaId) {
                    $this->createHuespedReservation($reservaId, $personaId);
                }
                
                // 5. Crear servicios como consumos
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
                'consumo_estado' => 1
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
            error_log("DEBUG confirmPayment: Buscando reserva con ID: $reservaId");
            $reserva = $this->find($reservaId);
            if (!$reserva) {
                error_log("ERROR confirmPayment: Reserva no encontrada con ID: $reservaId");
                throw new \Exception("Reserva no encontrada con ID: $reservaId");
            }
            
            error_log("DEBUG confirmPayment: Reserva encontrada - Estado: " . $reserva['rela_estadoreserva'] . ", Cabañía: " . $reserva['rela_cabania']);
            
            if ($reserva['rela_estadoreserva'] != 1) { // Estado PENDIENTE
                throw new \Exception("La reserva no está en estado pendiente para procesar el pago. Estado actual: " . $reserva['rela_estadoreserva']);
            }

            error_log("INFO: Reserva encontrada y validada - Estado: PENDIENTE");

            // 2. Obtener datos completos de la reserva con consumos
            error_log("DEBUG confirmPayment: Intentando obtener datos completos para reserva ID: $reservaId");
            $reservaCompleta = $this->getReservaCompleteData($reservaId);
            if (!$reservaCompleta) {
                error_log("ERROR confirmPayment: getReservaCompleteData devolvió null para reserva ID: $reservaId");
                throw new \Exception("No se pudieron obtener los datos completos de la reserva ID: $reservaId. Por favor, intente nuevamente o contacte al soporte.");
            }

            error_log("INFO: Datos completos obtenidos - Total: " . $reservaCompleta['total_general']);

            // 3. Registrar el pago
            $pagoModel = new \App\Models\Pago();
            $pagoId = $pagoModel->createPago($reservaId, [
                'total' => $reservaCompleta['total_general'],
                'metodo_pago_id' => $paymentData['metodo_pago_id'] ?? 1
            ]);

            if (!$pagoId) {
                throw new \Exception("Error al registrar el pago");
            }

            error_log("INFO: Pago registrado exitosamente - ID: $pagoId");

            // 4. Actualizar estado de la reserva a CONFIRMADA (estado 2)
            $updateResult = $this->update($reservaId, [
                'rela_estadoreserva' => 2 // CONFIRMADA
            ]);

            if (!$updateResult) {
                throw new \Exception("Error al actualizar el estado de la reserva a confirmada");
            }

            error_log("INFO: Estado de reserva actualizado a CONFIRMADA");

            // 6. Generar factura completa con detalles y número de factura
            error_log("DEBUG: Iniciando generación de factura para reserva ID: $reservaId");
            $facturaId = $this->generateFactura($reservaId, $reservaCompleta);

            if (!$facturaId) {
                throw new \Exception("Error al generar la factura");
            }

            error_log("INFO: Factura generada exitosamente - ID: $facturaId");

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
            error_log("DEBUG: getReservaCompleteData iniciado para reserva ID: $reservaId");
            
            // Primero verificar que la reserva existe básicamente
            $basicSql = "SELECT * FROM reserva WHERE id_reserva = ?";
            $basicResult = $this->query($basicSql, [$reservaId]);
            $basicReserva = $basicResult->fetch_assoc();
            
            if (!$basicReserva) {
                error_log("ERROR: Reserva ID $reservaId no existe en la tabla reserva");
                return null;
            }
            
            error_log("DEBUG: Reserva básica encontrada - Cabañía ID: " . $basicReserva['rela_cabania']);
            
            // Consulta corregida usando la estructura real de la BD: reserva → huesped_reserva → huesped → persona → contacto
            $sql = "SELECT r.*, 
                           c.cabania_nombre, c.cabania_precio, c.cabania_codigo,
                           per.persona_nombre, per.persona_apellido,
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
                    LEFT JOIN contacto cont ON per.id_persona = cont.rela_persona 
                        AND cont.rela_tipocontacto = 1 
                        AND cont.contacto_estado = 1
                    WHERE r.id_reserva = ?
                    LIMIT 1";

            error_log("DEBUG: Ejecutando consulta corregida con estructura BD real");
            $result = $this->query($sql, [$reservaId]);
            $reserva = $result->fetch_assoc();

            if (!$reserva) {
                error_log("ERROR: No se pudieron obtener datos completos para la reserva ID: $reservaId");
                
                // Debug de emergencia - verificar paso a paso
                $debugSql = "SELECT COUNT(*) as total FROM reserva WHERE id_reserva = ?";
                $debugResult = $this->query($debugSql, [$reservaId]);
                $debugRow = $debugResult->fetch_assoc();
                error_log("DEBUG EMERGENCIA: Reservas encontradas con ID $reservaId: " . $debugRow['total']);
                
                // Verificar si hay huéspedes asociados
                $huespedSql = "SELECT COUNT(*) as total FROM huesped_reserva WHERE rela_reserva = ?";
                $huespedResult = $this->query($huespedSql, [$reservaId]);
                $huespedRow = $huespedResult->fetch_assoc();
                error_log("DEBUG EMERGENCIA: Huéspedes encontrados para reserva $reservaId: " . $huespedRow['total']);
                
                return null;
            }
            
            error_log("DEBUG: Reserva completa obtenida - Cabañía: " . ($reserva['cabania_nombre'] ?? 'NULL') . ", Precio: " . ($reserva['cabania_precio'] ?? 'NULL'));

            // Verificar datos requeridos para el cálculo
            if (!$reserva['cabania_precio']) {
                error_log("ERROR: Precio de cabaña no encontrado para la reserva ID: $reservaId");
                return null;
            }
            
            // Calcular días de estadía
            $fechaInicio = new \DateTime($reserva['reserva_fhinicio'] ?? $reserva['reserva_fechainicio']);
            $fechaFin = new \DateTime($reserva['reserva_fhfin'] ?? $reserva['reserva_fechafin']);
            $dias = $fechaInicio->diff($fechaFin)->days;
            
            if ($dias <= 0) {
                error_log("ERROR: Días de estadía inválidos: $dias");
                return null;
            }
            
            error_log("DEBUG: Fechas - Inicio: " . $fechaInicio->format('Y-m-d') . ", Fin: " . $fechaFin->format('Y-m-d') . ", Días: $dias");

            // Calcular subtotal del alojamiento
            $precioNoche = floatval($reserva['cabania_precio']);
            $subtotalAlojamiento = $dias * $precioNoche;
            
            error_log("DEBUG: Cálculo - Precio por noche: $precioNoche, Días: $dias, Subtotal: $subtotalAlojamiento");

            // Obtener consumos (servicios) de la reserva
            $consumos = $this->getConsumptions($reservaId);
            $totalServicios = 0;

            if (is_array($consumos)) {
                foreach ($consumos as $consumo) {
                    $totalServicios += floatval($consumo['consumo_total'] ?? 0);
                }
                error_log("DEBUG: Total servicios calculado: $totalServicios (" . count($consumos) . " consumos)");
            } else {
                error_log("DEBUG: No hay consumos para esta reserva");
                $consumos = [];
            }

            // Preparar datos completos
            $totalGeneral = $subtotalAlojamiento + $totalServicios;
            
            $reserva['dias_estancia'] = $dias;
            $reserva['subtotal_alojamiento'] = $subtotalAlojamiento;
            $reserva['total_servicios'] = $totalServicios;
            $reserva['total_general'] = $totalGeneral;
            $reserva['consumos'] = $consumos;
            
            error_log("DEBUG: Datos finales - Subtotal alojamiento: $subtotalAlojamiento, Total servicios: $totalServicios, Total general: $totalGeneral");

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
                    $detalles[] = [
                        'descripcion' => $consumo['consumo_descripcion'],
                        'precio_unitario' => $consumo['consumo_total'] / $consumo['consumo_cantidad'],
                        'cantidad' => $consumo['consumo_cantidad'],
                        'total' => $consumo['consumo_total']
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
            
            return $row['count'] > 0;
        } catch (\Exception $e) {
            error_log('Error verificando propiedad de reserva: ' . $e->getMessage());
            return false;
        }
    }

}
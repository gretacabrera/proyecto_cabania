<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Salida extends Model
{
    protected $table = 'reserva';

    /**
     * Obtener reservas que están en curso y pueden hacer checkout
     * 
     * @param int $usuarioId ID del usuario actual
     * @return array Lista de reservas listas para salida
     */
    public function getReservasParaSalida($usuarioId = null)
    {
        $db = Database::getInstance();
        
        $whereClause = "";
        if ($usuarioId) {
            $whereClause = "AND u.id_usuario = " . intval($usuarioId);
        }

        $query = "SELECT 
                    r.id_reserva,
                    r.reserva_fhinicio,
                    r.reserva_fhfin,
                    c.cabania_nombre,
                    c.cabania_precio,
                    p.persona_nombre,
                    p.persona_apellido,
                    er.estadoreserva_descripcion,
                    DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio) as dias_estadia,
                    DATE_FORMAT(r.reserva_fhinicio, '%d/%m/%Y %H:%i') as fecha_inicio_formateada,
                    DATE_FORMAT(r.reserva_fhfin, '%d/%m/%Y %H:%i') as fecha_fin_formateada,
                    (SELECT 
                        (cabania_precio * CASE DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio)
                                        WHEN 0 THEN 1
                                        ELSE DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio)
                                    END)
                    ) as importe_estadia,
                    (SELECT IFNULL(SUM(consumo_total), 0)
                     FROM consumo
                     WHERE rela_reserva = r.id_reserva
                     AND baja_logica = 0
                    ) as importe_consumos,
                    (SELECT IFNULL(SUM(pago_total), 0)
                     FROM pago
                     WHERE rela_reserva = r.id_reserva
                    ) as total_pagado
                  FROM reserva r
                  LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                  LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                  LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                  LEFT JOIN persona p ON h.rela_persona = p.id_persona
                  LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                  LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                  WHERE er.estadoreserva_descripcion = 'en curso'
                  $whereClause
                  AND r.baja_logica = 0
                  ORDER BY r.reserva_fhfin ASC";

        $result = $db->query($query);
        
        $reservas = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Calcular saldo pendiente
                $total_adeudado = $row['importe_estadia'] + $row['importe_consumos'];
                $row['total_adeudado'] = $total_adeudado;
                $row['saldo_pendiente'] = $total_adeudado - $row['total_pagado'];
                $row['estado_pagos'] = ($row['saldo_pendiente'] <= 0) ? 'OK' : 'PENDIENTE';
                
                $reservas[] = $row;
            }
        }
        
        return $reservas;
    }

    /**
     * Calcular el estado de los pagos para una reserva específica
     * 
     * @param int $idReserva ID de la reserva
     * @return array Información financiera de la reserva
     */
    public function calcularEstadoPagos($idReserva)
    {
        $db = Database::getInstance();
        $idReserva = intval($idReserva);

        $query = "SELECT
                    (SELECT 
                        (cabania_precio  *
                        (case DATEDIFF(reserva_fhfin, reserva_fhinicio)
                            when 0 then 1
                            else DATEDIFF(reserva_fhfin, reserva_fhinicio)
                        end))
                    FROM cabania
                    WHERE id_cabania = r.rela_cabania) as importe_estadia,
                    (SELECT IFNULL(SUM(consumo_total),0)
                    FROM consumo
                    WHERE rela_reserva = r.id_reserva
                    AND baja_logica = 0) as importe_consumos,
                    (SELECT IFNULL(sum(pago_total),0)
                    FROM pago
                    where rela_reserva = r.id_reserva) as total_pagado
                FROM reserva r
                WHERE r.id_reserva = ?";

        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $total_adeudado = $row['importe_estadia'] + $row['importe_consumos'];
            $saldo_pendiente = $total_adeudado - $row['total_pagado'];
            
            return [
                'importe_estadia' => $row['importe_estadia'],
                'importe_consumos' => $row['importe_consumos'],
                'total_pagado' => $row['total_pagado'],
                'total_adeudado' => $total_adeudado,
                'saldo_pendiente' => $saldo_pendiente,
                'estado_pagos' => ($saldo_pendiente <= 0) ? 'OK' : 'PENDIENTE'
            ];
        }

        return null;
    }

    /**
     * Registrar la salida de una reserva
     * 
     * @param int $idReserva ID de la reserva
     * @param int $usuarioId ID del usuario que registra la salida
     * @return array Resultado de la operación
     */
    public function registrarSalida($idReserva, $usuarioId = null)
    {
        $db = Database::getInstance();
        $idReserva = intval($idReserva);

        try {
            $db->beginTransaction();

            // Verificar que la reserva existe y está en curso
            $checkQuery = "SELECT r.id_reserva, r.rela_cabania, er.estadoreserva_descripcion
                          FROM reserva r
                          LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                          WHERE r.id_reserva = ? AND r.baja_logica = 0";
            
            $stmt = $db->prepare($checkQuery);
            $stmt->bind_param('i', $idReserva);
            $stmt->execute();
            $reservaData = $stmt->get_result()->fetch_assoc();

            if (!$reservaData) {
                throw new \Exception('La reserva no existe o no es válida');
            }

            if ($reservaData['estadoreserva_descripcion'] !== 'en curso') {
                throw new \Exception('La reserva no está en curso');
            }

            // Calcular estado de pagos
            $estadoPagos = $this->calcularEstadoPagos($idReserva);
            
            if (!$estadoPagos) {
                throw new \Exception('No se pudo calcular el estado de pagos');
            }

            // Determinar el nuevo estado de la reserva
            $nuevoEstado = ($estadoPagos['estado_pagos'] === 'OK') ? 'finalizada' : 'pendiente de pago';

            // Actualizar estado de la reserva
            $updateReservaQuery = "UPDATE reserva 
                                  SET rela_estadoreserva = 
                                      (SELECT id_estadoreserva FROM estadoreserva
                                       WHERE estadoreserva_descripcion = ?)
                                  WHERE id_reserva = ?";
            
            $stmt = $db->prepare($updateReservaQuery);
            $stmt->bind_param('si', $nuevoEstado, $idReserva);
            
            if (!$stmt->execute()) {
                throw new \Exception('Error al actualizar el estado de la reserva');
            }

            // Liberar la cabaña (cambiar estado a libre = 1)
            $updateCabaniaQuery = "UPDATE cabania 
                                  SET cabania_estado = 1
                                  WHERE id_cabania = ?";
            
            $stmt = $db->prepare($updateCabaniaQuery);
            $stmt->bind_param('i', $reservaData['rela_cabania']);
            
            if (!$stmt->execute()) {
                throw new \Exception('Error al liberar la cabaña');
            }

            $db->commit();

            return [
                'success' => true,
                'message' => 'Salida registrada correctamente',
                'nuevo_estado' => $nuevoEstado,
                'estado_pagos' => $estadoPagos,
                'id_reserva' => $idReserva
            ];

        } catch (\Exception $e) {
            $db->rollback();
            return [
                'success' => false,
                'message' => 'Error al registrar salida: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener detalle completo de una reserva para la salida
     * 
     * @param int $idReserva ID de la reserva
     * @return array|null Datos completos de la reserva
     */
    public function getDetalleReserva($idReserva)
    {
        $db = Database::getInstance();
        $idReserva = intval($idReserva);

        $query = "SELECT 
                    r.id_reserva,
                    r.reserva_fhinicio,
                    r.reserva_fhfin,
                    r.reserva_observaciones,
                    c.cabania_nombre,
                    c.cabania_precio,
                    c.cabania_descripcion,
                    p.persona_nombre,
                    p.persona_apellido,
                    (SELECT ct.contacto_descripcion FROM contacto ct 
                     LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                     WHERE tc.tipocontacto_descripcion = 'telefono' AND ct.rela_persona = p.id_persona 
                     LIMIT 1) as persona_telefono,
                    (SELECT ct.contacto_descripcion FROM contacto ct 
                     LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                     WHERE tc.tipocontacto_descripcion = 'email' AND ct.rela_persona = p.id_persona 
                     LIMIT 1) as persona_email,
                    er.estadoreserva_descripcion,
                    DATE_FORMAT(r.reserva_fhinicio, '%d/%m/%Y %H:%i') as fecha_inicio_formateada,
                    DATE_FORMAT(r.reserva_fhfin, '%d/%m/%Y %H:%i') as fecha_fin_formateada,
                    DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio) as dias_estadia
                  FROM reserva r
                  LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                  LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                  LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                  LEFT JOIN persona p ON h.rela_persona = p.id_persona
                  LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                  WHERE r.id_reserva = ?
                  AND r.baja_logica = 0";

        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            // Agregar información financiera
            $estadoPagos = $this->calcularEstadoPagos($idReserva);
            if ($estadoPagos) {
                $row = array_merge($row, $estadoPagos);
            }

            // Obtener consumos
            $row['consumos'] = $this->getConsumosReserva($idReserva);
            
            // Obtener pagos
            $row['pagos'] = $this->getPagosReserva($idReserva);

            return $row;
        }

        return null;
    }

    /**
     * Obtener consumos de una reserva
     * 
     * @param int $idReserva ID de la reserva
     * @return array Lista de consumos
     */
    private function getConsumosReserva($idReserva)
    {
        $db = Database::getInstance();
        $idReserva = intval($idReserva);

        $query = "SELECT 
                    c.id_consumo,
                    c.consumo_cantidad,
                    c.consumo_total,
                    p.producto_nombre,
                    p.producto_precio,
                    DATE_FORMAT(c.created_at, '%d/%m/%Y %H:%i') as fecha_consumo
                  FROM consumo c
                  LEFT JOIN producto p ON c.rela_producto = p.id_producto
                  WHERE c.rela_reserva = ?
                  AND c.baja_logica = 0
                  ORDER BY c.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();

        $consumos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $consumos[] = $row;
            }
        }

        return $consumos;
    }

    /**
     * Obtener pagos de una reserva
     * 
     * @param int $idReserva ID de la reserva
     * @return array Lista de pagos
     */
    private function getPagosReserva($idReserva)
    {
        $db = Database::getInstance();
        $idReserva = intval($idReserva);

        $query = "SELECT 
                    p.id_pago,
                    p.pago_total,
                    p.pago_observaciones,
                    mp.metodo_nombre,
                    DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') as fecha_pago
                  FROM pago p
                  LEFT JOIN metodopago mp ON p.rela_metodopago = mp.id_metodopago
                  WHERE p.rela_reserva = ?
                  ORDER BY p.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();

        $pagos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $pagos[] = $row;
            }
        }

        return $pagos;
    }

    /**
     * Buscar salidas/reservas finalizadas con filtros
     * 
     * @param array $filtros Criterios de búsqueda
     * @return array Lista de reservas que coinciden
     */
    public function buscarSalidas($filtros = [])
    {
        $db = Database::getInstance();
        
        $where = ["r.baja_logica = 0"];
        $params = [];
        $paramTypes = '';

        // Filtro por estado
        if (!empty($filtros['estado'])) {
            $where[] = "er.estadoreserva_descripcion = ?";
            $params[] = $filtros['estado'];
            $paramTypes .= 's';
        } else {
            // Por defecto, solo reservas finalizadas o pendientes de pago
            $where[] = "er.estadoreserva_descripcion IN ('finalizada', 'pendiente de pago')";
        }

        // Filtro por fechas
        if (!empty($filtros['fecha_desde'])) {
            $where[] = "DATE(r.reserva_fhfin) >= ?";
            $params[] = $filtros['fecha_desde'];
            $paramTypes .= 's';
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where[] = "DATE(r.reserva_fhfin) <= ?";
            $params[] = $filtros['fecha_hasta'];
            $paramTypes .= 's';
        }

        // Filtro por cabaña
        if (!empty($filtros['cabania'])) {
            $where[] = "c.id_cabania = ?";
            $params[] = intval($filtros['cabania']);
            $paramTypes .= 'i';
        }

        // Filtro por huésped
        if (!empty($filtros['huesped'])) {
            $where[] = "(p.persona_nombre LIKE ? OR p.persona_apellido LIKE ?)";
            $searchTerm = '%' . $filtros['huesped'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $paramTypes .= 'ss';
        }

        $whereClause = implode(' AND ', $where);

        $query = "SELECT 
                    r.id_reserva,
                    r.reserva_fhinicio,
                    r.reserva_fhfin,
                    c.cabania_nombre,
                    c.cabania_precio,
                    p.persona_nombre,
                    p.persona_apellido,
                    er.estadoreserva_descripcion,
                    DATE_FORMAT(r.reserva_fhinicio, '%d/%m/%Y') as fecha_inicio_formateada,
                    DATE_FORMAT(r.reserva_fhfin, '%d/%m/%Y') as fecha_fin_formateada,
                    DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio) as dias_estadia
                  FROM reserva r
                  LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                  LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                  LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                  LEFT JOIN persona p ON h.rela_persona = p.id_persona
                  LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                  WHERE $whereClause
                  ORDER BY r.reserva_fhfin DESC
                  LIMIT 100";

        if (!empty($params)) {
            $stmt = $db->prepare($query);
            $stmt->bind_param($paramTypes, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $db->query($query);
        }

        $salidas = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $salidas[] = $row;
            }
        }

        return $salidas;
    }

    /**
     * Obtener estadísticas de salidas
     * 
     * @return array Datos estadísticos
     */
    public function getEstadisticasSalidas()
    {
        $db = Database::getInstance();

        // Total de salidas hoy
        $salidasHoy = $db->query("
            SELECT COUNT(*) as total
            FROM reserva r
            LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
            WHERE DATE(r.updated_at) = CURDATE()
            AND er.estadoreserva_descripcion IN ('finalizada', 'pendiente de pago')
            AND r.baja_logica = 0
        ")->fetch_assoc()['total'];

        // Total de salidas esta semana
        $salidasSemana = $db->query("
            SELECT COUNT(*) as total
            FROM reserva r
            LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
            WHERE WEEK(r.updated_at) = WEEK(NOW())
            AND YEAR(r.updated_at) = YEAR(NOW())
            AND er.estadoreserva_descripcion IN ('finalizada', 'pendiente de pago')
            AND r.baja_logica = 0
        ")->fetch_assoc()['total'];

        // Total de salidas este mes
        $salidasMes = $db->query("
            SELECT COUNT(*) as total
            FROM reserva r
            LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
            WHERE MONTH(r.updated_at) = MONTH(NOW())
            AND YEAR(r.updated_at) = YEAR(NOW())
            AND er.estadoreserva_descripcion IN ('finalizada', 'pendiente de pago')
            AND r.baja_logica = 0
        ")->fetch_assoc()['total'];

        // Reservas pendientes de pago
        $pendientesPago = $db->query("
            SELECT COUNT(*) as total
            FROM reserva r
            LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
            WHERE er.estadoreserva_descripcion = 'pendiente de pago'
            AND r.baja_logica = 0
        ")->fetch_assoc()['total'];

        // Cabaña con más salidas
        $result = $db->query("
            SELECT c.cabania_nombre, COUNT(*) as total_salidas
            FROM reserva r
            LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
            LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
            WHERE er.estadoreserva_descripcion IN ('finalizada', 'pendiente de pago')
            AND r.baja_logica = 0
            AND MONTH(r.updated_at) = MONTH(NOW())
            AND YEAR(r.updated_at) = YEAR(NOW())
            GROUP BY c.id_cabania, c.cabania_nombre
            ORDER BY total_salidas DESC
            LIMIT 1
        ");
        
        $cabaniaPopular = $result->fetch_assoc();

        // Distribución por estado
        $distribución = $db->query("
            SELECT 
                er.estadoreserva_descripcion,
                COUNT(*) as cantidad
            FROM reserva r
            LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
            WHERE er.estadoreserva_descripcion IN ('finalizada', 'pendiente de pago')
            AND r.baja_logica = 0
            AND MONTH(r.updated_at) = MONTH(NOW())
            AND YEAR(r.updated_at) = YEAR(NOW())
            GROUP BY er.estadoreserva_descripcion
        ");

        $distribucionEstados = [];
        while ($row = $distribución->fetch_assoc()) {
            $distribucionEstados[] = $row;
        }

        return [
            'salidas_hoy' => $salidasHoy,
            'salidas_semana' => $salidasSemana,
            'salidas_mes' => $salidasMes,
            'pendientes_pago' => $pendientesPago,
            'cabania_popular' => $cabaniaPopular,
            'distribucion_estados' => $distribucionEstados
        ];
    }

    /**
     * Verificar si un usuario puede acceder a una salida específica
     * 
     * @param int $idReserva ID de la reserva
     * @param int $usuarioId ID del usuario
     * @return bool Si puede acceder o no
     */
    public function usuarioPuedeAcceder($idReserva, $usuarioId)
    {
        $db = Database::getInstance();
        
        $query = "SELECT COUNT(*) as count
                  FROM reserva r
                  LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                  LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                  LEFT JOIN persona p ON h.rela_persona = p.id_persona
                  LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                  WHERE r.id_reserva = ? AND u.id_usuario = ? AND r.baja_logica = 0";

        $stmt = $db->prepare($query);
        $stmt->bind_param('ii', $idReserva, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc()['count'] > 0;
    }
}
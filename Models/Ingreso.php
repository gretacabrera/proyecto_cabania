<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

class Ingreso extends Model
{
    protected $table = 'reserva'; // Tabla principal que maneja
    protected $primaryKey = 'id_reserva';

    /**
     * Obtiene las reservas confirmadas del usuario actual que pueden hacer check-in
     * @param string $usuario_nombre Nombre del usuario
     * @return array Array de reservas elegibles para ingreso
     */
    public function getReservasParaIngreso($usuario_nombre)
    {
        try {
            $escaped_usuario = $this->db->escape($usuario_nombre);
            $query = "SELECT r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                           c.id_cabania, c.cabania_nombre, c.cabania_ubicacion,
                           p.persona_nombre, p.persona_apellido
                    FROM reserva r
                    LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona
                    LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                    LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    WHERE u.usuario_nombre = '$escaped_usuario'
                    AND er.estadoreserva_descripcion = 'confirmada'
                    AND NOW() BETWEEN r.reserva_fhinicio AND r.reserva_fhfin
                    AND er.estadoreserva_estado = 1";

            $result = $this->db->query($query);
            $reservas = [];
            
            while ($row = $result->fetch_assoc()) {
                $reservas[] = $row;
            }
            
            return $reservas;
        } catch (Exception $e) {
            throw new Exception("Error al obtener reservas para ingreso: " . $e->getMessage());
        }
    }

    /**
     * Registra el ingreso (check-in) de una reserva
     * @param int $id_reserva ID de la reserva
     * @return array Resultado de la operación con datos de la cabaña
     * @throws Exception Si hay error en la operación
     */
    public function registrarIngreso($id_reserva)
    {
        try {
            // Validar que la reserva existe y está en estado confirmada
            if (!$this->validarReservaParaIngreso($id_reserva)) {
                throw new Exception("La reserva no está en estado válido para ingreso");
            }

            $this->db->beginTransaction();

            // Cambiar estado de reserva a 'en curso'
            $query1 = "UPDATE reserva 
                      SET rela_estadoreserva = (
                          SELECT id_estadoreserva FROM estadoreserva
                          WHERE estadoreserva_descripcion = 'en curso'
                      )
                      WHERE id_reserva = " . (int)$id_reserva;

            $resultado1 = $this->db->query($query1);

            // Cambiar estado de cabaña a ocupada (estado 2)
            $query2 = "UPDATE cabania 
                      LEFT JOIN reserva ON rela_cabania = id_cabania
                      SET cabania_estado = 2
                      WHERE id_reserva = " . (int)$id_reserva;

            $resultado2 = $this->db->query($query2);

            if ($resultado1 && $resultado2) {
                // Obtener datos de la cabaña para el mensaje
                $query3 = "SELECT c.cabania_nombre, c.cabania_ubicacion,
                                 r.reserva_fhinicio, r.reserva_fhfin
                          FROM cabania c
                          LEFT JOIN reserva r ON r.rela_cabania = c.id_cabania
                          WHERE r.id_reserva = " . (int)$id_reserva;

                $result = $this->db->query($query3);
                $datos_cabania = $result->fetch_assoc();

                $this->db->commit();
                
                return [
                    'success' => true,
                    'cabania_nombre' => $datos_cabania['cabania_nombre'],
                    'cabania_ubicacion' => $datos_cabania['cabania_ubicacion'],
                    'fecha_inicio' => $datos_cabania['reserva_fhinicio'],
                    'fecha_fin' => $datos_cabania['reserva_fhfin'],
                    'mensaje' => "Se registró correctamente el ingreso al complejo. La {$datos_cabania['cabania_nombre']} se encuentra {$datos_cabania['cabania_ubicacion']}"
                ];
            } else {
                $this->db->rollback();
                throw new Exception("Error al actualizar los estados de reserva y cabaña");
            }

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Error al registrar ingreso: " . $e->getMessage());
        }
    }

    /**
     * Valida si una reserva puede realizar ingreso
     * @param int $id_reserva ID de la reserva
     * @return bool True si la reserva es válida para ingreso
     */
    private function validarReservaParaIngreso($id_reserva)
    {
        try {
            $query = "SELECT COUNT(*) as total
                     FROM reserva r
                     LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                     WHERE r.id_reserva = " . (int)$id_reserva . "
                     AND er.estadoreserva_descripcion = 'confirmada'
                     AND er.estadoreserva_estado = 1
                     AND NOW() BETWEEN r.reserva_fhinicio AND r.reserva_fhfin";

            $result = $this->db->query($query);
            $row = $result->fetch_assoc();

            return $row['total'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtiene estadísticas de ingresos
     * @return array Estadísticas de ingresos por período
     */
    public function getEstadisticasIngresos()
    {
        try {
            // Ingresos por estado actual
            $query1 = "SELECT er.estadoreserva_descripcion as estado,
                            COUNT(*) as cantidad
                     FROM reserva r
                     LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                     WHERE er.estadoreserva_estado = 1
                     GROUP BY er.estadoreserva_descripcion
                     ORDER BY cantidad DESC";

            $result1 = $this->db->query($query1);
            $estadosPorReservas = [];
            while ($row = $result1->fetch_assoc()) {
                $estadosPorReservas[] = $row;
            }

            // Ingresos por mes (último año)
            $query2 = "SELECT DATE_FORMAT(r.reserva_fhinicio, '%Y-%m') as mes,
                            COUNT(*) as ingresos
                     FROM reserva r
                     LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                     WHERE er.estadoreserva_descripcion IN ('en curso', 'finalizada')
                     AND r.reserva_fhinicio >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                     AND er.estadoreserva_estado = 1
                     GROUP BY DATE_FORMAT(r.reserva_fhinicio, '%Y-%m')
                     ORDER BY mes DESC";

            $result2 = $this->db->query($query2);
            $ingresosPorMes = [];
            while ($row = $result2->fetch_assoc()) {
                $ingresosPorMes[] = $row;
            }

            // Cabañas más utilizadas
            $query3 = "SELECT c.cabania_nombre, c.cabania_ubicacion,
                            COUNT(*) as usos
                     FROM reserva r
                     LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                     LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                     WHERE er.estadoreserva_descripcion IN ('en curso', 'finalizada')
                     AND er.estadoreserva_estado = 1
                     GROUP BY c.id_cabania
                     ORDER BY usos DESC
                     LIMIT 10";

            $result3 = $this->db->query($query3);
            $cabaniasPopulares = [];
            while ($row = $result3->fetch_assoc()) {
                $cabaniasPopulares[] = $row;
            }

            return [
                'estados-reservas' => $estadosPorReservas,
                'ingresos_por_mes' => $ingresosPorMes,
                'cabanias_populares' => $cabaniasPopulares
            ];
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas de ingresos: " . $e->getMessage());
        }
    }

    /**
     * Busca ingresos (reservas) por criterios
     * @param array $criterios Criterios de búsqueda
     * @return array Resultados de la búsqueda
     */
    public function buscarIngresos($criterios)
    {
        try {
            $where_conditions = ["er.estadoreserva_estado = 1"];
            
            // Filtro por fechas
            if (!empty($criterios['fecha_desde'])) {
                $fecha_desde = $this->db->escape($criterios['fecha_desde']);
                $where_conditions[] = "r.reserva_fhinicio >= '$fecha_desde'";
            }

            if (!empty($criterios['fecha_hasta'])) {
                $fecha_hasta = $this->db->escape($criterios['fecha_hasta']);
                $where_conditions[] = "r.reserva_fhinicio <= '$fecha_hasta 23:59:59'";
            }

            // Filtro por cabaña
            if (!empty($criterios['cabania']) && $criterios['cabania'] !== 'todas') {
                $id_cabania = (int)$criterios['cabania'];
                $where_conditions[] = "c.id_cabania = $id_cabania";
            }

            // Filtro por estado de reserva
            if (!empty($criterios['estado']) && $criterios['estado'] !== 'todos') {
                $estado = $this->db->escape($criterios['estado']);
                $where_conditions[] = "er.estadoreserva_descripcion = '$estado'";
            }

            // Filtro por huésped
            if (!empty($criterios['huesped'])) {
                $huesped = $this->db->escape($criterios['huesped']);
                $where_conditions[] = "(p.persona_nombre LIKE '%$huesped%' OR p.persona_apellido LIKE '%$huesped%')";
            }

            $where_clause = implode(' AND ', $where_conditions);

            $query = "SELECT r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                           c.cabania_nombre, c.cabania_ubicacion,
                           er.estadoreserva_descripcion,
                           p.persona_nombre, p.persona_apellido,
                           p.persona_documento
                    FROM reserva r
                    LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona
                    WHERE $where_clause
                    ORDER BY r.reserva_fhinicio DESC";

            $result = $this->db->query($query);
            $ingresos = [];
            
            while ($row = $result->fetch_assoc()) {
                $ingresos[] = $row;
            }
            
            return $ingresos;
        } catch (Exception $e) {
            throw new Exception("Error en búsqueda de ingresos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el detalle completo de un ingreso/reserva
     * @param int $id_reserva ID de la reserva
     * @return array|null Datos completos de la reserva
     */
    public function getDetalleIngreso($id_reserva)
    {
        try {
            $query = "SELECT r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                           c.cabania_nombre, c.cabania_ubicacion, c.cabania_precio,
                           er.estadoreserva_descripcion,
                           p.persona_nombre, p.persona_apellido, p.persona_documento,
                           (SELECT ct.contacto_descripcion FROM contacto ct 
                            LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                            WHERE tc.tipocontacto_descripcion = 'telefono' AND ct.rela_persona = p.id_persona 
                            LIMIT 1) as persona_telefono,
                           (SELECT ct.contacto_descripcion FROM contacto ct 
                            LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                            WHERE tc.tipocontacto_descripcion = 'email' AND ct.rela_persona = p.id_persona 
                            LIMIT 1) as persona_email,
                           u.usuario_nombre,
                           DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio) as dias_estadia,
                           (c.cabania_precio * 
                            CASE DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio)
                                WHEN 0 THEN 1
                                ELSE DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio)
                            END) as importe_estadia
                    FROM reserva r
                    LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                    LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona
                    LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                    WHERE r.id_reserva = " . (int)$id_reserva . "
                    AND er.estadoreserva_estado = 1";

            $result = $this->db->query($query);
            $reserva = $result->fetch_assoc();

            if ($reserva) {
                // Obtener consumos asociados
                $query_consumos = "SELECT co.consumo_total, co.consumo_fechahora,
                                        pr.producto_nombre, pr.producto_precio
                                 FROM consumo co
                                 LEFT JOIN producto pr ON co.rela_producto = pr.id_producto
                                 WHERE co.rela_reserva = " . (int)$id_reserva . "
                                 AND co.consumo_estado = 1
                                 ORDER BY co.consumo_fechahora";

                $result_consumos = $this->db->query($query_consumos);
                $reserva['consumos'] = [];
                while ($row = $result_consumos->fetch_assoc()) {
                    $reserva['consumos'][] = $row;
                }

                // Obtener pagos realizados
                $query_pagos = "SELECT p.pago_total, p.pago_fechahora,
                                     mp.metododepago_descripcion
                              FROM pago p
                              LEFT JOIN metododepago mp ON p.rela_metododepago = mp.id_metododepago
                              WHERE p.rela_reserva = " . (int)$id_reserva . "
                              AND p.pago_estado = 1
                              ORDER BY p.pago_fechahora";

                $result_pagos = $this->db->query($query_pagos);
                $reserva['pagos'] = [];
                while ($row = $result_pagos->fetch_assoc()) {
                    $reserva['pagos'][] = $row;
                }

                return $reserva;
            }

            return null;
        } catch (Exception $e) {
            throw new Exception("Error al obtener detalle del ingreso: " . $e->getMessage());
        }
    }

    /**
     * Verifica si el usuario puede acceder al ingreso especificado
     * @param int $id_reserva ID de la reserva
     * @param string $usuario_nombre Nombre del usuario
     * @return bool True si tiene acceso
     */
    public function usuarioPuedeAcceder($id_reserva, $usuario_nombre)
    {
        try {
            $escaped_usuario = $this->db->escape($usuario_nombre);
            $query = "SELECT COUNT(*) as total
                     FROM reserva r
                     LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                     LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                     LEFT JOIN persona p ON h.rela_persona = p.id_persona
                     LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                     WHERE r.id_reserva = " . (int)$id_reserva . "
                     AND u.usuario_nombre = '$escaped_usuario'
                     AND er.estadoreserva_estado = 1";

            $result = $this->db->query($query);
            $row = $result->fetch_assoc();

            return $row['total'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
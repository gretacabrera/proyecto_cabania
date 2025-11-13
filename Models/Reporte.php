<?php

namespace App\Models;

use App\Core\Database;

/**
 * Modelo para reportes y análisis avanzados
 * Contiene queries complejas para estadísticas de negocio
 */
class Reporte
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Reporte de comentarios con filtros avanzados
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $page Página actual
     * @param int $perPage Registros por página
     * @return array
     */
    public function getComentarios($filtros = [], $page = 1, $perPage = 10)
    {
        $whereConditions = ["c.comentario_estado = 1"];
        $params = [];
        $paramTypes = '';

        // Aplicar filtros
        if (!empty($filtros['fecha_desde'])) {
            $whereConditions[] = "DATE(c.comentario_fechahora) >= ?";
            $params[] = $filtros['fecha_desde'];
            $paramTypes .= 's';
        }

        if (!empty($filtros['fecha_hasta'])) {
            $whereConditions[] = "DATE(c.comentario_fechahora) <= ?";
            $params[] = $filtros['fecha_hasta'];
            $paramTypes .= 's';
        }

        if (!empty($filtros['puntuacion']) && $filtros['puntuacion'] > 0) {
            $whereConditions[] = "c.comentario_puntuacion = ?";
            $params[] = intval($filtros['puntuacion']);
            $paramTypes .= 'i';
        }

        if (!empty($filtros['cabania']) && $filtros['cabania'] > 0) {
            $whereConditions[] = "cab.id_cabania = ?";
            $params[] = intval($filtros['cabania']);
            $paramTypes .= 'i';
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        // Query principal
        $offset = ($page - 1) * $perPage;
        $mainQuery = "
            SELECT 
                c.id_comentario,
                c.comentario_contenido,
                c.comentario_puntuacion,
                c.comentario_fechahora,
                c.comentario_respuesta,
                cab.cabania_nombre,
                cab.cabania_codigo,
                CONCAT(h.huesped_nombre, ' ', h.huesped_apellido) as huesped_nombre_completo,
                h.huesped_email,
                r.id_reserva,
                r.reserva_fecha_inicio,
                r.reserva_fecha_fin,
                CASE 
                    WHEN c.comentario_puntuacion >= 4 THEN 'Excelente'
                    WHEN c.comentario_puntuacion = 3 THEN 'Bueno'
                    WHEN c.comentario_puntuacion = 2 THEN 'Regular'
                    ELSE 'Malo'
                END as categoria_puntuacion
            FROM comentario c
            LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
            LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
            LEFT JOIN huesped h ON r.rela_huesped = h.id_huesped
            {$whereClause}
            ORDER BY c.comentario_fechahora DESC
            LIMIT ?, ?
        ";

        // Agregar parámetros de paginación
        $params[] = $offset;
        $params[] = $perPage;
        $paramTypes .= 'ii';

        $stmt = $this->db->prepare($mainQuery);
        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $comentarios = $result->fetch_all(MYSQLI_ASSOC);

        // Query para contar total
        $countQuery = "
            SELECT COUNT(*) as total
            FROM comentario c
            LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
            LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
            LEFT JOIN huesped h ON r.rela_huesped = h.id_huesped
            {$whereClause}
        ";

        $countStmt = $this->db->prepare($countQuery);
        if (!empty($params) && count($params) > 2) {
            // Remover los parámetros de paginación para el count
            $countParams = array_slice($params, 0, -2);
            $countParamTypes = substr($paramTypes, 0, -2);
            if (!empty($countParams)) {
                $countStmt->bind_param($countParamTypes, ...$countParams);
            }
        }
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];

        return [
            'data' => $comentarios,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Reporte de consumos por cabaña en pesos
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $page Página actual
     * @param int $perPage Registros por página
     * @return array
     */
    public function getConsumosPorCabania($filtros = [], $page = 1, $perPage = 10)
    {
        $whereConditions = ["c.consumo_estado = 1"];
        $params = [];
        $paramTypes = '';

        if (!empty($filtros['cabania']) && $filtros['cabania'] > 0) {
            $whereConditions[] = "cab.id_cabania = ?";
            $params[] = intval($filtros['cabania']);
            $paramTypes .= 'i';
        }

        if (!empty($filtros['fecha_desde'])) {
            $whereConditions[] = "DATE(r.reserva_fhinicio) >= ?";
            $params[] = $filtros['fecha_desde'];
            $paramTypes .= 's';
        }

        if (!empty($filtros['fecha_hasta'])) {
            $whereConditions[] = "DATE(r.reserva_fhinicio) <= ?";
            $params[] = $filtros['fecha_hasta'];
            $paramTypes .= 's';
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $offset = ($page - 1) * $perPage;
        $mainQuery = "
            SELECT 
                cab.id_cabania,
                cab.cabania_nombre,
                cab.cabania_codigo,
                COUNT(DISTINCT r.id_reserva) as total_reservas,
                COUNT(c.id_consumo) as total_consumos,
                SUM(c.consumo_cantidad * p.producto_precio) as total_importe_pesos,
                AVG(c.consumo_cantidad * p.producto_precio) as promedio_por_reserva,
                GROUP_CONCAT(DISTINCT p.producto_nombre ORDER BY p.producto_nombre SEPARATOR ', ') as productos_consumidos,
                MIN(r.reserva_fhinicio) as primera_fecha,
                MAX(r.reserva_fhinicio) as ultima_fecha
            FROM cabania cab
            LEFT JOIN reserva r ON cab.id_cabania = r.rela_cabania
            LEFT JOIN consumo c ON r.id_reserva = c.rela_reserva
            LEFT JOIN producto p ON c.rela_producto = p.id_producto
            {$whereClause}
            GROUP BY cab.id_cabania, cab.cabania_nombre, cab.cabania_codigo
            HAVING total_consumos > 0
            ORDER BY total_importe_pesos DESC
            LIMIT ?, ?
        ";

        $params[] = $offset;
        $params[] = $perPage;
        $paramTypes .= 'ii';

        $stmt = $this->db->prepare($mainQuery);
        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $consumos = $result->fetch_all(MYSQLI_ASSOC);

        // Count query
        $countQuery = "
            SELECT COUNT(DISTINCT cab.id_cabania) as total
            FROM cabania cab
            LEFT JOIN reserva r ON cab.id_cabania = r.rela_cabania
            LEFT JOIN consumo c ON r.id_reserva = c.rela_reserva
            LEFT JOIN producto p ON c.rela_producto = p.id_producto
            {$whereClause}
            HAVING COUNT(c.id_consumo) > 0
        ";

        $countStmt = $this->db->prepare($countQuery);
        if (!empty($params) && count($params) > 2) {
            $countParams = array_slice($params, 0, -2);
            $countParamTypes = substr($paramTypes, 0, -2);
            if (!empty($countParams)) {
                $countStmt->bind_param($countParamTypes, ...$countParams);
            }
        }
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;

        return [
            'data' => $consumos,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Reporte de productos por categoría
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $page Página actual
     * @param int $perPage Registros por página
     * @return array
     */
    public function getProductosPorCategoria($filtros = [], $page = 1, $perPage = 10)
    {
        $whereConditions = ["p.producto_estado = 1"];
        $params = [];
        $paramTypes = '';

        if (!empty($filtros['producto_nombre'])) {
            $whereConditions[] = "p.producto_nombre LIKE ?";
            $params[] = '%' . $filtros['producto_nombre'] . '%';
            $paramTypes .= 's';
        }

        if (!empty($filtros['marca']) && $filtros['marca'] > 0) {
            $whereConditions[] = "p.rela_marca = ?";
            $params[] = intval($filtros['marca']);
            $paramTypes .= 'i';
        }

        if (!empty($filtros['estado_producto']) && $filtros['estado_producto'] > 0) {
            $whereConditions[] = "p.rela_estadoproducto = ?";
            $params[] = intval($filtros['estado_producto']);
            $paramTypes .= 'i';
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        $offset = ($page - 1) * $perPage;
        $mainQuery = "
            SELECT 
                c.categoria_descripcion,
                COUNT(p.id_producto) as cantidad_productos,
                AVG(p.producto_precio) as precio_promedio,
                MIN(p.producto_precio) as precio_minimo,
                MAX(p.producto_precio) as precio_maximo,
                SUM(COALESCE(cons.total_vendido, 0)) as total_vendido,
                GROUP_CONCAT(
                    CONCAT(p.producto_nombre, ' ($', FORMAT(p.producto_precio, 2), ')')
                    ORDER BY p.producto_nombre
                    SEPARATOR '; '
                ) as productos_detalle
            FROM categoria c
            LEFT JOIN producto p ON c.id_categoria = p.rela_categoria AND p.producto_estado = 1
            LEFT JOIN (
                SELECT 
                    rela_producto,
                    SUM(consumo_cantidad) as total_vendido
                FROM consumo
                WHERE consumo_estado = 1
                GROUP BY rela_producto
            ) cons ON p.id_producto = cons.rela_producto
            WHERE c.categoria_estado = 1
            " . (!empty(array_slice($whereConditions, 1)) ? " AND " . implode(" AND ", array_slice($whereConditions, 1)) : "") . "
            GROUP BY c.id_categoria, c.categoria_descripcion
            HAVING cantidad_productos > 0
            ORDER BY cantidad_productos DESC, c.categoria_descripcion
            LIMIT ?, ?
        ";

        $params[] = $offset;
        $params[] = $perPage;
        $paramTypes .= 'ii';

        $stmt = $this->db->prepare($mainQuery);
        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $productos = $result->fetch_all(MYSQLI_ASSOC);

        // Count query
        $countQuery = "
            SELECT COUNT(DISTINCT c.id_categoria) as total
            FROM categoria c
            LEFT JOIN producto p ON c.id_categoria = p.rela_categoria
            {$whereClause}
            GROUP BY c.id_categoria
            HAVING COUNT(p.id_producto) > 0
        ";

        $total = $this->db->query($countQuery)->fetch_assoc()['total'] ?? 0;

        return [
            'data' => $productos,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Reporte de temporadas altas por año
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $page Página actual
     * @param int $perPage Registros por página
     * @return array
     */
    public function getTemporadasAltas($filtros = [], $page = 1, $perPage = 10)
    {
        $whereConditions = [];
        $params = [];
        $paramTypes = '';

        if (!empty($filtros['anio'])) {
            $whereConditions[] = "YEAR(r.reserva_fecha_inicio) = ?";
            $params[] = intval($filtros['anio']);
            $paramTypes .= 'i';
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $offset = ($page - 1) * $perPage;
        $mainQuery = "
            SELECT 
                YEAR(r.reserva_fecha_inicio) as anio,
                p.periodo_descripcion as temporada_alta,
                COUNT(r.id_reserva) as total_reservas,
                COUNT(DISTINCT r.rela_cabania) as cabanas_ocupadas,
                AVG(DATEDIFF(r.reserva_fecha_fin, r.reserva_fecha_inicio)) as promedio_dias_estadia,
                SUM(r.reserva_importe) as ingresos_totales,
                AVG(r.reserva_importe) as ingreso_promedio_reserva,
                MIN(r.reserva_fecha_inicio) as primera_reserva,
                MAX(r.reserva_fecha_fin) as ultima_reserva,
                GROUP_CONCAT(DISTINCT c.cabania_nombre ORDER BY c.cabania_nombre SEPARATOR ', ') as cabanas_utilizadas
            FROM reserva r
            JOIN periodo p ON r.rela_periodo = p.id_periodo
            JOIN cabania c ON r.rela_cabania = c.id_cabania
            {$whereClause}
            GROUP BY YEAR(r.reserva_fecha_inicio), p.id_periodo, p.periodo_descripcion
            ORDER BY anio DESC, total_reservas DESC
            LIMIT ?, ?
        ";

        $params[] = $offset;
        $params[] = $perPage;
        $paramTypes .= 'ii';

        $stmt = $this->db->prepare($mainQuery);
        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $temporadas = $result->fetch_all(MYSQLI_ASSOC);

        // Count query
        $countQuery = "
            SELECT COUNT(*) as total
            FROM (
                SELECT YEAR(r.reserva_fecha_inicio), p.id_periodo
                FROM reserva r
                JOIN periodo p ON r.rela_periodo = p.id_periodo
                {$whereClause}
                GROUP BY YEAR(r.reserva_fecha_inicio), p.id_periodo
            ) as subquery
        ";

        $countStmt = $this->db->prepare($countQuery);
        if (!empty($params) && count($params) > 2) {
            $countParams = array_slice($params, 0, -2);
            $countParamTypes = substr($paramTypes, 0, -2);
            if (!empty($countParams)) {
                $countStmt->bind_param($countParamTypes, ...$countParams);
            }
        }
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];

        return [
            'data' => $temporadas,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Reporte de grupos etarios por período
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $page Página actual
     * @param int $perPage Registros por página
     * @return array
     */
    public function getGruposEtarios($filtros = [], $page = 1, $perPage = 10)
    {
        $whereConditions = [];
        $params = [];
        $paramTypes = '';

        if (!empty($filtros['periodo']) && $filtros['periodo'] > 0) {
            $whereConditions[] = "r.rela_periodo = ?";
            $params[] = intval($filtros['periodo']);
            $paramTypes .= 'i';
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $offset = ($page - 1) * $perPage;
        $mainQuery = "
            SELECT 
                p.periodo_descripcion,
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE()) < 18 THEN 'Menores de 18 años'
                    WHEN TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE()) BETWEEN 18 AND 27 THEN 'Jóvenes (18-27 años)'
                    WHEN TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE()) BETWEEN 28 AND 50 THEN 'Adultos (28-50 años)'
                    WHEN TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE()) BETWEEN 51 AND 70 THEN 'Adultos mayores (51-70 años)'
                    ELSE 'Ancianos (mayores de 70 años)'
                END as grupo_etario,
                COUNT(r.id_reserva) as cantidad_reservas,
                COUNT(DISTINCT h.id_huesped) as huespedes_unicos,
                AVG(TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE())) as edad_promedio,
                MIN(TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE())) as edad_minima,
                MAX(TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE())) as edad_maxima,
                AVG(r.reserva_importe) as gasto_promedio,
                SUM(r.reserva_importe) as gasto_total
            FROM reserva r
            JOIN huesped h ON r.rela_huesped = h.id_huesped
            JOIN periodo p ON r.rela_periodo = p.id_periodo
            {$whereClause}
            GROUP BY p.id_periodo, p.periodo_descripcion, grupo_etario
            ORDER BY p.periodo_descripcion, cantidad_reservas DESC
            LIMIT ?, ?
        ";

        $params[] = $offset;
        $params[] = $perPage;
        $paramTypes .= 'ii';

        $stmt = $this->db->prepare($mainQuery);
        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $grupos = $result->fetch_all(MYSQLI_ASSOC);

        // Count query
        $countQuery = "
            SELECT COUNT(*) as total
            FROM (
                SELECT p.id_periodo,
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE()) < 18 THEN 'Menores de 18 años'
                        WHEN TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE()) BETWEEN 18 AND 27 THEN 'Jóvenes (18-27 años)'
                        WHEN TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE()) BETWEEN 28 AND 50 THEN 'Adultos (28-50 años)'
                        WHEN TIMESTAMPDIFF(YEAR, h.huesped_fecha_nacimiento, CURDATE()) BETWEEN 51 AND 70 THEN 'Adultos mayores (51-70 años)'
                        ELSE 'Ancianos (mayores de 70 años)'
                    END as grupo_etario
                FROM reserva r
                JOIN huesped h ON r.rela_huesped = h.id_huesped
                JOIN periodo p ON r.rela_periodo = p.id_periodo
                {$whereClause}
                GROUP BY p.id_periodo, grupo_etario
            ) as subquery
        ";

        $countStmt = $this->db->prepare($countQuery);
        if (!empty($params) && count($params) > 2) {
            $countParams = array_slice($params, 0, -2);
            $countParamTypes = substr($paramTypes, 0, -2);
            if (!empty($countParams)) {
                $countStmt->bind_param($countParamTypes, ...$countParams);
            }
        }
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];

        return [
            'data' => $grupos,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Obtener producto más vendido por mes
     * 
     * @param array $filtros Filtros de búsqueda
     * @return array
     */
    public function getProductoMasVendidoPorMes($filtros = [])
    {
        $whereConditions = ["c.consumo_estado = 1"];
        $params = [];
        $paramTypes = '';

        if (!empty($filtros['anio'])) {
            $whereConditions[] = "YEAR(r.reserva_fhinicio) = ?";
            $params[] = intval($filtros['anio']);
            $paramTypes .= 'i';
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        $query = "
            SELECT 
                YEAR(r.reserva_fhinicio) as anio,
                MONTH(r.reserva_fhinicio) as mes,
                MONTHNAME(r.reserva_fhinicio) as nombre_mes,
                p.producto_nombre,
                cat.categoria_descripcion,
                m.marca_descripcion,
                SUM(c.consumo_cantidad) as total_vendido,
                COUNT(DISTINCT c.rela_reserva) as reservas_diferentes,
                AVG(c.consumo_cantidad) as promedio_por_reserva,
                SUM(c.consumo_cantidad * p.producto_precio) as ingresos_generados
            FROM consumo c
            JOIN reserva r ON c.rela_reserva = r.id_reserva
            JOIN producto p ON c.rela_producto = p.id_producto
            JOIN categoria cat ON p.rela_categoria = cat.id_categoria
            JOIN marca m ON p.rela_marca = m.id_marca
            {$whereClause}
            GROUP BY YEAR(r.reserva_fhinicio), MONTH(r.reserva_fhinicio), p.id_producto
            ORDER BY anio DESC, mes DESC, total_vendido DESC
        ";

        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener estadísticas generales para dashboard
     * 
     * @return array
     */
    public function getEstadisticasGenerales()
    {
        $stats = [];

        // Total de comentarios por puntuación
        $comentariosQuery = "
            SELECT 
                comentario_puntuacion,
                COUNT(*) as cantidad
            FROM comentario 
            WHERE comentario_estado = 1
            GROUP BY comentario_puntuacion
            ORDER BY comentario_puntuacion DESC
        ";
        $stats['comentarios_por_puntuacion'] = $this->db->query($comentariosQuery)->fetch_all(MYSQLI_ASSOC);

        // Top 5 productos más vendidos
        $productosQuery = "
            SELECT 
                p.producto_nombre,
                SUM(c.consumo_cantidad) as total_vendido,
                SUM(c.consumo_cantidad * p.producto_precio) as ingresos
            FROM consumo c
            JOIN producto p ON c.rela_producto = p.id_producto
            WHERE c.consumo_estado = 1
            GROUP BY p.id_producto, p.producto_nombre
            ORDER BY total_vendido DESC
            LIMIT 5
        ";
        $stats['top_productos'] = $this->db->query($productosQuery)->fetch_all(MYSQLI_ASSOC);

        // Ingresos por mes (último año)
        $ingresosQuery = "
            SELECT 
                DATE_FORMAT(r.reserva_fecha_inicio, '%Y-%m') as periodo,
                COUNT(r.id_reserva) as total_reservas,
                SUM(r.reserva_importe) as ingresos_reservas,
                COALESCE(SUM(consumos.total_consumos), 0) as ingresos_consumos
            FROM reserva r
            LEFT JOIN (
                SELECT 
                    c.rela_reserva,
                    SUM(c.consumo_cantidad * p.producto_precio) as total_consumos
                FROM consumo c
                JOIN producto p ON c.rela_producto = p.id_producto
                WHERE c.consumo_estado = 1
                GROUP BY c.rela_reserva
            ) consumos ON r.id_reserva = consumos.rela_reserva
            WHERE r.reserva_fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(r.reserva_fecha_inicio, '%Y-%m')
            ORDER BY periodo DESC
        ";
        $stats['ingresos_mensuales'] = $this->db->query($ingresosQuery)->fetch_all(MYSQLI_ASSOC);

        // Cabañas más populares
        $cabanasQuery = "
            SELECT 
                c.cabania_nombre,
                c.cabania_codigo,
                COUNT(r.id_reserva) as total_reservas,
                AVG(r.reserva_importe) as ingreso_promedio,
                COALESCE(AVG(com.comentario_puntuacion), 0) as puntuacion_promedio
            FROM cabania c
            LEFT JOIN reserva r ON c.id_cabania = r.rela_cabania
            LEFT JOIN comentario com ON r.id_reserva = com.rela_reserva
            WHERE c.cabania_estado = 1
            GROUP BY c.id_cabania, c.cabania_nombre, c.cabania_codigo
            HAVING total_reservas > 0
            ORDER BY total_reservas DESC
            LIMIT 5
        ";
        $stats['cabanas_populares'] = $this->db->query($cabanasQuery)->fetch_all(MYSQLI_ASSOC);

        return $stats;
    }

    /**
     * Obtener listas para filtros
     * 
     * @return array
     */
    public function getFiltrosData()
    {
        $data = [];

        // Cabañas activas
        $data['cabanas'] = $this->db->query("
            SELECT id_cabania, cabania_nombre, cabania_codigo 
            FROM cabania 
            WHERE cabania_estado = 1 
            ORDER BY cabania_nombre
        ")->fetch_all(MYSQLI_ASSOC);

        // Marcas activas
        $data['marcas'] = $this->db->query("
            SELECT id_marca, marca_descripcion 
            FROM marca 
            WHERE marca_estado = 1 
            ORDER BY marca_descripcion
        ")->fetch_all(MYSQLI_ASSOC);

        // Estados de producto
        $data['estados_producto'] = $this->db->query("
            SELECT id_estadoproducto, estadoproducto_descripcion 
            FROM estadoproducto 
            WHERE estadoproducto_estado = 1 
            ORDER BY estadoproducto_descripcion
        ")->fetch_all(MYSQLI_ASSOC);

        // Períodos
        $data['periodos'] = $this->db->query("
            SELECT id_periodo, periodo_descripcion 
            FROM periodo 
            WHERE periodo_estado = 1 
            ORDER BY periodo_descripcion
        ")->fetch_all(MYSQLI_ASSOC);

        // Años disponibles
        $data['anios'] = $this->db->query("
            SELECT DISTINCT YEAR(reserva_fecha_inicio) as anio
            FROM reserva
            ORDER BY anio DESC
        ")->fetch_all(MYSQLI_ASSOC);

        return $data;
    }
}
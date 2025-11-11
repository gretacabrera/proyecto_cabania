<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad NivelDanio
 */
class NivelDanio extends Model
{
    protected $table = 'niveldanio';
    protected $primaryKey = 'id_niveldanio';

    /**
     * Obtener niveles de daño activos
     */
    public function getActive()
    {
        return $this->findAll("niveldanio_estado = 1", "niveldanio_descripcion");
    }

    /**
     * Obtener niveles de daño con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['niveldanio_descripcion'])) {
            $where .= " AND niveldanio_descripcion LIKE ?";
            $params[] = '%' . $filters['niveldanio_descripcion'] . '%';
        }
        
        if (isset($filters['niveldanio_estado']) && $filters['niveldanio_estado'] !== '') {
            $where .= " AND niveldanio_estado = ?";
            $params[] = (int) $filters['niveldanio_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "niveldanio_descripcion ASC", $params);
    }

    /**
     * Obtener todos los niveles de daño con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['niveldanio_descripcion'])) {
            $where .= " AND niveldanio_descripcion LIKE ?";
            $params[] = '%' . $filters['niveldanio_descripcion'] . '%';
        }
        
        if (isset($filters['niveldanio_estado']) && $filters['niveldanio_estado'] !== '') {
            $where .= " AND niveldanio_estado = ?";
            $params[] = (int) $filters['niveldanio_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT * FROM {$this->table} WHERE $where ORDER BY niveldanio_descripcion ASC";
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
     * Obtener niveles de daño con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT * FROM {$this->table} WHERE $where $orderClause LIMIT $limit OFFSET $offset";
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
     */
    private function queryWithParams($sql, $params = [])
    {
        return $this->query($sql, $params);
    }

    /**
     * Obtener estadísticas de un nivel de daño
     */
    public function getStatistics($id)
    {
        $stats = [
            'costos_danio_registrados' => $this->getCostosDanioRegistrados($id),
            'total_incidencias' => $this->getTotalIncidencias($id),
            'costo_promedio' => $this->getCostoPromedio($id),
            'ultima_incidencia' => $this->getUltimaIncidencia($id),
            'danios_mes' => $this->getDaniosMes($id),
            'danios_anio' => $this->getDaniosAnio($id),
            'costos_facturados_mes' => $this->getCostosFacturadosMes($id),
            'costos_facturados_anio' => $this->getCostosFacturadosAnio($id)
        ];
        
        return $stats;
    }

    /**
     * Obtener daños registrados en el mes actual
     */
    private function getDaniosMes($id)
    {
        // Contar costos de daño relacionados con revisiones de reservas del mes actual
        $sql = "SELECT COUNT(DISTINCT cd.id_costodanio) as total 
                FROM costodanio cd
                INNER JOIN inventario i ON cd.rela_inventario = i.id_inventario
                INNER JOIN inventario_cabania ic ON i.id_inventario = ic.rela_inventario
                INNER JOIN revision r ON ic.id_inventariocabania = r.rela_inventariocabania
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                WHERE cd.rela_niveldanio = ?
                AND MONTH(res.reserva_fhinicio) = MONTH(CURDATE())
                AND YEAR(res.reserva_fhinicio) = YEAR(CURDATE())";
        
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtener daños registrados en el año actual
     */
    private function getDaniosAnio($id)
    {
        // Contar costos de daño relacionados con revisiones de reservas del año actual
        $sql = "SELECT COUNT(DISTINCT cd.id_costodanio) as total 
                FROM costodanio cd
                INNER JOIN inventario i ON cd.rela_inventario = i.id_inventario
                INNER JOIN inventario_cabania ic ON i.id_inventario = ic.rela_inventario
                INNER JOIN revision r ON ic.id_inventariocabania = r.rela_inventariocabania
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                WHERE cd.rela_niveldanio = ?
                AND YEAR(res.reserva_fhinicio) = YEAR(CURDATE())";
        
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtener costos facturados en el mes actual
     */
    private function getCostosFacturadosMes($id)
    {
        // Sumar costos de daño relacionados con revisiones de reservas del mes actual
        $sql = "SELECT COALESCE(SUM(r.revision_costo), 0) as total 
                FROM revision r
                INNER JOIN inventario_cabania ic ON r.rela_inventariocabania = ic.id_inventariocabania
                INNER JOIN inventario i ON ic.rela_inventario = i.id_inventario
                INNER JOIN costodanio cd ON i.id_inventario = cd.rela_inventario
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                WHERE cd.rela_niveldanio = ?
                AND MONTH(res.reserva_fhinicio) = MONTH(CURDATE())
                AND YEAR(res.reserva_fhinicio) = YEAR(CURDATE())";
        
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        return (float)($row['total'] ?? 0);
    }

    /**
     * Obtener costos facturados en el año actual
     */
    private function getCostosFacturadosAnio($id)
    {
        // Sumar costos de daño relacionados con revisiones de reservas del año actual
        $sql = "SELECT COALESCE(SUM(r.revision_costo), 0) as total 
                FROM revision r
                INNER JOIN inventario_cabania ic ON r.rela_inventariocabania = ic.id_inventariocabania
                INNER JOIN inventario i ON ic.rela_inventario = i.id_inventario
                INNER JOIN costodanio cd ON i.id_inventario = cd.rela_inventario
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                WHERE cd.rela_niveldanio = ?
                AND YEAR(res.reserva_fhinicio) = YEAR(CURDATE())";
        
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        return (float)($row['total'] ?? 0);
    }

    /**
     * Obtener número de costos por daño registrados
     */
    private function getCostosDanioRegistrados($id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM costodanio 
                WHERE rela_niveldanio = ?";
        
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtener número total de incidencias
     */
    private function getTotalIncidencias($id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM costodanio 
                WHERE rela_niveldanio = ?";
        
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        return (int)($row['total'] ?? 0);
    }

    /**
     * Calcular costo promedio de incidencias
     */
    private function getCostoPromedio($id)
    {
        $sql = "SELECT AVG(costodanio_importe) as promedio 
                FROM costodanio 
                WHERE rela_niveldanio = ?";
        
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        return (float)($row['promedio'] ?? 0);
    }

    /**
     * Obtener fecha de última incidencia
     */
    private function getUltimaIncidencia($id)
    {
        // La tabla costodanio no tiene campo de fecha, retornamos null
        return null;
    }
}

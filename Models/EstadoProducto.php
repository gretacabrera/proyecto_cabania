<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para el manejo de Estados de Productos
 */
class EstadoProducto extends Model
{
    protected $table = 'estadoproducto';
    protected $primaryKey = 'id_estadoproducto';

    // Nombres de estados de producto
    const DISPONIBLE = 'disponible';
    const EN_STOCK_MINIMO = 'en stock minimo';
    const SIN_STOCK = 'sin stock';
    const BAJA = 'baja';
    
    // Cache para IDs de estados
    private $estadosCache = null;
    private $estadosInvertidos = null;

    /**
     * Cargar estados en cache (método optimizado)
     */
    private function loadEstados()
    {
        if ($this->estadosCache === null) {
            $estados = $this->findAll("estadoproducto_estado = 1");
            
            $this->estadosCache = [];
            $this->estadosInvertidos = [];
            
            foreach ($estados as $estado) {
                $nombre = $estado['estadoproducto_descripcion'];
                $id = $estado['id_estadoproducto'];
                
                $this->estadosCache[$nombre] = $id;
                $this->estadosInvertidos[$id] = $nombre;
            }
        }
    }
    
    /**
     * Obtener ID de estado por nombre
     */
    public function getId($nombre)
    {
        $this->loadEstados();
        return isset($this->estadosCache[$nombre]) ? $this->estadosCache[$nombre] : null;
    }
    
    /**
     * Obtener nombre de estado por ID  
     */
    public function getName($id)
    {
        $this->loadEstados();
        return isset($this->estadosInvertidos[$id]) ? $this->estadosInvertidos[$id] : null;
    }
    
    /**
     * Verificar si un estado existe
     */
    public function existe($nombre)
    {
        $this->loadEstados();
        return isset($this->estadosCache[$nombre]);
    }
    
    /**
     * Obtener múltiples IDs por nombres
     */
    public function getIds($nombres)
    {
        $this->loadEstados();
        $ids = [];
        foreach ($nombres as $nombre) {
            if (isset($this->estadosCache[$nombre])) {
                $ids[] = $this->estadosCache[$nombre];
            }
        }
        return $ids;
    }

    /**
     * Obtener estados de productos con detalles, aplicando filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        
        // Aplicar filtros usando LIKE directo (sin prepared statements para compatibilidad)
        if (!empty($filters['estadoproducto_descripcion'])) {
            $descripcion = $this->db->escape($filters['estadoproducto_descripcion']);
            $where .= " AND estadoproducto_descripcion LIKE '%" . $descripcion . "%'";
        }

        if (isset($filters['estadoproducto_estado']) && $filters['estadoproducto_estado'] !== '') {
            $estado = (int) $filters['estadoproducto_estado'];
            $where .= " AND estadoproducto_estado = " . $estado;
        }

        $result = $this->paginate($page, $perPage, $where, "estadoproducto_descripcion ASC");
        
        // Agregar el conteo de productos a cada estado
        if (isset($result['data'])) {
            foreach ($result['data'] as &$estado) {
                $estado['productos_count'] = $this->getProductosCountByEstado($estado['id_estadoproducto']);
            }
        }
        
        return $result;
    }

    /**
     * Obtener todos los estados de productos con detalles para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";

        // Aplicar filtros usando escape directo
        if (!empty($filters['estadoproducto_descripcion'])) {
            $descripcion = $this->db->escape($filters['estadoproducto_descripcion']);
            $where .= " AND estadoproducto_descripcion LIKE '%" . $descripcion . "%'";
        }

        if (isset($filters['estadoproducto_estado']) && $filters['estadoproducto_estado'] !== '') {
            $estado = (int) $filters['estadoproducto_estado'];
            $where .= " AND estadoproducto_estado = " . $estado;
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY estadoproducto_descripcion ASC";
        $result = $this->db->query($sql);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }

        return [
            'data' => $records,
            'total' => count($records)
        ];
    }

    /**
     * Obtener el conteo de productos por estado de producto
     */
    public function getProductosCountByEstado($id_estadoproducto)
    {
        $id = (int) $id_estadoproducto;
        $sql = "SELECT COUNT(*) as total FROM producto WHERE rela_estadoproducto = {$id}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Obtener estadísticas generales de estados de productos
     */
    public function getEstadisticas()
    {
        $sql = "SELECT 
                    COUNT(*) as total_estados,
                    SUM(CASE WHEN estadoproducto_estado = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estadoproducto_estado = 0 THEN 1 ELSE 0 END) as inactivos
                FROM {$this->table}";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Validar si se puede cambiar el estado del estado de producto
     */
    public function canChangeStatus($id_estadoproducto)
    {
        // Verificar si tiene productos asociados activos
        $productosCount = $this->getProductosCountByEstado($id_estadoproducto);
        return $productosCount === 0;
    }

    /**
     * Obtener estados activos
     */
    public function getActive()
    {
        return $this->findAll("estadoproducto_estado = 1", "estadoproducto_descripcion");
    }

    /**
     * Buscar estados de productos
     */
    public function search($filters = [], $page = 1, $perPage = 10)
    {
        $where = ["estadoproducto_estado = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "estadoproducto_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "estadoproducto_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        
        return $this->paginate($page, $perPage, $whereClause, "estadoproducto_descripcion", $params);
    }

    /**
     * Obtener total de páginas para búsqueda
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "estadoproducto_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "estadoproducto_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        
        $result = $this->query($sql, $params);
        $total = $result->fetch_assoc()['total'];
        
        return ceil($total / $perPage);
    }

    /**
     * Verificar si el estado está en uso
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as count FROM producto WHERE rela_estadoproducto = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Cambiar estado (baja/alta lógica)
     */
    public function toggleStatus($id)
    {
        $estadoProducto = $this->find($id);
        if (!$estadoProducto) {
            return false;
        }

        $newStatus = $estadoProducto['estadoproducto_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['estadoproducto_estado' => $newStatus]);
    }

    /**
     * Obtener estados con conteo de productos
     */
    public function getWithProductCount()
    {
        $sql = "SELECT ep.*, 
                       COUNT(p.id_producto) as productos_count
                FROM {$this->table} ep
                LEFT JOIN producto p ON ep.{$this->primaryKey} = p.rela_estadoproducto
                GROUP BY ep.{$this->primaryKey}
                ORDER BY ep.estadoproducto_descripcion";
        
        $result = $this->db->query($sql);
        
        $estados = [];
        while ($row = $result->fetch_assoc()) {
            $estados[] = $row;
        }
        
        return $estados;
    }

    /**
     * Obtener descripción amigable del estado
     */
    public function getDescripcion($estadoActual)
    {
        $nombreEstado = $this->getName($estadoActual);
        
        $descripciones = [
            self::DISPONIBLE => 'Disponible para venta',
            self::EN_STOCK_MINIMO => 'Stock mínimo alcanzado',
            self::SIN_STOCK => 'Sin stock disponible',
            self::BAJA => 'Producto dado de baja'
        ];
        
        return isset($descripciones[$nombreEstado]) ? $descripciones[$nombreEstado] : ucfirst($nombreEstado);
    }

    /**
     * Obtener clase CSS para el estado
     */
    public function getCssClass($estadoActual)
    {
        $nombreEstado = $this->getName($estadoActual);
        
        $clases = [
            self::DISPONIBLE => 'success',
            self::EN_STOCK_MINIMO => 'warning',
            self::SIN_STOCK => 'danger',
            self::BAJA => 'secondary'
        ];
        
        return isset($clases[$nombreEstado]) ? $clases[$nombreEstado] : 'secondary';
    }

    /**
     * Obtener estadísticas de un estado de producto específico
     */
    public function getStatistics($id)
    {
        $stats = [
            'total_productos' => $this->getTotalProductosByEstado($id),
            'productos_mes_actual' => $this->getProductosMesActualByEstado($id),
            'porcentaje_uso' => $this->getPorcentajeUsoByEstado($id)
        ];
        
        return $stats;
    }

    /**
     * Obtener total de productos con este estado (histórico)
     */
    private function getTotalProductosByEstado($id)
    {
        $sql = "SELECT COUNT(*) as total FROM producto WHERE rela_estadoproducto = ?";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtener productos del mes actual con este estado
     */
    private function getProductosMesActualByEstado($id)
    {
        // Calcular valor monetario total de productos con este estado
        $sql = "SELECT COALESCE(SUM(producto_precio * producto_stock), 0) as valor_total 
                FROM producto 
                WHERE rela_estadoproducto = ?";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        return (float)($row['valor_total'] ?? 0);
    }

    /**
     * Obtener porcentaje de uso de este estado respecto al total
     */
    private function getPorcentajeUsoByEstado($id)
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM producto WHERE rela_estadoproducto = ?) as estado_count,
                    (SELECT COUNT(*) FROM producto) as total_count";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        $estadoCount = (int)($row['estado_count'] ?? 0);
        $totalCount = (int)($row['total_count'] ?? 0);
        
        if ($totalCount == 0) {
            return 0;
        }
        
        return round(($estadoCount / $totalCount) * 100, 1);
    }
}
<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad ParametroGeneral
 * Gestiona parámetros de configuración del sistema
 */
class ParametroGeneral extends Model
{
    protected $table = 'parametrogeneral';
    protected $primaryKey = 'id_parametrogeneral';

    // Códigos de parámetros del sistema
    const PARAM_IVA = 'PIIVA';              // Porcentaje IVA
    const PARAM_DESCUENTO_EFECTIVO = 'PDEFE'; // Porcentaje descuento efectivo
    const PARAM_COSTO_MERCADOPAGO = 'COSMP';  // Costo MercadoPago
    const PARAM_HORAS_REINTEGRO = 'MHSRE';    // Margen horas para reintegro
    const PARAM_PORCENTAJE_REINTEGRO = 'PREIN'; // Porcentaje de reintegro

    /**
     * Obtener parámetros con paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['parametrogeneral_codigo'])) {
            $where .= " AND parametrogeneral_codigo LIKE ?";
            $params[] = '%' . $filters['parametrogeneral_codigo'] . '%';
        }
        
        if (!empty($filters['parametrogeneral_descripcion'])) {
            $where .= " AND parametrogeneral_descripcion LIKE ?";
            $params[] = '%' . $filters['parametrogeneral_descripcion'] . '%';
        }
        
        if (isset($filters['parametrogeneral_estado']) && $filters['parametrogeneral_estado'] !== '') {
            $where .= " AND parametrogeneral_estado = " . (int)$filters['parametrogeneral_estado'];
        }
        
        return $this->paginate($page, $perPage, $where, "parametrogeneral_codigo ASC");
    }

    /**
     * Obtener parámetro por código
     */
    public function getByCode($codigo)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE parametrogeneral_codigo = ? 
                AND parametrogeneral_estado = 1 
                LIMIT 1";
        
        $result = $this->query($sql, [$codigo]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener valor de parámetro por código
     * Retorna el valor numérico almacenado en descripción
     */
    public function getParametroValue($codigo)
    {
        $parametro = $this->getByCode($codigo);
        
        if (!$parametro) {
            throw new \Exception("Parámetro no encontrado: $codigo");
        }
        
        // La descripción contiene el valor numérico
        return (float) $parametro['parametrogeneral_descripcion'];
    }

    /**
     * Actualizar valor de parámetro por código
     */
    public function updateByCode($codigo, $nuevoValor)
    {
        $parametro = $this->getByCode($codigo);
        
        if (!$parametro) {
            throw new \Exception("Parámetro no encontrado: $codigo");
        }
        
        return $this->update($parametro[$this->primaryKey], [
            'parametrogeneral_descripcion' => $nuevoValor
        ]);
    }

    /**
     * Obtener porcentaje de IVA
     */
    public function getIVA()
    {
        return $this->getParametroValue(self::PARAM_IVA);
    }

    /**
     * Obtener porcentaje de descuento por pago en efectivo
     */
    public function getDescuentoEfectivo()
    {
        return $this->getParametroValue(self::PARAM_DESCUENTO_EFECTIVO);
    }

    /**
     * Obtener costo de comisión de MercadoPago
     */
    public function getCostoMercadoPago()
    {
        return $this->getParametroValue(self::PARAM_COSTO_MERCADOPAGO);
    }

    /**
     * Obtener margen de horas permitido para reintegros
     */
    public function getMargenHorasReintegro()
    {
        return $this->getParametroValue(self::PARAM_HORAS_REINTEGRO);
    }

    /**
     * Obtener porcentaje de reintegro
     */
    public function getPorcentajeReintegro()
    {
        return $this->getParametroValue(self::PARAM_PORCENTAJE_REINTEGRO);
    }

    /**
     * Calcular IVA sobre un monto
     */
    public function calcularIVA($monto)
    {
        $iva = $this->getIVA();
        return $monto * ($iva / 100);
    }

    /**
     * Calcular monto con IVA incluido
     */
    public function calcularMontoConIVA($monto)
    {
        $iva = $this->getIVA();
        return $monto * (1 + ($iva / 100));
    }

    /**
     * Calcular descuento por pago en efectivo
     */
    public function calcularDescuentoEfectivo($monto)
    {
        $descuento = $this->getDescuentoEfectivo();
        return $monto * ($descuento / 100);
    }

    /**
     * Calcular monto con descuento por efectivo
     */
    public function calcularMontoConDescuentoEfectivo($monto)
    {
        $descuento = $this->getDescuentoEfectivo();
        return $monto * (1 - ($descuento / 100));
    }

    /**
     * Calcular comisión de MercadoPago
     */
    public function calcularComisionMercadoPago($monto)
    {
        $comision = $this->getCostoMercadoPago();
        return $monto * ($comision / 100);
    }

    /**
     * Calcular monto de reintegro
     */
    public function calcularMontoReintegro($montoBase)
    {
        $porcentaje = $this->getPorcentajeReintegro();
        return $montoBase * ($porcentaje / 100);
    }

    /**
     * Validar si una fecha está dentro del margen de reintegro
     */
    public function validarMargenReintegro($fechaConsumo)
    {
        $horasMargen = $this->getMargenHorasReintegro();
        $fechaConsumoObj = new \DateTime($fechaConsumo);
        $fechaActual = new \DateTime();
        
        $diferencia = $fechaActual->diff($fechaConsumoObj);
        $horasTranscurridas = ($diferencia->days * 24) + $diferencia->h;
        
        return [
            'valido' => $horasTranscurridas <= $horasMargen,
            'horas_transcurridas' => $horasTranscurridas,
            'horas_margen' => $horasMargen,
            'horas_restantes' => max(0, $horasMargen - $horasTranscurridas)
        ];
    }

    /**
     * Obtener todos los parámetros activos
     */
    public function getParametrosActivos()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE parametrogeneral_estado = 1 
                ORDER BY parametrogeneral_codigo";
        
        $result = $this->db->query($sql);
        
        $parametros = [];
        while ($row = $result->fetch_assoc()) {
            $parametros[$row['parametrogeneral_codigo']] = $row;
        }
        
        return $parametros;
    }

    /**
     * Obtener todos los valores como array asociativo
     */
    public function getParametrosValores()
    {
        $parametros = $this->getParametrosActivos();
        
        $valores = [];
        foreach ($parametros as $codigo => $parametro) {
            $valores[$codigo] = (float) $parametro['parametrogeneral_descripcion'];
        }
        
        return $valores;
    }

    /**
     * Exportar todos los parámetros sin paginación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['parametrogeneral_codigo'])) {
            $where .= " AND parametrogeneral_codigo LIKE ?";
            $params[] = '%' . $filters['parametrogeneral_codigo'] . '%';
        }
        
        if (isset($filters['parametrogeneral_estado']) && $filters['parametrogeneral_estado'] !== '') {
            $where .= " AND parametrogeneral_estado = ?";
            $params[] = (int) $filters['parametrogeneral_estado'];
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE $where ORDER BY parametrogeneral_codigo";
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $parametros = [];
        while ($row = $result->fetch_assoc()) {
            $parametros[] = $row;
        }
        
        return [
            'data' => $parametros,
            'total' => $total
        ];
    }

    /**
     * Cambiar estado de parámetro
     */
    public function changeStatus($id, $status)
    {
        return $this->update($id, ['parametrogeneral_estado' => $status]);
    }

    /**
     * Validar código de parámetro
     */
    public function codigoExiste($codigo, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE parametrogeneral_codigo = ?";
        $params = [$codigo];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
}

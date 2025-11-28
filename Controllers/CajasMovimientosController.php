<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\CajaMovimiento;
use App\Models\CajaTurno;
use App\Models\Caja;

class CajasMovimientosController extends Controller
{
    private $modelo;
    private $modeloTurno;
    private $modeloCaja;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new CajaMovimiento();
        $this->modeloTurno = new CajaTurno();
        $this->modeloCaja = new Caja();
    }

    /**
     * Listado de movimientos
     */
    public function index()
    {
        $this->requirePermission('cajas');

        $usuarioId = Auth::id();
        
        // Obtener caja del usuario
        $caja = $this->modeloTurno->getCajaByUsuario($usuarioId);
        
        if (!$caja) {
            $data = [
                'title' => 'Movimientos de Caja',
                'sinCaja' => true,
                'isAdminArea' => true
            ];
            return $this->render('admin/operaciones/cajas_movimientos/index', $data, 'main');
        }
        
        // Verificar si hay turno abierto
        $turnoAbierto = $this->modeloTurno->getTurnoAbierto($caja['id_caja']);
        
        if (!$turnoAbierto) {
            $data = [
                'title' => 'Movimientos de Caja',
                'sinTurno' => true,
                'caja' => $caja,
                'isAdminArea' => true
            ];
            return $this->render('admin/operaciones/cajas_movimientos/index', $data, 'main');
        }

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        // Filtrar solo por el turno actual del usuario
        $filters = [
            'turno' => $turnoAbierto['id_cajaturno'],
            'tipo' => $this->get('tipo')
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);
        
        // Obtener estadísticas del turno actual
        $estadisticas = [
            'total_movimientos' => 0,
            'total_ingresos' => 0,
            'total_egresos' => 0,
            'cantidad_ingresos' => 0,
            'cantidad_egresos' => 0
        ];
        
        foreach ($result['data'] as $mov) {
            $estadisticas['total_movimientos']++;
            if ($mov['cajamovimiento_tipo'] === 'I') {
                $estadisticas['total_ingresos'] += $mov['cajamovimiento_monto'];
                $estadisticas['cantidad_ingresos']++;
            } else {
                $estadisticas['total_egresos'] += $mov['cajamovimiento_monto'];
                $estadisticas['cantidad_egresos']++;
            }
        }

        $data = [
            'title' => 'Movimientos de Caja',
            'movimientos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'turnoActual' => $turnoAbierto,
            'caja' => $caja,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas_movimientos/index', $data, 'main');
    }

    /**
     * Formulario para registrar movimiento
     */
    public function create()
    {
        $this->requirePermission('cajas');

        $usuarioId = Auth::id();
        
        // Obtener caja del usuario
        $caja = $this->modeloTurno->getCajaByUsuario($usuarioId);
        
        if (!$caja) {
            $this->redirect('/aperturas', 'No tiene una caja asignada', 'error');
            return;
        }
        
        // Verificar si hay turno abierto
        $turnoAbierto = $this->modeloTurno->getTurnoAbierto($caja['id_caja']);
        
        if (!$turnoAbierto) {
            $this->redirect('/aperturas', 'No hay turno abierto. Debe abrir la caja primero', 'error');
            return;
        }

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Movimiento de Caja',
            'caja' => $caja,
            'turnoActual' => $turnoAbierto,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas_movimientos/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo movimiento
     */
    public function store()
    {
        $this->requirePermission('cajas');

        $usuarioId = Auth::id();
        
        // Obtener caja del usuario
        $caja = $this->modeloTurno->getCajaByUsuario($usuarioId);
        
        if (!$caja) {
            $this->redirect('/aperturas', 'No tiene una caja asignada', 'error');
            return;
        }
        
        // Verificar turno abierto
        $turnoAbierto = $this->modeloTurno->getTurnoAbierto($caja['id_caja']);
        
        if (!$turnoAbierto) {
            $this->redirect('/aperturas', 'No hay turno abierto', 'error');
            return;
        }
        
        // Validar datos
        $descripcion = trim($this->post('cajamovimiento_descripcion') ?? '');
        $tipo = $this->post('cajamovimiento_tipo');
        $monto = (float) $this->post('cajamovimiento_monto', 0);
        
        if (empty($descripcion)) {
            $this->redirect('/movimientos/create', 'Debe ingresar una descripción', 'error');
            return;
        }
        
        if (!in_array($tipo, ['I', 'E'])) {
            $this->redirect('/movimientos/create', 'Tipo de movimiento inválido', 'error');
            return;
        }
        
        if ($monto <= 0) {
            $this->redirect('/movimientos/create', 'El monto debe ser mayor a cero', 'error');
            return;
        }
        
        // Registrar movimiento
        $movimientoId = $this->modelo->registrarMovimiento(
            $turnoAbierto['id_cajaturno'],
            $descripcion,
            $tipo,
            $monto
        );
        
        if ($movimientoId) {
            $tipoTexto = $tipo === 'I' ? 'Ingreso' : 'Egreso';
            $this->redirect('/movimientos', 
                "{$tipoTexto} registrado correctamente por \$" . number_format($monto, 2, ',', '.'), 
                'success'
            );
        } else {
            $this->redirect('/movimientos/create', 'Error al registrar el movimiento', 'error');
        }
    }

    /**
     * Ver detalle de un movimiento
     */
    public function show($id)
    {
        $this->requirePermission('cajas');

        $movimiento = $this->modelo->find($id);
        
        if (!$movimiento) {
            $this->redirect('/movimientos', 'Movimiento no encontrado', 'error');
            return;
        }
        
        // Obtener datos del turno
        $turno = $this->modeloTurno->find($movimiento['rela_cajaturno']);

        $data = [
            'title' => 'Detalle de Movimiento',
            'movimiento' => $movimiento,
            'turno' => $turno,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas_movimientos/detalle', $data, 'main');
    }

    /**
     * Anular movimiento
     */
    public function anular($id)
    {
        $this->requirePermission('cajas');

        if ($this->isPost()) {
            $movimiento = $this->modelo->find($id);
            
            if (!$movimiento) {
                $this->redirect('/movimientos', 'Movimiento no encontrado', 'error');
                return;
            }
            
            // Baja lógica
            if ($this->modelo->update($id, ['cajamovimiento_estado' => 0])) {
                $this->redirect('/movimientos', 'Movimiento anulado correctamente', 'success');
            } else {
                $this->redirect('/movimientos', 'Error al anular el movimiento', 'error');
            }
        } else {
            $this->redirect('/movimientos');
        }
    }
}

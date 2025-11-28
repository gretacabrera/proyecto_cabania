<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CajaTurno;
use App\Models\Caja;

/**
 * Controlador para el manejo de aperturas de caja
 */
class CajasAperturasController extends Controller
{
    protected $cajaTurnoModel;
    protected $cajaModel;

    public function __construct()
    {
        parent::__construct();
        $this->cajaTurnoModel = new CajaTurno();
        $this->cajaModel = new Caja();
    }

    /**
     * Pantalla principal - Información de la caja asignada
     */
    public function index()
    {
        // Requiere autenticación - el permiso 'cajas' puede no existir aún
        $this->requireAuth();

        // Obtener el usuario logueado usando Auth helper
        $usuarioId = \App\Core\Auth::id();
        
        if (!$usuarioId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión', 'error');
            return;
        }

        // Obtener la caja asignada al usuario
        $caja = $this->cajaTurnoModel->getCajaByUsuario($usuarioId);
        
        if (!$caja) {
            $data = [
                'title' => 'Gestión de Caja',
                'caja' => null,
                'turnoAbierto' => null,
                'isAdminArea' => true
            ];
            
            return $this->render('admin/operaciones/cajas_aperturas/index', $data, 'main');
        }

        // Verificar si hay un turno abierto
        $turnoAbierto = $this->cajaTurnoModel->getTurnoAbierto($caja['id_caja']);
        
        // Si hay turno abierto, obtener estadísticas
        $estadisticas = null;
        $ultimosMovimientos = [];
        
        if ($turnoAbierto) {
            $estadisticas = $this->cajaTurnoModel->getEstadisticasTurno($turnoAbierto['id_cajaturno']);
            $ultimosMovimientos = $this->cajaTurnoModel->getUltimosMovimientos($turnoAbierto['id_cajaturno'], 5);
        }

        // Obtener historial de turnos
        $historialTurnos = $this->cajaTurnoModel->getHistorialTurnos($caja['id_caja'], 10);

        $data = [
            'title' => 'Gestión de Caja',
            'caja' => $caja,
            'turnoAbierto' => $turnoAbierto,
            'estadisticas' => $estadisticas,
            'ultimosMovimientos' => $ultimosMovimientos,
            'historialTurnos' => $historialTurnos,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas_aperturas/index', $data, 'main');
    }

    /**
     * Mostrar formulario de apertura de caja
     */
    public function apertura()
    {
        // Requiere autenticación - el permiso 'cajas' puede no existir aún
        $this->requireAuth();

        // Obtener el usuario logueado usando Auth helper
        $usuarioId = \App\Core\Auth::id();
        
        if (!$usuarioId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión', 'error');
            return;
        }

        // Obtener la caja asignada al usuario
        $caja = $this->cajaTurnoModel->getCajaByUsuario($usuarioId);
        
        if (!$caja) {
            $this->redirect('/aperturas', 'No tiene una caja asignada', 'error');
            return;
        }

        // Verificar si ya hay un turno abierto
        $turnoAbierto = $this->cajaTurnoModel->getTurnoAbierto($caja['id_caja']);
        
        if ($turnoAbierto) {
            $this->redirect('/aperturas', 'Ya existe un turno abierto para esta caja', 'error');
            return;
        }

        if ($this->isPost()) {
            return $this->procesarApertura();
        }

        // Denominaciones de billetes argentinos
        $denominaciones = [
            ['valor' => 10, 'cantidad' => 0],
            ['valor' => 20, 'cantidad' => 0],
            ['valor' => 50, 'cantidad' => 0],
            ['valor' => 100, 'cantidad' => 0],
            ['valor' => 200, 'cantidad' => 0],
            ['valor' => 500, 'cantidad' => 0],
            ['valor' => 1000, 'cantidad' => 0],
            ['valor' => 2000, 'cantidad' => 0],
            ['valor' => 10000, 'cantidad' => 0],
            ['valor' => 20000, 'cantidad' => 0]
        ];

        $data = [
            'title' => 'Apertura de Caja',
            'caja' => $caja,
            'denominaciones' => $denominaciones,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas_aperturas/formulario', $data, 'main');
    }

    /**
     * Procesar apertura de caja
     */
    private function procesarApertura()
    {
        // Requiere autenticación - el permiso 'cajas' puede no existir aún
        $this->requireAuth();

        // Obtener el usuario logueado usando Auth helper
        $usuarioId = \App\Core\Auth::id();
        
        if (!$usuarioId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión', 'error');
            return;
        }

        // Obtener la caja asignada al usuario
        $caja = $this->cajaTurnoModel->getCajaByUsuario($usuarioId);
        
        if (!$caja) {
            $this->redirect('/aperturas', 'No tiene una caja asignada', 'error');
            return;
        }

        // Verificar si ya hay un turno abierto
        $turnoAbierto = $this->cajaTurnoModel->getTurnoAbierto($caja['id_caja']);
        
        if ($turnoAbierto) {
            $this->redirect('/aperturas', 'Ya existe un turno abierto para esta caja', 'error');
            return;
        }

        // Obtener denominaciones
        $denominaciones = [];
        $total = 0;

        $valoresDenominaciones = [10, 20, 50, 100, 200, 500, 1000, 2000, 10000, 20000];
        
        foreach ($valoresDenominaciones as $valor) {
            $cantidad = (int) $this->post("denom_{$valor}", 0);
            if ($cantidad > 0) {
                $subtotal = $valor * $cantidad;
                $denominaciones[] = [
                    'valor' => $valor,
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }
        }

        // Validación: debe haber al menos un billete contado
        if ($total <= 0) {
            $this->redirect('/aperturas/apertura', 'Debe contar al menos un billete para abrir la caja', 'error');
            return;
        }

        try {
            // Crear el turno de caja
            $turnoId = $this->cajaTurnoModel->abrirCaja($caja['id_caja'], $usuarioId, $total, $denominaciones);
            
            if ($turnoId) {
                $this->redirect('/aperturas', 'Caja abierta correctamente. Monto inicial: $' . number_format($total, 2, ',', '.'), 'exito');
            } else {
                throw new \Exception('Error al abrir la caja');
            }

        } catch (\Exception $e) {
            error_log("Error al procesar apertura de caja: " . $e->getMessage());
            $this->redirect('/aperturas/apertura', 'Error al abrir la caja: ' . $e->getMessage(), 'error');
        }
    }
}

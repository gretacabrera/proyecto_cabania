<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\CajaArqueo;
use App\Models\CajaTurno;
use App\Models\Caja;

class CajasArqueoController extends Controller
{
    private $modelo;
    private $modeloTurno;
    private $modeloCaja;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new CajaArqueo();
        $this->modeloTurno = new CajaTurno();
        $this->modeloCaja = new Caja();
    }

    /**
     * Listado de arqueos realizados
     */
    public function index()
    {
        $this->requirePermission('cajas');

        $usuarioId = Auth::id();
        
        // Obtener caja del usuario
        $caja = $this->modeloTurno->getCajaByUsuario($usuarioId);
        
        if (!$caja) {
            $data = [
                'title' => 'Arqueos de Caja',
                'sinCaja' => true,
                'isAdminArea' => true
            ];
            return $this->render('admin/operaciones/cajas_arqueo/index', $data, 'main');
        }
        
        // Verificar si hay turno abierto
        $turnoAbierto = $this->modeloTurno->getTurnoAbierto($caja['id_caja']);
        
        if (!$turnoAbierto) {
            $data = [
                'title' => 'Arqueos de Caja',
                'sinTurno' => true,
                'caja' => $caja,
                'isAdminArea' => true
            ];
            return $this->render('admin/operaciones/cajas_arqueo/index', $data, 'main');
        }

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        // Filtrar solo por el turno actual del usuario
        $filters = [
            'turno' => $turnoAbierto['id_cajaturno']
        ];

        $result = $this->modelo->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Arqueos de Caja',
            'arqueos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'turnoActual' => $turnoAbierto,
            'caja' => $caja,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas_arqueo/index', $data, 'main');
    }

    /**
     * Formulario para realizar arqueo
     */
    public function formulario()
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
            $this->redirect('/aperturas', 'No hay turno abierto para realizar arqueo', 'error');
            return;
        }
        
        // Obtener estadísticas del turno
        $estadisticas = $this->modeloTurno->getEstadisticasTurno($turnoAbierto['id_cajaturno']);
        
        // Denominaciones argentinas
        $denominaciones = [
            ['valor' => 10],
            ['valor' => 20],
            ['valor' => 50],
            ['valor' => 100],
            ['valor' => 200],
            ['valor' => 500],
            ['valor' => 1000],
            ['valor' => 2000],
            ['valor' => 10000],
            ['valor' => 20000]
        ];

        $data = [
            'title' => 'Arqueo de Caja',
            'caja' => $caja,
            'turnoAbierto' => $turnoAbierto,
            'estadisticas' => $estadisticas,
            'denominaciones' => $denominaciones,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas_arqueo/formulario', $data, 'main');
    }

    /**
     * Procesar el arqueo de caja
     */
    public function procesarArqueo()
    {
        $this->requirePermission('cajas');

        if ($this->isPost()) {
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
            
            // Calcular total contado
            $totalContado = 0;
            $denominaciones = [10, 20, 50, 100, 200, 500, 1000, 2000, 10000, 20000];
            
            foreach ($denominaciones as $denom) {
                $cantidad = (int) $this->post("denom_{$denom}", 0);
                $totalContado += $denom * $cantidad;
            }
            
            // Guardar arqueo
            $arqueoId = $this->modelo->guardarArqueo(
                $turnoAbierto['id_cajaturno'],
                $totalContado
            );
            
            if ($arqueoId) {
                $this->redirect('/arqueos', 'Arqueo registrado correctamente', 'success');
            } else {
                $this->redirect('/arqueos/formulario', 'Error al guardar el arqueo', 'error');
            }
        } else {
            $this->redirect('/arqueos/formulario');
        }
    }

    /**
     * Ver detalle de un arqueo
     */
    public function show($id)
    {
        $this->requirePermission('cajas');

        $arqueo = $this->modelo->find($id);
        
        if (!$arqueo) {
            $this->redirect('/arqueos', 'Arqueo no encontrado', 'error');
            return;
        }
        
        // Obtener datos del turno
        $turno = $this->modeloTurno->find($arqueo['rela_cajaturno']);
        $estadisticas = $this->modeloTurno->getEstadisticasTurno($arqueo['rela_cajaturno']);

        $data = [
            'title' => 'Detalle de Arqueo',
            'arqueo' => $arqueo,
            'turno' => $turno,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cajas_arqueo/detalle', $data, 'main');
    }
}

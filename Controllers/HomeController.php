<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

/**
 * Controlador principal para la página de inicio
 */
class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Casa de Palos - Cabañas',
            'user' => null,
            'userProfile' => null,
            'showReservaButton' => false
        ];

        if (Auth::check()) {
            $data['user'] = Auth::user();
            $data['userProfile'] = Auth::getUserProfile();
            
            // Mostrar botón de reserva si es huésped
            if ($data['userProfile'] === 'huesped') {
                $data['showReservaButton'] = true;
            }
        }

        return $this->render('public/home', $data, 'main');
    }

    /**
     * Página de información sobre las cabañas
     */
    public function about()
    {
        $data = [
            'title' => 'Acerca de Nosotros - Casa de Palos',
        ];

        return $this->render('public/home/about', $data, 'main');
    }

    /**
     * Página de contacto
     */
    public function contact()
    {
        if ($this->isPost()) {
            // Procesar formulario de contacto
            $nombre = $this->post('nombre');
            $email = $this->post('email');
            $mensaje = $this->post('mensaje');

            // Aquí podrías enviar email o guardar en BD
            // Por ahora solo redirigimos con mensaje de éxito
            
            $this->redirect('/', 'Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.', 'exito');
        }

        $data = [
            'title' => 'Contacto - Casa de Palos',
        ];

        return $this->render('public/home/contact', $data, 'main');
    }
}
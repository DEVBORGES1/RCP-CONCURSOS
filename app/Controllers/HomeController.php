<?php
/**
 * HomeController
 * 
 * Controller responsável pela página inicial
 * 
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\BaseController;

class HomeController extends BaseController
{
    /**
     * Exibe a página inicial
     * 
     * @return void
     */
    public function index(): void
    {
        $data = [
            'titulo' => 'RCP - Sistema de Concursos - Plataforma de Estudos'
        ];

        echo $this->view('home/index', $data);
    }
}


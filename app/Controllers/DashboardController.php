<?php
/**
 * DashboardController
 * 
 * Controller responsável pelo dashboard principal do sistema
 * 
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Usuario;
use App\Models\Edital;
use App\Models\Simulado;

class DashboardController extends BaseController
{
    private Usuario $usuarioModel;
    private Edital $editalModel;
    private Simulado $simuladoModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->requireAuth();
        
        $this->usuarioModel = new Usuario();
        $this->editalModel = new Edital();
        $this->simuladoModel = new Simulado();
    }

    /**
     * Exibe o dashboard principal
     * 
     * @return void
     */
    public function index(): void
    {
        $userId = $this->getUserId();

        // Obter dados do usuário
        $dadosUsuario = $this->usuarioModel->obterDadosCompletos($userId);

        // Calcular estatísticas
        $totalQuestoes = $dadosUsuario['questoes_respondidas'] ?? 0;
        $questoesCorretas = $dadosUsuario['questoes_corretas'] ?? 0;
        $percentualAcerto = $totalQuestoes > 0 ? round(($questoesCorretas / $totalQuestoes) * 100, 1) : 0;

        // Obter editais
        $totalEditais = $this->editalModel->contarPorUsuario($userId);

        // Obter simulados
        $simuladosConcluidos = $this->simuladoModel->findConcluidos($userId);
        $totalSimulados = count($simuladosConcluidos);

        // Preparar dados para a view
        $data = [
            'titulo' => 'Dashboard - Sistema de Concursos',
            'usuario' => [
                'nome' => $dadosUsuario['nome'] ?? 'Usuário',
                'nivel' => $dadosUsuario['nivel'] ?? 1,
                'pontos' => $dadosUsuario['pontos_total'] ?? 0,
                'streak' => $dadosUsuario['streak_dias'] ?? 0,
            ],
            'estatisticas' => [
                'questoes_respondidas' => $totalQuestoes,
                'questoes_corretas' => $questoesCorretas,
                'percentual_acerto' => $percentualAcerto,
                'total_editais' => $totalEditais,
                'total_simulados' => $totalSimulados,
            ],
            'conquistas' => $dadosUsuario['conquistas'] ?? [],
            'simulados_recentes' => array_slice($simuladosConcluidos, 0, 5),
        ];

        echo $this->view('dashboard/index', $data);
    }
}


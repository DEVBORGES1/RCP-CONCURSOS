<?php
/**
 * AuthController
 * 
 * Controller responsável por autenticação e registro de usuários
 * 
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Usuario;
use App\Models\Progresso;

class AuthController extends BaseController
{
    private Usuario $usuarioModel;
    private Progresso $progressoModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->progressoModel = new Progresso();
    }

    /**
     * Mostra página de login
     * 
     * @return void
     */
    public function login(): void
    {
        // Se já está logado, redireciona para dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $mensagem = $_SESSION['flash']['error'] ?? '';
        unset($_SESSION['flash']['error']);

        $data = [
            'titulo' => 'Login - Sistema de Concursos',
            'mensagem' => $mensagem
        ];

        echo $this->view('auth/login', $data);
    }

    /**
     * Processa login do usuário
     * 
     * @return void
     */
    public function processarLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        // Validar entrada
        if (empty($email) || empty($senha)) {
            $this->setFlash('error', 'Por favor, preencha todos os campos.');
            $this->redirect('/login');
            return;
        }

        // Verificar credenciais
        $usuario = $this->usuarioModel->verificarCredenciais($email, $senha);

        if ($usuario) {
            // Criar sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];

            // Garantir que o progresso existe
            $this->progressoModel->obterOuCriar($usuario['id']);

            // Atualizar streak
            $this->progressoModel->atualizarStreak($usuario['id']);

            $this->setFlash('success', 'Login realizado com sucesso!');
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('error', 'Email ou senha incorretos.');
            $this->redirect('/login');
        }
    }

    /**
     * Mostra página de registro
     * 
     * @return void
     */
    public function register(): void
    {
        // Se já está logado, redireciona para dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $mensagem = $_SESSION['flash']['error'] ?? '';
        unset($_SESSION['flash']['error']);

        $data = [
            'titulo' => 'Cadastro - Sistema de Concursos',
            'mensagem' => $mensagem
        ];

        echo $this->view('auth/register', $data);
    }

    /**
     * Processa registro do usuário
     * 
     * @return void
     */
    public function processarRegistro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
            return;
        }

        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        // Validar entrada
        if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {
            $this->setFlash('error', 'Por favor, preencha todos os campos.');
            $this->redirect('/register');
            return;
        }

        if ($senha !== $confirmarSenha) {
            $this->setFlash('error', 'As senhas não coincidem.');
            $this->redirect('/register');
            return;
        }

        if (strlen($senha) < 6) {
            $this->setFlash('error', 'A senha deve ter pelo menos 6 caracteres.');
            $this->redirect('/register');
            return;
        }

        // Verificar se email já existe
        $usuarioExistente = $this->usuarioModel->findByEmail($email);
        if ($usuarioExistente) {
            $this->setFlash('error', 'Este email já está cadastrado.');
            $this->redirect('/register');
            return;
        }

        // Criar usuário
        $usuarioId = $this->usuarioModel->criar($nome, $email, $senha);

        if ($usuarioId) {
            // Inicializar progresso
            $this->progressoModel->obterOuCriar($usuarioId);

            $this->setFlash('success', 'Cadastro realizado com sucesso! Faça login.');
            $this->redirect('/login');
        } else {
            $this->setFlash('error', 'Erro ao criar conta. Tente novamente.');
            $this->redirect('/register');
        }
    }

    /**
     * Faz logout do usuário
     * 
     * @return void
     */
    public function logout(): void
    {
        // Limpar todas as variáveis de sessão
        $_SESSION = [];

        // Destruir a sessão
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $this->redirect('/');
    }
}


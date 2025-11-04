<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/GamificacaoRefatorada.php';

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } else {
        // Cadastrar usuário usando POO
        $user = new User();
        
        if ($user->create($nome, $email, $senha)) {
            $usuario_id = $user->getId();
            
            // Inicializar progresso do usuário usando a classe GamificacaoRefatorada
            $gamificacao = new GamificacaoRefatorada();
            $gamificacao->garantirProgressoUsuario($usuario_id);
            
            // Adicionar pontos de boas-vindas
            $gamificacao->adicionarPontos($usuario_id, 50, 'primeiro_acesso');
            
            $mensagem = "Cadastro realizado com sucesso! Você ganhou 50 pontos de boas-vindas!";
            
            // Fazer login automático
            $_SESSION["usuario_id"] = $usuario_id;
            
            // Redirecionar após 2 segundos
            header("refresh:2;url=dashboard.php");
        } else {
            $erro = "Erro ao cadastrar usuário. Este email já está cadastrado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h1><i class="fas fa-user-plus"></i> Criar Conta</h1>
                <p>Junte-se a nós e comece sua jornada de estudos!</p>
            </div>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $mensagem ?>
                    <p style="margin-top: 10px; font-size: 0.9rem;">Redirecionando para o dashboard...</p>
                </div>
            <?php endif; ?>
            
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= $erro ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$mensagem): ?>
                <form method="POST" class="register-form">
                    <div class="form-group">
                        <label for="nome">Nome Completo:</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nome" name="nome" placeholder="Seu nome completo" 
                                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Seu email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha">Senha:</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha:</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Digite a senha novamente" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary btn-large">
                        <i class="fas fa-user-plus"></i> Criar Conta
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="register-footer">
                <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar ao início
                </a>
            </div>
        </div>
    </div>

    <style>
        .register-container {
            max-width: 450px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: white;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .register-header h1 i {
            color: #ff4444;
            margin-right: 10px;
        }
        
        .register-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
        }
        
        .alert-danger {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
        }
        
        .register-form {
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: white;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            color: #ff4444;
            z-index: 1;
        }
        
        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(0, 0, 0, 0.6) !important;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #ff4444;
            box-shadow: 0 0 0 3px rgba(255, 68, 68, 0.2);
            background: rgba(0, 0, 0, 0.8) !important;
        }
        
        .btn-large {
            width: 100%;
            padding: 18px;
            font-size: 1.1rem;
            background: linear-gradient(45deg, #ff4444, #cc0000) !important;
            color: white !important;
            border: none !important;
        }
        
        .btn-large:hover {
            background: linear-gradient(45deg, #cc0000, #990000) !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3);
        }
        
        .register-footer {
            text-align: center;
        }
        
        .register-footer p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 15px;
        }
        
        .register-footer a {
            color: #ff4444;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .register-footer a:hover {
            color: #cc0000;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.6) !important;
            font-size: 0.9rem;
        }
        
        .back-link:hover {
            color: white !important;
        }
        
        @media (max-width: 480px) {
            .register-container {
                margin: 20px;
                padding: 30px 20px;
            }
            
            .register-header h1 {
                font-size: 1.5rem;
            }
        }
        
        /* Força aplicação dos estilos dos inputs */
        .register-form input[type="text"],
        .register-form input[type="email"],
        .register-form input[type="password"] {
            background: rgba(0, 0, 0, 0.6) !important;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8) !important;
        }
        
        .register-form input[type="text"]:focus,
        .register-form input[type="email"]:focus,
        .register-form input[type="password"]:focus {
            background: rgba(0, 0, 0, 0.8) !important;
        }
    </style>

    <script>
        // Validação em tempo real da senha
        document.getElementById('confirmar_senha').addEventListener('input', function() {
            const senha = document.getElementById('senha').value;
            const confirmar = this.value;
            
            if (senha !== confirmar) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#28a745';
            }
        });
        
        // Validação da força da senha
        document.getElementById('senha').addEventListener('input', function() {
            const senha = this.value;
            if (senha.length < 6) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#28a745';
            }
        });
    </script>
</body>
</html>
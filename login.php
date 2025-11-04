<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/GamificacaoRefatorada.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = new User();
    
    if ($user->authenticate($_POST["email"], $_POST["senha"])) {
        $_SESSION["usuario_id"] = $user->getId();

        // Inicializar progresso do usuário usando a classe GamificacaoRefatorada
        $gamificacao = new GamificacaoRefatorada();

        // Garantir que o usuário tenha progresso inicializado
        $gamificacao->garantirProgressoUsuario($user->getId());

        // Atualizar streak
        $gamificacao->atualizarStreak($user->getId());

        header("Location: dashboard.php");
        exit;
    } else {
        $mensagem = "Email ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h1><i class="fas fa-graduation-cap"></i> Sistema de Concursos</h1>
                <p>Faça login para continuar seus estudos</p>
            </div>

            <?php if ($mensagem): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Seu email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary btn-large">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>

            <div class="login-footer">
                <p>Não tem uma conta? <a href="register.php">Cadastre-se aqui</a></p>
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar ao início
                </a>
            </div>
        </div>
    </div>

    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
        }

        .login-container form {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            backdrop-filter: blur(20px) !important;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4) !important;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: white;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .login-header h1 i {
            color: #ff4444;
            margin-right: 10px;
        }

        .login-header p {
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

        .alert-danger {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
        }

        .login-form {
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

        .login-footer {
            text-align: center;
        }

        .login-footer p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 15px;
        }

        .login-footer a {
            color: #ff4444;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
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
            .login-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }

        /* Força aplicação dos estilos dos inputs */
        .login-form input[type="email"],
        .login-form input[type="password"] {
            background: rgba(0, 0, 0, 0.6) !important;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8) !important;
        }

        .login-form input[type="email"]:focus,
        .login-form input[type="password"]:focus {
            background: rgba(0, 0, 0, 0.8) !important;
        }
    </style>
</body>

</html>
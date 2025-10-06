<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

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
        // Verificar se email já existe
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        
        if ($stmt->fetchColumn() > 0) {
            $erro = "Este email já está cadastrado.";
        } else {
            // Cadastrar usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$nome, $email, $senha_hash])) {
                $usuario_id = $pdo->lastInsertId();
                
                // Inicializar progresso do usuário
                $sql = "INSERT INTO usuarios_progresso (usuario_id) VALUES (?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario_id]);
                
                // Adicionar conquista de primeiro acesso
                $gamificacao = new Gamificacao($pdo);
                $gamificacao->adicionarPontos($usuario_id, 50, 'primeiro_acesso');
                
                $mensagem = "Cadastro realizado com sucesso! Você ganhou 50 pontos de boas-vindas!";
                
                // Fazer login automático
                $_SESSION["usuario_id"] = $usuario_id;
                
                // Redirecionar após 2 segundos
                header("refresh:2;url=dashboard.php");
            } else {
                $erro = "Erro ao cadastrar usuário. Tente novamente.";
            }
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .register-header h1 i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .register-header p {
            color: #666;
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
            background: linear-gradient(45deg, #43e97b, #38f9d7);
            color: white;
        }
        
        .alert-danger {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
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
            color: #2c3e50;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            color: #667eea;
            z-index: 1;
        }
        
        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-large {
            width: 100%;
            padding: 18px;
            font-size: 1.1rem;
        }
        
        .register-footer {
            text-align: center;
        }
        
        .register-footer p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .register-footer a:hover {
            color: #764ba2;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666 !important;
            font-size: 0.9rem;
        }
        
        .back-link:hover {
            color: #333 !important;
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
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Servidor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .success {
            color: #28a745;
            font-weight: bold;
        }

        .info {
            color: #17a2b8;
        }

        .error {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Teste de Servidor Web</h1>

        <h2>✅ Status do PHP:</h2>
        <p class="success">PHP está funcionando!</p>
        <p><strong>Versão:</strong> <?= phpversion() ?></p>
        <p><strong>Data/Hora:</strong> <?= date('d/m/Y H:i:s') ?></p>

        <h2>📁 Informações do Sistema:</h2>
        <p><strong>Diretório atual:</strong> <?= __DIR__ ?></p>
        <p><strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido' ?></p>
        <p><strong>Porta:</strong> <?= $_SERVER['SERVER_PORT'] ?? 'Desconhecida' ?></p>

        <h2>📋 Arquivos PHP encontrados:</h2>
        <ul>
            <?php
            $arquivos = glob('*.php');
            foreach ($arquivos as $arquivo) {
                echo "<li>$arquivo</li>";
            }
            ?>
        </ul>

        <h2>🔗 Teste de Conexão com Banco:</h2>
        <?php
        try {
            require 'conexao.php';
            echo '<p class="success">✅ Conexão com banco de dados OK!</p>';
        } catch (Exception $e) {
            echo '<p class="error">❌ Erro na conexão: ' . $e->getMessage() . '</p>';
        }
        ?>

        <h2>🚀 Próximos Passos:</h2>
        <p>Se você está vendo esta página, o servidor está funcionando!</p>
        <p>Agora você pode tentar acessar:</p>
        <ul>
            <li><a href="instalar_exercicios.php">instalar_exercicios.php</a></li>
            <li><a href="login.php">login.php</a></li>
            <li><a href="dashboard.php">dashboard.php</a></li>
        </ul>
    </div>
</body>

</html>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'RCP - Sistema de Concursos' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="/css/concurso.png" type="image/png">
</head>
<body>
    <?php 
    // Exibir mensagens flash
    if (isset($_SESSION['flash'])): 
        foreach ($_SESSION['flash'] as $type => $message): 
    ?>
        <div class="flash-message flash-<?= $type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php 
        endforeach;
        unset($_SESSION['flash']);
    endif; 
    ?>

    <?= $content ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Auto-dismiss flash messages
        $(document).ready(function() {
            $('.flash-message').fadeIn().delay(3000).fadeOut();
        });
    </script>
</body>
</html>


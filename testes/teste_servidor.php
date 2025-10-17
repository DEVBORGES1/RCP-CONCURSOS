<?php
echo "✅ Servidor PHP funcionando!<br>";
echo "📅 Data atual: " . date('d/m/Y H:i:s') . "<br>";
echo "🌐 Diretório atual: " . __DIR__ . "<br>";
echo "📁 Arquivos PHP encontrados:<br>";

$arquivos = glob('*.php');
foreach ($arquivos as $arquivo) {
    echo "- $arquivo<br>";
}
?>

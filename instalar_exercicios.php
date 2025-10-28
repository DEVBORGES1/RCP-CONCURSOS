<?php
echo "🚀 Instalador de Exercícios e Simulados\n";
echo "=====================================\n\n";

// Verificar se a conexão está funcionando
try {
    require 'conexao.php';
    echo "✅ Conexão com banco de dados estabelecida\n";
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
    exit;
}

echo "\n📚 Passo 1: Inserindo exercícios no banco de dados...\n";
echo "----------------------------------------------------\n";

// Executar script de inserção de exercícios
ob_start();
include 'adicionar_exercicios.php';
$output1 = ob_get_clean();
echo $output1;

echo "\n🎯 Passo 2: Criando simulados pré-definidos...\n";
echo "--------------------------------------------\n";

// Executar script de criação de simulados
ob_start();
include 'criar_simulados.php';
$output2 = ob_get_clean();
echo $output2;

echo "\n🎉 Instalação concluída com sucesso!\n";
echo "=====================================\n\n";

echo "📋 Resumo do que foi criado:\n";
echo "- ✅ 7 disciplinas diferentes\n";
echo "- ✅ 30 questões de exercícios\n";
echo "- ✅ 5 simulados pré-definidos\n";
echo "- ✅ Sistema pronto para uso\n\n";

echo "🔗 Próximos passos:\n";
echo "1. Acesse o sistema de login\n";
echo "2. Faça login com sua conta\n";
echo "3. Vá para a seção 'Simulados'\n";
echo "4. Escolha um dos simulados criados\n";
echo "5. Comece a estudar!\n\n";

echo "💡 Dicas:\n";
echo "- Os simulados são criados com questões aleatórias\n";
echo "- Cada simulado tem um foco diferente\n";
echo "- Você pode criar seus próprios simulados personalizados\n";
echo "- O sistema de gamificação está ativo para motivar seus estudos\n";
?>

<?php
require 'conexao.php';

echo "🔧 CORREÇÃO DOS SIMULADOS (SEM SESSÃO)\n";
echo "======================================\n\n";

// Simular usuário ID 1 (primeiro usuário)
$usuario_id = 1;

try {
    // 1. Verificar questões disponíveis
    echo "1. Verificando questões disponíveis...\n";
    $sql = "SELECT COUNT(*) as total FROM questoes WHERE edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $total_questoes = $stmt->fetchColumn();
    echo "✅ $total_questoes questões encontradas\n\n";
    
    // 2. Limpar simulados existentes
    echo "2. Limpando simulados existentes...\n";
    $sql = "DELETE sq FROM simulados_questoes sq 
            JOIN simulados s ON sq.simulado_id = s.id 
            WHERE s.usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    
    $sql = "DELETE FROM simulados WHERE usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    echo "✅ Simulados limpos\n\n";
    
    // 3. Verificar disciplinas disponíveis
    echo "3. Verificando disciplinas...\n";
    $sql = "SELECT DISTINCT d.nome_disciplina, COUNT(q.id) as total 
            FROM disciplinas d 
            LEFT JOIN questoes q ON d.id = q.disciplina_id 
            WHERE d.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)
            GROUP BY d.nome_disciplina 
            ORDER BY total DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $disciplinas = $stmt->fetchAll();
    
    foreach ($disciplinas as $disciplina) {
        echo "   - {$disciplina['nome_disciplina']}: {$disciplina['total']} questões\n";
    }
    echo "\n";
    
    // 4. Criar simulados pré-definidos
    echo "4. Criando simulados pré-definidos...\n";
    
    $simulados_config = [
        'Simulado Geral Básico' => [
            'quantidade' => 15,
            'disciplinas' => null
        ],
        'Simulado Português e Matemática' => [
            'quantidade' => 12,
            'disciplinas' => ['Português', 'Matemática']
        ],
        'Simulado Conhecimentos Específicos' => [
            'quantidade' => 10,
            'disciplinas' => ['Direito', 'Administração', 'Atualidades']
        ],
        'Simulado Raciocínio e Informática' => [
            'quantidade' => 10,
            'disciplinas' => ['Raciocínio Lógico', 'Informática']
        ],
        'Simulado Completo' => [
            'quantidade' => 30,
            'disciplinas' => null
        ]
    ];
    
    foreach ($simulados_config as $nome => $config) {
        // Criar simulado
        $sql = "INSERT INTO simulados (usuario_id, nome, questoes_total) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $nome, $config['quantidade']]);
        $simulado_id = $pdo->lastInsertId();
        
        // Selecionar questões
        $where_clause = "";
        $params = [];
        
        if ($config['disciplinas']) {
            $placeholders = str_repeat('?,', count($config['disciplinas']) - 1) . '?';
            $where_clause = "WHERE d.nome_disciplina IN ($placeholders)";
            $params = $config['disciplinas'];
        }
        
        $sql = "SELECT DISTINCT q.* FROM questoes q 
                LEFT JOIN disciplinas d ON q.disciplina_id = d.id 
                $where_clause
                AND q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)
                ORDER BY RAND() LIMIT " . $config['quantidade'];
        $params[] = $usuario_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $questoes_selecionadas = $stmt->fetchAll();
        
        // Se não há questões suficientes, pegar todas as disponíveis
        if (count($questoes_selecionadas) < $config['quantidade']) {
            $sql = "SELECT DISTINCT q.* FROM questoes q 
                    WHERE q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)
                    ORDER BY RAND() LIMIT " . $config['quantidade'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $questoes_selecionadas = $stmt->fetchAll();
        }
        
        // Adicionar questões ao simulado
        $questoes_adicionadas = 0;
        $questoes_ja_adicionadas = [];
        foreach ($questoes_selecionadas as $questao) {
            if (!in_array($questao['id'], $questoes_ja_adicionadas)) {
                $sql = "INSERT INTO simulados_questoes (simulado_id, questao_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$simulado_id, $questao['id']]);
                $questoes_ja_adicionadas[] = $questao['id'];
                $questoes_adicionadas++;
            }
        }
        
        echo "   ✅ $nome: $questoes_adicionadas questões adicionadas\n";
    }
    
    echo "\n";
    
    // 5. Verificação final
    echo "5. Verificação final...\n";
    $sql = "SELECT s.nome, s.questoes_total, COUNT(sq.questao_id) as questoes_adicionadas 
            FROM simulados s 
            LEFT JOIN simulados_questoes sq ON s.id = sq.simulado_id 
            WHERE s.usuario_id = ? 
            GROUP BY s.id, s.nome, s.questoes_total";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $simulados = $stmt->fetchAll();
    
    foreach ($simulados as $simulado) {
        echo "   - {$simulado['nome']}: {$simulado['questoes_adicionadas']}/{$simulado['questoes_total']} questões\n";
    }
    
    echo "\n🎉 CORREÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "💡 Agora você pode acessar os simulados pré-definidos normalmente.\n";
    echo "🔗 Acesse: http://localhost/RCP-CONCURSOS/simulados.php\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>


<?php
require 'conexao.php';

// Função para criar um simulado
function criarSimulado($nome, $disciplinas_questoes, $usuario_id = null)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Se não foi fornecido usuário, buscar o primeiro disponível
        if (!$usuario_id) {
            $sql = "SELECT id FROM usuarios LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $usuario_result = $stmt->fetch();
            $usuario_id = $usuario_result ? $usuario_result['id'] : 1;
        }

        // Criar o simulado
        $sql = "INSERT INTO simulados (usuario_id, nome, questoes_total, data_criacao) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $total_questoes = array_sum($disciplinas_questoes);
        $stmt->execute([$usuario_id, $nome, $total_questoes]);
        $simulado_id = $pdo->lastInsertId();

        // Adicionar questões ao simulado
        foreach ($disciplinas_questoes as $disciplina_nome => $quantidade) {
            // Buscar disciplina
            $sql = "SELECT id FROM disciplinas WHERE nome_disciplina = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$disciplina_nome]);
            $disciplina_result = $stmt->fetch();

            if (!$disciplina_result) {
                echo "Aviso: Disciplina '$disciplina_nome' não encontrada. Pulando...\n";
                continue;
            }

            $disciplina_id = $disciplina_result['id'];

            // Buscar questões aleatórias da disciplina
            $sql = "SELECT id FROM questoes WHERE disciplina_id = ? ORDER BY RAND() LIMIT " . (int)$quantidade;
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$disciplina_id]);
            $questoes = $stmt->fetchAll();

            // Adicionar questões ao simulado
            foreach ($questoes as $questao) {
                $sql = "INSERT INTO simulados_questoes (simulado_id, questao_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$simulado_id, $questao['id']]);
            }
        }

        $pdo->commit();
        return $simulado_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

try {
    echo "Criando simulados pré-definidos...\n";

    // Simulado 1: Geral Básico (todas as disciplinas)
    $simulado1_id = criarSimulado(
        "Simulado Geral Básico",
        [
            'Língua Portuguesa' => 3,
            'Matemática' => 3,
            'Raciocínio Lógico' => 2,
            'Informática' => 2,
            'Direito Administrativo e Constitucional' => 2,
            'Atualidades e Cidadania' => 2,
            'Administração Pública e Ética' => 1
        ]
    );
    echo "✅ Simulado Geral Básico criado (ID: $simulado1_id)\n";

    // Simulado 2: Foco em Português e Matemática
    $simulado2_id = criarSimulado(
        "Simulado Português e Matemática",
        [
            'Língua Portuguesa' => 5,
            'Matemática' => 5,
            'Raciocínio Lógico' => 2
        ]
    );
    echo "✅ Simulado Português e Matemática criado (ID: $simulado2_id)\n";

    // Simulado 3: Conhecimentos Específicos
    $simulado3_id = criarSimulado(
        "Simulado Conhecimentos Específicos",
        [
            'Direito Administrativo e Constitucional' => 5,
            'Administração Pública e Ética' => 2,
            'Atualidades e Cidadania' => 3
        ]
    );
    echo "✅ Simulado Conhecimentos Específicos criado (ID: $simulado3_id)\n";

    // Simulado 4: Raciocínio e Informática
    $simulado4_id = criarSimulado(
        "Simulado Raciocínio e Informática",
        [
            'Raciocínio Lógico' => 5,
            'Informática' => 5
        ]
    );
    echo "✅ Simulado Raciocínio e Informática criado (ID: $simulado4_id)\n";

    // Simulado 5: Completo (todas as questões disponíveis)
    $simulado5_id = criarSimulado(
        "Simulado Completo",
        [
            'Língua Portuguesa' => 5,
            'Matemática' => 5,
            'Raciocínio Lógico' => 5,
            'Informática' => 5,
            'Direito Administrativo e Constitucional' => 5,
            'Atualidades e Cidadania' => 3,
            'Administração Pública e Ética' => 2
        ]
    );
    echo "✅ Simulado Completo criado (ID: $simulado5_id)\n";

    echo "\n🎉 Todos os simulados foram criados com sucesso!\n";

    // Mostrar estatísticas
    $sql = "SELECT s.id, s.nome, s.questoes_total, COUNT(sq.questao_id) as questoes_adicionadas 
            FROM simulados s 
            LEFT JOIN simulados_questoes sq ON s.id = sq.simulado_id 
            WHERE s.usuario_id = 1 
            GROUP BY s.id, s.nome, s.questoes_total";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $simulados = $stmt->fetchAll();

    echo "\n📊 Estatísticas dos Simulados:\n";
    foreach ($simulados as $simulado) {
        echo "- {$simulado['nome']}: {$simulado['questoes_adicionadas']}/{$simulado['questoes_total']} questões\n";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>

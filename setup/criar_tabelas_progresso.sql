-- Script para criar as tabelas do sistema de progresso avançado
-- Execute este script no MySQL para criar as tabelas necessárias

USE concursos;

-- Tabela de metas personalizadas
CREATE TABLE IF NOT EXISTS metas_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    titulo VARCHAR(200),
    descricao TEXT,
    tipo ENUM('questoes', 'taxa_acerto', 'streak', 'simulados', 'disciplina', 'personalizada'),
    valor_meta INT,
    valor_atual INT DEFAULT 0,
    data_inicio DATE,
    data_fim DATE,
    pontos_recompensa INT DEFAULT 0,
    ativa BOOLEAN DEFAULT TRUE,
    concluida BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de badges dinâmicas
CREATE TABLE IF NOT EXISTS badges_dinamicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    descricao TEXT,
    icone VARCHAR(50),
    categoria ENUM('performance', 'consistencia', 'conquista', 'especial'),
    criterios JSON,
    pontos_recompensa INT DEFAULT 0,
    raridade ENUM('comum', 'rara', 'epica', 'lendaria') DEFAULT 'comum',
    ativa BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de badges conquistadas pelos usuários
CREATE TABLE IF NOT EXISTS usuarios_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    badge_id INT,
    data_conquista TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pontos_ganhos INT DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (badge_id) REFERENCES badges_dinamicas(id),
    UNIQUE KEY unique_user_badge (usuario_id, badge_id)
);

-- Tabela de progresso por disciplina
CREATE TABLE IF NOT EXISTS progresso_disciplina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    disciplina_id INT,
    questoes_respondidas INT DEFAULT 0,
    questoes_corretas INT DEFAULT 0,
    taxa_acerto DECIMAL(5,2) DEFAULT 0,
    pontos_total INT DEFAULT 0,
    nivel_dominio INT DEFAULT 1,
    ultimo_estudo TIMESTAMP NULL,
    dias_estudados INT DEFAULT 0,
    tempo_total_minutos INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id),
    UNIQUE KEY unique_user_discipline (usuario_id, disciplina_id)
);

-- Tabela de sessões de estudo
CREATE TABLE IF NOT EXISTS sessoes_estudo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    disciplina_id INT NULL,
    tipo ENUM('questao_individual', 'simulado', 'revisao', 'cronograma'),
    inicio_sessao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fim_sessao TIMESTAMP NULL,
    duracao_minutos INT DEFAULT 0,
    questoes_respondidas INT DEFAULT 0,
    questoes_corretas INT DEFAULT 0,
    pontos_ganhos INT DEFAULT 0,
    nivel_dificuldade ENUM('facil', 'medio', 'dificil') DEFAULT 'medio',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)
);

-- Tabela de insights e recomendações
CREATE TABLE IF NOT EXISTS insights_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo_insight ENUM('performance', 'consistencia', 'disciplina', 'meta', 'recomendacao'),
    titulo VARCHAR(200),
    mensagem TEXT,
    dados_suporte JSON,
    prioridade ENUM('baixa', 'media', 'alta') DEFAULT 'media',
    visualizado BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de comparações e benchmarking
CREATE TABLE IF NOT EXISTS comparacoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo_comparacao ENUM('ranking_geral', 'ranking_disciplina', 'ranking_nivel', 'ranking_tempo'),
    posicao_atual INT,
    total_participantes INT,
    percentil DECIMAL(5,2),
    dados_comparacao JSON,
    data_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de histórico de progresso
CREATE TABLE IF NOT EXISTS historico_progresso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    data_registro DATE,
    pontos_dia INT DEFAULT 0,
    questoes_respondidas INT DEFAULT 0,
    questoes_corretas INT DEFAULT 0,
    taxa_acerto DECIMAL(5,2) DEFAULT 0,
    tempo_estudo_minutos INT DEFAULT 0,
    simulados_completos INT DEFAULT 0,
    streak_dias INT DEFAULT 0,
    nivel_atual INT DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY unique_user_date (usuario_id, data_registro)
);

-- Inserir badges dinâmicas iniciais
INSERT IGNORE INTO badges_dinamicas (nome, descricao, icone, categoria, criterios, pontos_recompensa, raridade) VALUES
('Primeiro Passo', 'Responda sua primeira questão', '👶', 'conquista', '{"questoes_respondidas": 1}', 10, 'comum'),
('Iniciante Dedicado', 'Responda 10 questões', '🌟', 'performance', '{"questoes_respondidas": 10}', 25, 'comum'),
('Estudioso', 'Responda 50 questões', '📚', 'performance', '{"questoes_respondidas": 50}', 100, 'rara'),
('Expert', 'Responda 100 questões', '🏆', 'performance', '{"questoes_respondidas": 100}', 250, 'rara'),
('Mestre', 'Responda 500 questões', '👑', 'performance', '{"questoes_respondidas": 500}', 1000, 'epica'),
('Perfeccionista', 'Mantenha 90% de acerto por 7 dias', '💯', 'performance', '{"taxa_acerto": 90, "dias_consecutivos": 7}', 500, 'epica'),
('Streak de Fogo', 'Estude 7 dias seguidos', '🔥', 'consistencia', '{"streak_dias": 7}', 100, 'rara'),
('Maratonista', 'Estude 30 dias seguidos', '🏃', 'consistencia', '{"streak_dias": 30}', 500, 'epica'),
('Disciplinado', 'Complete 5 simulados', '📝', 'performance', '{"simulados_completos": 5}', 200, 'rara'),
('Estrategista', 'Alcance nível 10', '⭐', 'conquista', '{"nivel": 10}', 750, 'epica'),
('Lenda', 'Responda 1000 questões', '🏅', 'performance', '{"questoes_respondidas": 1000}', 2000, 'lendaria'),
('Domador de Disciplinas', 'Domine 5 disciplinas diferentes', '🎯', 'performance', '{"disciplinas_dominadas": 5}', 300, 'epica');

-- Criar índices para performance
CREATE INDEX IF NOT EXISTS idx_progresso_disciplina_usuario ON progresso_disciplina(usuario_id);
CREATE INDEX IF NOT EXISTS idx_sessoes_estudo_usuario ON sessoes_estudo(usuario_id);
CREATE INDEX IF NOT EXISTS idx_historico_progresso_usuario ON historico_progresso(usuario_id);
CREATE INDEX IF NOT EXISTS idx_insights_usuario ON insights_usuario(usuario_id, visualizado);
CREATE INDEX IF NOT EXISTS idx_metas_usuario ON metas_usuario(usuario_id, ativa);
CREATE INDEX IF NOT EXISTS idx_usuarios_badges_usuario ON usuarios_badges(usuario_id);

-- Mensagem de sucesso
SELECT 'Tabelas do sistema de progresso avançado criadas com sucesso!' as resultado;

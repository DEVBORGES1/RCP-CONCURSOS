-- =====================================================
-- RCP-CONCURSOS - BANCO DE DADOS COMPLETO
-- Sistema de Concursos - Plataforma Gamificada de Estudos
-- Versão 2.0 POO
-- =====================================================

CREATE DATABASE IF NOT EXISTS concursos;
USE concursos;

-- =====================================================
-- TABELAS PRINCIPAIS
-- =====================================================

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Tabela de Editais
CREATE TABLE IF NOT EXISTS editais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_arquivo VARCHAR(255),
    texto_extraido LONGTEXT,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id)
);

-- Tabela de Disciplinas
CREATE TABLE IF NOT EXISTS disciplinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edital_id INT NOT NULL,
    nome_disciplina VARCHAR(150) NOT NULL,
    FOREIGN KEY (edital_id) REFERENCES editais(id) ON DELETE CASCADE,
    INDEX idx_edital (edital_id)
);

-- Tabela de Cronogramas
CREATE TABLE IF NOT EXISTS cronogramas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    edital_id INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    horas_por_dia INT DEFAULT 2,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (edital_id) REFERENCES editais(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id)
);

-- Tabela de Cronograma Detalhado
CREATE TABLE IF NOT EXISTS cronograma_detalhado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cronograma_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    data_estudo DATE NOT NULL,
    horas_previstas DECIMAL(3,1) DEFAULT 2.0,
    horas_realizadas DECIMAL(3,1) DEFAULT 0,
    concluido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (cronograma_id) REFERENCES cronogramas(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
    INDEX idx_cronograma (cronograma_id),
    INDEX idx_disciplina (disciplina_id)
);

-- Tabela de Questões
CREATE TABLE IF NOT EXISTS questoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edital_id INT NOT NULL,
    disciplina_id INT,
    enunciado TEXT NOT NULL,
    alternativa_a VARCHAR(255) NOT NULL,
    alternativa_b VARCHAR(255) NOT NULL,
    alternativa_c VARCHAR(255) NOT NULL,
    alternativa_d VARCHAR(255) NOT NULL,
    alternativa_e VARCHAR(255) NOT NULL,
    alternativa_correta CHAR(1) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (edital_id) REFERENCES editais(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE SET NULL,
    INDEX idx_edital (edital_id),
    INDEX idx_disciplina (disciplina_id)
);

-- Tabela de Respostas do Usuário
CREATE TABLE IF NOT EXISTS respostas_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    questao_id INT NOT NULL,
    resposta CHAR(1) NOT NULL,
    correta BOOLEAN NOT NULL,
    pontos_ganhos INT DEFAULT 0,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (questao_id) REFERENCES questoes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_question (usuario_id, questao_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_questao (questao_id),
    INDEX idx_data (data_resposta)
);

-- =====================================================
-- SISTEMA DE GAMIFICAÇÃO
-- =====================================================

-- Tabela de Progresso do Usuário
CREATE TABLE IF NOT EXISTS usuarios_progresso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE NOT NULL,
    nivel INT DEFAULT 1,
    pontos_total INT DEFAULT 0,
    streak_dias INT DEFAULT 0,
    ultimo_login DATE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_nivel (nivel),
    INDEX idx_pontos (pontos_total)
);

-- Tabela de Conquistas
CREATE TABLE IF NOT EXISTS conquistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    icone VARCHAR(50) NOT NULL,
    pontos_necessarios INT NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- 'questoes', 'streak', 'nivel', 'simulado'
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo)
);

-- Tabela de Conquistas do Usuário
CREATE TABLE IF NOT EXISTS usuarios_conquistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    conquista_id INT NOT NULL,
    data_conquista TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (conquista_id) REFERENCES conquistas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_achievement (usuario_id, conquista_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_conquista (conquista_id)
);

-- Tabela de Ranking Mensal
CREATE TABLE IF NOT EXISTS ranking_mensal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mes_ano VARCHAR(7) NOT NULL, -- formato: 2024-01
    pontos_mes INT DEFAULT 0,
    posicao INT,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_month (usuario_id, mes_ano),
    INDEX idx_mes_ano (mes_ano),
    INDEX idx_posicao (posicao)
);

-- =====================================================
-- SISTEMA DE SIMULADOS
-- =====================================================

-- Tabela de Simulados
CREATE TABLE IF NOT EXISTS simulados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    questoes_total INT NOT NULL,
    questoes_corretas INT DEFAULT 0,
    pontuacao_final INT DEFAULT 0,
    tempo_gasto INT DEFAULT 0, -- em minutos
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_data_criacao (data_criacao)
);

-- Tabela de Questões do Simulado
CREATE TABLE IF NOT EXISTS simulados_questoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    simulado_id INT NOT NULL,
    questao_id INT NOT NULL,
    resposta_usuario CHAR(1),
    correta BOOLEAN,
    FOREIGN KEY (simulado_id) REFERENCES simulados(id) ON DELETE CASCADE,
    FOREIGN KEY (questao_id) REFERENCES questoes(id) ON DELETE CASCADE,
    INDEX idx_simulado (simulado_id),
    INDEX idx_questao (questao_id)
);

-- =====================================================
-- SISTEMA DE PROGRESSO AVANÇADO (OPCIONAL)
-- =====================================================

-- Tabela de Metas Personalizadas
CREATE TABLE IF NOT EXISTS metas_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    tipo ENUM('questoes', 'taxa_acerto', 'streak', 'simulados', 'disciplina', 'personalizada') NOT NULL,
    valor_meta INT NOT NULL,
    valor_atual INT DEFAULT 0,
    data_inicio DATE,
    data_fim DATE,
    pontos_recompensa INT DEFAULT 0,
    ativa BOOLEAN DEFAULT TRUE,
    concluida BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_ativa (usuario_id, ativa)
);

-- Tabela de Progresso por Disciplina
CREATE TABLE IF NOT EXISTS progresso_disciplina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    disciplina_id INT NOT NULL,
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
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_discipline (usuario_id, disciplina_id),
    INDEX idx_usuario (usuario_id)
);

-- Tabela de Sessões de Estudo
CREATE TABLE IF NOT EXISTS sessoes_estudo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    disciplina_id INT,
    tipo ENUM('questao_individual', 'simulado', 'revisao', 'cronograma') NOT NULL,
    inicio_sessao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fim_sessao TIMESTAMP NULL,
    duracao_minutos INT DEFAULT 0,
    questoes_respondidas INT DEFAULT 0,
    questoes_corretas INT DEFAULT 0,
    pontos_ganhos INT DEFAULT 0,
    nivel_dificuldade ENUM('facil', 'medio', 'dificil') DEFAULT 'medio',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id)
);

-- Tabela de Histórico de Progresso
CREATE TABLE IF NOT EXISTS historico_progresso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_registro DATE NOT NULL,
    pontos_dia INT DEFAULT 0,
    questoes_respondidas INT DEFAULT 0,
    questoes_corretas INT DEFAULT 0,
    taxa_acerto DECIMAL(5,2) DEFAULT 0,
    tempo_estudo_minutos INT DEFAULT 0,
    simulados_completos INT DEFAULT 0,
    streak_dias INT DEFAULT 0,
    nivel_atual INT DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (usuario_id, data_registro),
    INDEX idx_usuario_data (usuario_id, data_registro)
);

-- =====================================================
-- SISTEMA DE VIDEOAULAS
-- =====================================================

-- Tabela de Categorias de Videoaulas
CREATE TABLE IF NOT EXISTS videoaulas_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    cor VARCHAR(20) DEFAULT '#667eea',
    icone VARCHAR(50),
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo),
    INDEX idx_ordem (ordem)
);

-- Tabela de Videoaulas
CREATE TABLE IF NOT EXISTS videoaulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    url_video TEXT NOT NULL,
    duracao INT DEFAULT 0, -- em minutos
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    visualizacoes INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES videoaulas_categorias(id) ON DELETE CASCADE,
    INDEX idx_categoria (categoria_id),
    INDEX idx_ativo (ativo)
);

-- Tabela de Progresso de Videoaulas
CREATE TABLE IF NOT EXISTS videoaulas_progresso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    videoaula_id INT NOT NULL,
    tempo_assistido INT DEFAULT 0, -- em segundos
    concluida BOOLEAN DEFAULT FALSE,
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (videoaula_id) REFERENCES videoaulas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_video (usuario_id, videoaula_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_videoaula (videoaula_id)
);

-- =====================================================
-- DADOS INICIAIS
-- =====================================================

-- Inserir conquistas padrão
INSERT IGNORE INTO conquistas (nome, descricao, icone, pontos_necessarios, tipo) VALUES
('Primeira Questão', 'Responda sua primeira questão', '🎯', 10, 'questoes'),
('Iniciante', 'Responda 10 questões', '🌟', 100, 'questoes'),
('Estudioso', 'Responda 50 questões', '📚', 500, 'questoes'),
('Expert', 'Responda 100 questões', '🏆', 1000, 'questoes'),
('Mestre', 'Responda 500 questões', '👑', 5000, 'questoes'),
('Streak 3', 'Estude 3 dias seguidos', '🔥', 50, 'streak'),
('Streak 7', 'Estude 7 dias seguidos', '🔥🔥', 200, 'streak'),
('Streak 30', 'Estude 30 dias seguidos', '🔥🔥🔥', 1000, 'streak'),
('Nível 5', 'Alcance o nível 5', '⭐', 250, 'nivel'),
('Nível 10', 'Alcance o nível 10', '⭐⭐', 750, 'nivel'),
('Simulador', 'Complete seu primeiro simulado', '📝', 100, 'simulado'),
('Perfeccionista', 'Acerte 100% em um simulado', '💯', 500, 'simulado');

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

CREATE INDEX IF NOT EXISTS idx_conquistas_tipo ON conquistas(tipo);
CREATE INDEX IF NOT EXISTS idx_progresso_usuario ON usuarios_progresso(usuario_id);
CREATE INDEX IF NOT EXISTS idx_ranking_usuario_mes ON ranking_mensal(usuario_id, mes_ano);

-- =====================================================
-- MENSAGEM DE SUCESSO
-- =====================================================

SELECT 'Banco de dados RCP-CONCURSOS criado com sucesso!' as resultado;
SELECT COUNT(*) as 'Total de Tabelas Criadas' FROM information_schema.tables WHERE table_schema = 'concursos';


-- =====================================================
-- ADICIONAR TABELAS DE VIDEOAULAS
-- Execute este script se você já tem o banco criado
-- =====================================================

USE concursos;

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
    duracao INT DEFAULT 0,
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
    tempo_assistido INT DEFAULT 0,
    concluida BOOLEAN DEFAULT FALSE,
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (videoaula_id) REFERENCES videoaulas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_video (usuario_id, videoaula_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_videoaula (videoaula_id)
);

SELECT 'Tabelas de videoaulas criadas com sucesso!' as resultado;



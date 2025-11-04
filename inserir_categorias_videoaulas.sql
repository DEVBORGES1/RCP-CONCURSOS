-- =====================================================
-- INSERIR CATEGORIAS/DISCIPLINAS PADRÃO PARA VIDEOAULAS
-- Execute este script para criar as categorias pré-definidas
-- =====================================================

USE concursos;

-- Inserir categorias/disciplinas padrão para concursos
INSERT IGNORE INTO videoaulas_categorias (nome, descricao, cor, icone, ordem, ativo) VALUES
('Português', 'Língua Portuguesa - Gramática, Interpretação de Texto, Redação', '#3498db', 'fas fa-book', 1, 1),
('Matemática', 'Matemática - Álgebra, Geometria, Trigonometria, Estatística', '#e74c3c', 'fas fa-calculator', 2, 1),
('Raciocínio Lógico', 'Raciocínio Lógico e Quantitativo', '#9b59b6', 'fas fa-brain', 3, 1),
('Direito Constitucional', 'Direito Constitucional e Legislação', '#16a085', 'fas fa-gavel', 4, 1),
('Direito Administrativo', 'Direito Administrativo e Licitações', '#27ae60', 'fas fa-landmark', 5, 1),
('Direito Penal', 'Direito Penal e Processo Penal', '#c0392b', 'fas fa-balance-scale', 6, 1),
('Direito Civil', 'Direito Civil e Processo Civil', '#2980b9', 'fas fa-scroll', 7, 1),
('Direito do Trabalho', 'Direito do Trabalho e Processo do Trabalho', '#f39c12', 'fas fa-briefcase', 8, 1),
('Direito Tributário', 'Direito Tributário e Fiscal', '#e67e22', 'fas fa-file-invoice-dollar', 9, 1),
('Informática', 'Informática Básica e Avançada', '#1abc9c', 'fas fa-laptop-code', 10, 1),
('Atualidades', 'Atualidades e Conhecimentos Gerais', '#34495e', 'fas fa-newspaper', 11, 1),
('Administração Pública', 'Administração Pública e Gestão', '#95a5a6', 'fas fa-building', 12, 1),
('Legislação Específica', 'Legislação Específica do Cargo', '#2c3e50', 'fas fa-file-alt', 13, 1),
('Noções de Gestão', 'Noções de Gestão Pública e Organizacional', '#7f8c8d', 'fas fa-chart-line', 14, 1),
('Ética', 'Ética no Serviço Público', '#8e44ad', 'fas fa-hands-helping', 15, 1);

SELECT 'Categorias de videoaulas inseridas com sucesso!' as resultado;
SELECT COUNT(*) as total_categorias FROM videoaulas_categorias WHERE ativo = 1;


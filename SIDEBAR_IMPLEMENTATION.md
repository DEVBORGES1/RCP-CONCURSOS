# Implementação da Sidebar - Sistema RCP Concursos

## Visão Geral

A sidebar foi implementada para fornecer acesso rápido a todas as páginas do sistema, mantendo uma navegação consistente e intuitiva. A sidebar é fixa à esquerda, com rolagem vertical e largura definida por variáveis CSS.

## Estrutura da Sidebar

### Variáveis CSS
```css
:root {
    --sidebar-width: 280px;           /* Largura da sidebar em desktop */
    --sidebar-width-mobile: 250px;    /* Largura da sidebar em mobile */
    --content-margin: 20px;           /* Margem do conteúdo */
}
```

### Seções da Sidebar
1. **Navegação**: Dashboard, Perfil, Simulados
2. **Estudos**: Banco de Questões, Videoaulas, Meus Editais
3. **Ferramentas**: Upload Edital, Gerar Cronograma, Dashboard Avançado
4. **Conta**: Sair

## Como Implementar em Outras Páginas

### 1. Estrutura HTML
```html
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-graduation-cap"></i> RCP Concursos</h2>
            <p>Sistema de Estudos</p>
        </div>
        <div class="sidebar-nav">
            <!-- Seções de navegação aqui -->
        </div>
    </nav>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="content-with-sidebar">
        <div class="container">
            <!-- Conteúdo da página aqui -->
        </div>
    </div>

    <script>
        // JavaScript da sidebar aqui
    </script>
</body>
```

### 2. JavaScript Obrigatório
```javascript
// Sidebar mobile toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
        
        // Fechar sidebar ao clicar fora dela em mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }
});
```

### 3. Marcar Item Ativo
Para destacar a página atual na sidebar, adicione a classe `active` ao item correspondente:
```html
<a href="dashboard.php" class="nav-item active">
    <i class="fas fa-home"></i>
    <span>Dashboard</span>
</a>
```

## Características da Sidebar

### Desktop
- Largura fixa de 280px
- Sempre visível
- Conteúdo principal com margem esquerda de 280px

### Mobile
- Largura de 250px (ou 100% em telas muito pequenas)
- Ocultada por padrão
- Botão toggle para abrir/fechar
- Fecha automaticamente ao clicar fora

### Responsividade
- **> 768px**: Sidebar sempre visível
- **≤ 768px**: Sidebar oculta com toggle
- **≤ 480px**: Sidebar ocupa 100% da largura

## Páginas Já Implementadas
- ✅ `simulado.php`
- ✅ `dashboard.php`
- ✅ `perfil.php` (nova página criada)

## Páginas que Precisam da Sidebar
- `simulados.php`
- `questoes.php`
- `videoaulas.php`
- `editais.php`
- `upload_edital.php`
- `gerar_cronograma.php`
- `dashboard_avancado.php`

## Página de Perfil

A página `perfil.php` foi criada com as seguintes funcionalidades:

### Informações Pessoais
- Nome do usuário
- Nível e pontos
- Sequência de dias seguidos

### Estatísticas Principais
- Posição no ranking
- Melhor pontuação em simulado
- Maior sequência de dias seguidos
- Total de certificados

### Estatísticas Detalhadas
- Questões respondidas
- Taxa de acerto
- Simulados realizados
- Pontos totais

### Conquistas
- Lista de conquistas desbloqueadas e bloqueadas
- Data de conquista
- Pontos necessários para desbloquear

### Certificados
- Certificados de videoaulas assistidas
- Título e categoria da videoaula
- Data de conclusão

## Estilos CSS

Todos os estilos da sidebar estão no arquivo `css/style.css` e incluem:
- Layout responsivo
- Animações suaves
- Scrollbar customizada
- Estados hover e active
- Transições para mobile

## Considerações de Acessibilidade

- Navegação por teclado
- Contraste adequado
- Ícones descritivos
- Estrutura semântica
- Responsividade completa

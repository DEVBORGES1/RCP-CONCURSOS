# 📁 Estrutura do Projeto - RCP Sistema de Concursos

## 📊 Visão Geral da Organização

O projeto foi reorganizado seguindo princípios de **arquitetura limpa** e **separação de responsabilidades**.

---

## 🎯 Estrutura de Pastas

```
RCP-CONCURSOS/
│
├── 📱 app/                    # Código da aplicação (MVC)
│   ├── Controllers/           # Controladores
│   ├── Models/                # Modelos de dados
│   ├── Views/                 # Templates/Páginas
│   ├── Core/                  # Classes base
│   └── Services/              # Lógica de negócio
│
├── 📚 docs/                   # Documentação
│   ├── ARQUITETURA_MVC.md
│   ├── README_MIGRACAO.md
│   ├── README_TECNICO.md
│   └── GUIA_COMPLETO_MIGRACAO.md
│
├── ⚙️ config/                 # Configurações
│   ├── config.php
│   └── database.php
│
├── 🗄️ classes/                # Classes antigas (em migração)
│   ├── AnalisadorEdital.php
│   ├── Gamificacao.php
│   └── ...
│
├── 📦 old_code/              # Código antigo (para migração)
│   ├── dashboard.php
│   ├── questoes.php
│   └── ...
│
├── 🔧 setup/                  # Scripts de instalação
│   ├── instalar_*.php
│   └── criar_tabelas_*.sql
│
├── 📦 archive/               # Arquivos arquivados
│   ├── teste/
│   ├── debug/
│   └── ...
│
├── 🎨 css/                   # Estilos
├── 📤 uploads/               # Uploads de usuários
├── 🌐 public/                # Arquivos públicos (a configurar)
│
├── index.php                 # Sistema atual (legacy)
├── mvc_index.php             # Sistema MVC (novo)
├── bootstrap.php             # Bootstrap da aplicação MVC
├── conexao.php               # Conexão antiga (deprecado)
└── composer.json            # Autoloading
```

---

## 📂 Descrição Detalhada

### 📱 app/ - Código da Aplicação

**Status**: ✅ Nova arquitetura MVC implementada

#### Controllers/
- `AuthController.php` - Login, registro, logout
- `DashboardController.php` - Dashboard principal
- `HomeController.php` - Homepage

#### Models/
- `Usuario.php` - Usuários e autenticação
- `Questao.php` - Questões
- `Simulado.php` - Simulados
- `Edital.php` - Editais
- `Progresso.php` - Gamificação

#### Views/
- `layouts/default.php` - Layout principal
- `pages/home/index.php` - Homepage
- `pages/auth/login.php` - Login
- `pages/auth/register.php` - Registro
- `pages/dashboard/index.php` - Dashboard

#### Core/
- `BaseModel.php` - CRUD genérico
- `BaseController.php` - Helpers para controllers
- `Router.php` - Sistema de rotas
- `Autoloader.php` - Autoloading PSR-4

---

### 📚 docs/ - Documentação

Todos os documentos técnicos estão aqui:

- **ARQUITETURA_MVC.md** - Documentação completa da arquitetura
- **README_MIGRACAO.md** - Guia prático de migração
- **README_TECNICO.md** - Referência técnica
- **GUIA_COMPLETO_MIGRACAO.md** - Guia visual
- **SIDEBAR_IMPLEMENTATION.md** - Implementação de sidebar

---

### ⚙️ config/ - Configurações

Centralização de todas as configurações:

- `config.php` - Configurações gerais
- `database.php` - Gerenciador de conexão (Singleton)

---

### 🗄️ classes/ - Classes Antigas

Classes do sistema antigo ainda em uso:

- `AnalisadorEdital.php` - Análise de editais
- `Gamificacao.php` - Sistema de gamificação
- `GeradorCronograma.php` - Geração de cronogramas
- `GeradorPDFCronograma.php` - PDF de cronogramas
- `SistemaProgressoAvancado.php` - Progresso avançado

**Nota**: Estas classes serão migradas para `app/Services/` gradualmente.

---

### 📦 old_code/ - Código Antigo

Arquivos PHP do sistema antigo (pré-MVC):

- `dashboard.php` - Dashboard antigo
- `questoes.php` - Questões
- `simulados.php` - Simulados
- `editais.php` - Editais
- `perfil.php` - Perfil
- `videoaulas.php` - Videoaulas
- E outros...

**Status**: Arquivados para referência durante a migração

**Plano**: Cada arquivo será migrado para a arquitetura MVC

---

### 🔧 setup/ - Scripts de Instalação

Scripts para configuração inicial:

- `instalar_*.php` - Instalação de módulos
- `inicializar_*.php` - Inicialização de dados
- `adicionar_exercicios.php` - Adicionar exercícios
- `criar_tabelas_*.sql` - Criação de tabelas

**Uso**: Execute após criar o banco de dados

---

### 📦 archive/ - Arquivos Arquivados

Arquivos de teste, debug e correção:

- Arquivos `corrigir_*.php`
- Arquivos `testar_*.php`
- Arquivos `debug_*.php`
- Arquivos `diagnostico_*.php`
- Pasta `mysql-8.4/` (logs do sistema)
- Backups de classes

**Status**: Não usados, arquivados para referência

---

## 🚀 Fluxo de Trabalho

### 1. Desenvolvimento Atual
```bash
# Usar sistema antigo
http://localhost/RCP-CONCURSOS/index.php

# Ou usar sistema MVC (recomendado)
http://localhost/RCP-CONCURSOS/mvc_index.php
```

### 2. Migração para MVC

1. Escolha um arquivo de `old_code/`
2. Crie Controller correspondente em `app/Controllers/`
3. Crie Models se necessário em `app/Models/`
4. Crie Views em `app/Views/pages/`
5. Defina rotas em `app/Core/Router.php`
6. Teste e delete o arquivo antigo

### 3. Criar Nova Funcionalidade

```php
// 1. Criar Model
app/Models/MinhaEntidade.php

// 2. Criar Controller
app/Controllers/MinhaEntidadeController.php

// 3. Criar View
app/Views/pages/minha_entidade/index.php

// 4. Definir Rota
app/Core/Router.php
```

---

## 📋 Status de Migração

### ✅ Migrado para MVC
- [x] Homepage (`index.php`)
- [x] Login (`login.php` → `AuthController`)
- [x] Registro (`register.php` → `AuthController`)
- [x] Dashboard (`dashboard.php` → `DashboardController`)

### 🔄 Em Migração
- [ ] Questões (`old_code/questoes.php`)
- [ ] Simulados (`old_code/simulados.php`)
- [ ] Editais (`old_code/editais.php`)
- [ ] Perfil (`old_code/perfil.php`)
- [ ] Videoaulas (`old_code/videoaulas.php`)

### ⏳ Pendente
- [ ] Classes → Services
- [ ] Upload de editais
- [ ] Geração de cronogramas
- [ ] Integração completa

---

## 🗑️ Arquivos Seguros para Deletar

Se precisar de mais espaço, estes arquivos podem ser deletados com segurança:

### ✅ Deletar com Segurança
```bash
archive/              # Arquivos de teste/debug
old_code/             # Após migração completa
mysql-8.4/            # Logs do sistema (dentro de archive/)
```

### ❌ NÃO Deletar
```bash
app/                  # Código MVC
config/               # Configurações
docs/                 # Documentação
setup/                # Scripts de instalação
css/                  # Estilos
uploads/              # Arquivos de usuários
banco.sql             # Estrutura do banco
```

---

## 📊 Métricas de Organização

| Categoria | Arquivos |
|-----------|----------|
| MVC (app/) | 30+ |
| Código Antigo (old_code/) | 15+ |
| Docs | 6 |
| Setup | 8 |
| Archive | 30+ |
| **Total** | **89+** |

---

## 🎯 Próximos Passos

1. **Migrar funcionalidades de `old_code/`** para MVC
2. **Transformar classes** em Services
3. **Expandir Views** para todas funcionalidades
4. **Implementar testes** automatizados
5. **Remover `old_code/`** após migração completa

---

## 📚 Leia Mais

- [Arquitetura MVC](ARQUITETURA_MVC.md)
- [Guia de Migração](README_MIGRACAO.md)
- [Referência Técnica](README_TECNICO.md)

---

**Projeto organizado para máxima clareza e manutenibilidade! 🚀**


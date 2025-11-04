# 📥 Instruções de Instalação - RCP-CONCURSOS

## 🚀 Instalação Rápida

### Passo 1: Importar Banco de Dados

Se você já tem um banco de dados criado, execute:

```bash
# Adicionar apenas as tabelas que faltam (recomendado)
mysql -u root -p concursos < adicionar_tabelas_videoaulas.sql
```

**OU** se ainda não tem o banco:

```bash
# Criar banco completo do zero
mysql -u root -p < banco_completo.sql
```

---

### Passo 2: Verificar Conexão

O arquivo `classes/Database.php` já está configurado:
- **Host**: localhost
- **Database**: concursos
- **User**: root
- **Password**: (vazio)

Se necessário, edite `classes/Database.php` para alterar as credenciais.

---

### Passo 3: Acessar o Sistema

```
http://localhost/RCP-CONCURSOS-main/
```

---

## 🔧 Correções Aplicadas

### ✅ Problemas Corrigidos

1. **Erro de LIMIT SQL** - Corrigido em GamificacaoRefatorada e Questao
2. **Tabelas de videoaulas** - Adicionadas ao banco
3. **Migração POO** - Login, Register, Dashboard e Perfil migrados
4. **Organização** - ~40 arquivos desnecessários removidos

### ✅ Novas Classes POO

- `Database.php` - Singleton para conexão
- `User.php` - Gestão de usuários
- `Questao.php` - Gestão de questões
- `Simulado.php` - Gestão de simulados
- `GamificacaoRefatorada.php` - Gamificação POO

---

## 📚 Documentação Disponível

1. **README.md** - Visão geral
2. **DOCUMENTACAO_POO.md** - Arquitetura completa
3. **GUIA_MIGRACAO.md** - Como migrar código
4. **ESTRUTURA_FINAL.md** - Estrutura do projeto
5. **CORRECOES_FINAIS.md** - Correções aplicadas
6. **INSTRUCOES_INSTALACAO.md** - Este arquivo

---

## 🎯 Funcionalidades Disponíveis

### Autenticação ✅
- Login e cadastro
- Recuperação de senha (futuro)

### Gamificação ✅
- Pontos e níveis
- Conquistas
- Streak
- Rankings

### Questões ✅
- Banco de questões
- Resposta individual
- Estatísticas

### Simulados ✅
- Criação personalizada
- Correção automática
- Resultados

### Videoaulas ✅ (após executar SQL)
- Categorias
- Progresso
- Certificados

### Cronogramas ✅
- Geração automática
- Acompanhamento

---

## 🆘 Solução de Problemas

### Erro: "Table doesn't exist"

**Solução**: Execute o script SQL apropriado
```bash
mysql -u root -p concursos < adicionar_tabelas_videoaulas.sql
```

### Erro: "LIMIT syntax error"

**Status**: ✅ Já corrigido! Não deve mais acontecer.

### Erro: "Connection refused"

**Solução**: Verifique se o MySQL está rodando e edite `classes/Database.php`

---

## 📞 Suporte

Documentação completa disponível em:
- `DOCUMENTACAO_POO.md`
- `GUIA_MIGRACAO.md`
- `README.md`

---

**✅ Sistema pronto para uso!**



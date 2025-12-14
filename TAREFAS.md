# TAREFAS - Sistema de Autenticação Pet360

## 📋 Visão Geral
Implementação de sistema de autenticação completo com criação de conta, login e dashboard para usuários.

---

## 🗄️ BANCO DE DADOS

### Sistema Recomendado: **MySQL/MariaDB com phpMyAdmin**

**Recomendação:** MySQL ou MariaDB com phpMyAdmin é a melhor opção para este projeto porque:
- ✅ Integração nativa com PHP (PDO/MySQLi)
- ✅ phpMyAdmin oferece interface gráfica amigável para gerenciamento
- ✅ Gratuito e open-source
- ✅ Amplamente suportado em hospedagens
- ✅ Excelente performance e estabilidade
- ✅ Compatível com a stack atual (PHP)

**Alternativas consideradas:**
- PostgreSQL: Mais robusto, mas menos comum em hospedagens compartilhadas
- SQLite: Simples, mas não ideal para produção com múltiplos usuários

**Estrutura do Banco de Dados Necessária:**
- Tabela `usuarios` com campos: id, nome, email, senha (hash), telefone, data_criacao, etc.

---

## ✅ TAREFAS PENDENTES

### 1. Configuração do Banco de Dados ✅
- [x] Instalar/configurar MySQL/MariaDB no servidor
- [x] Instalar/configurar phpMyAdmin
- [x] Criar banco de dados `pet360_db`
- [x] Criar tabela `usuarios` com estrutura adequada
- [x] Criar arquivo de configuração de conexão (`config/database.php`)

### 2. Página de Criação de Conta (`criar-conta.php`)
- [ ] Criar página de registro de novos usuários
- [ ] Formulário com campos: nome, email, telefone, senha, confirmar senha
- [ ] Validação de dados no frontend (JavaScript)
- [ ] Validação de dados no backend (PHP)
- [ ] Verificar se email já existe no banco
- [ ] Hash da senha antes de salvar (password_hash)
- [ ] Mensagens de erro/sucesso
- [ ] Design consistente com o tema do site (Tailwind CSS)
- [ ] Link para página de login
- [ ] Redirecionamento após cadastro bem-sucedido

### 3. Modal de Login no `index.php`
- [ ] Adicionar botão "Login" no header (superior direito, ao lado de "Agendar agora")
- [ ] Criar modal de login com Tailwind CSS
- [ ] Formulário de login (email/telefone e senha)
- [ ] JavaScript para abrir/fechar modal
- [ ] Validação de campos
- [ ] Integração com backend para autenticação
- [ ] Mensagens de erro (credenciais inválidas)
- [ ] Link para página de criação de conta dentro do modal
- [ ] Design responsivo e acessível

### 4. Sistema de Autenticação Backend
- [ ] Criar arquivo `auth/login.php` para processar login
- [ ] Criar arquivo `auth/register.php` para processar registro
- [ ] Criar arquivo `auth/logout.php` para encerrar sessão
- [ ] Implementar sessões PHP (session_start)
- [ ] Verificar credenciais no banco de dados
- [ ] Comparar senha com password_verify
- [ ] Criar variáveis de sessão após login bem-sucedido
- [ ] Proteção contra SQL injection (prepared statements)
- [ ] Proteção contra CSRF (tokens)

### 5. Página de Dashboard (`dashboard.php`)
- [ ] Criar página de dashboard para usuários logados
- [ ] Verificar se usuário está autenticado (middleware)
- [ ] Exibir informações do usuário
- [ ] Design consistente com o tema do site
- [ ] Menu de navegação
- [ ] Botão de logout
- [ ] Seções: perfil, serviços agendados, histórico, etc.
- [ ] Responsivo e moderno

### 6. Redirecionamento e Proteção de Rotas
- [ ] Após login bem-sucedido → redirecionar para `dashboard.php`
- [ ] Após registro bem-sucedido → redirecionar para `dashboard.php` (ou login)
- [ ] Proteger rotas que requerem autenticação
- [ ] Redirecionar usuários não autenticados para login
- [ ] Redirecionar usuários autenticados que tentam acessar login/registro

### 7. Melhorias e Segurança
- [ ] Implementar "Lembrar-me" (opcional)
- [ ] Recuperação de senha (futuro)
- [ ] Validação de email (confirmação por email - futuro)
- [ ] Rate limiting para tentativas de login
- [ ] Sanitização de inputs
- [ ] Headers de segurança

---

## 📁 Estrutura de Arquivos Sugerida

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

### 2. Modal de Criação de Conta no `index.php` ✅
- [x] Criar modal de registro de novos usuários (popup animado)
- [x] Formulário com campos: nome, email, telefone, senha, confirmar senha
- [x] Validação de dados no frontend (JavaScript)
- [x] Validação de dados no backend (PHP)
- [x] Verificar se email já existe no banco
- [x] Hash da senha antes de salvar (password_hash)
- [x] Mensagens de erro/sucesso
- [x] Design consistente com o tema do site (Tailwind CSS)
- [x] Máscara de telefone (formato brasileiro)
- [x] Envio apenas de números para o banco de dados
- [x] Animação moderna de abertura/fechamento do modal
- [x] Fundo embaçado (backdrop blur)
- [x] Mensagem de sucesso com contador e redirecionamento automático
- [x] Botão para mostrar/ocultar senha (ícone de olho)
- [x] Validação de senha forte (mínimo 7 caracteres, 1 maiúscula, 1 caractere especial)
- [x] Link para modal de login (a ser implementado)

### 3. Modal de Login no `index.php` ✅
- [x] Adicionar botão "Login" no header (superior direito, ao lado de "Agendar agora")
- [x] Criar modal de login com Tailwind CSS
- [x] Formulário de login (email e senha)
- [x] JavaScript para abrir/fechar modal
- [x] Validação de campos
- [x] Integração com backend para autenticação
- [x] Mensagens de erro (credenciais inválidas)
- [x] Link para modal de criação de conta dentro do modal
- [x] Design responsivo e acessível
- [x] Botão para mostrar/ocultar senha (ícone de olho)
- [x] Animações modernas (mesmo padrão do modal de criar conta)

### 4. Sistema de Autenticação Backend ✅
- [x] Criar arquivo `auth/login.php` para processar login
- [x] Criar arquivo `auth/register.php` para processar registro
- [x] Criar arquivo `auth/logout.php` para encerrar sessão
- [x] Implementar sessões PHP (session_start)
- [x] Verificar credenciais no banco de dados
- [x] Comparar senha com password_verify
- [x] Criar variáveis de sessão após login bem-sucedido
- [x] Proteção contra SQL injection (prepared statements)
- [ ] Proteção contra CSRF (tokens)

### 5. Página de Dashboard (`dashboard.php`) ✅
- [x] Criar página de dashboard para usuários logados
- [x] Verificar se usuário está autenticado (middleware)
- [x] Exibir informações do usuário
- [x] Design consistente com o tema do site
- [x] Menu de navegação
- [x] Botão de logout
- [x] Seções: perfil, serviços agendados, histórico, etc.
- [x] Responsivo e moderno

### 6. Redirecionamento e Proteção de Rotas
- [x] Após login bem-sucedido → redirecionar para `dashboard.php`
- [ ] Após registro bem-sucedido → redirecionar para `dashboard.php` (ou login)
- [x] Proteger rotas que requerem autenticação
- [x] Redirecionar usuários não autenticados para login
- [ ] Redirecionar usuários autenticados que tentam acessar login/registro
- [x] Manter sessão ativa na home (index.php) quando usuário estiver logado
- [x] Exibir informações do usuário logado no header da home
- [x] Ocultar botões Login/Criar Conta quando usuário estiver autenticado
- [x] Manter cookie de sessão ativo (session_start em todas as páginas)

### 7. Melhorias e Segurança
- [ ] Implementar "Lembrar-me" (opcional)
- [ ] Recuperação de senha (futuro)
- [ ] Validação de email (confirmação por email - futuro)
- [ ] Rate limiting para tentativas de login
- [ ] Sanitização de inputs
- [ ] Headers de segurança

### 8. Sistema de Cadastro de Pets ✅
- [x] Criar tabela `pets` no banco de dados
- [x] Campos: nome, idade, raça, tipo (cachorro/gato/outro)
- [x] Campo foto para armazenar imagem do pet
- [x] Vincular pet ao usuário (chave estrangeira)
- [x] Interface no dashboard para adicionar pet (modal animado)
- [x] Formulário de cadastro com validação
- [x] Upload de foto do pet (JPG, PNG, GIF, WEBP - máx. 5MB)
- [x] Preview de foto antes do upload
- [x] Backend para processar cadastro (`pets/create.php`)
- [x] Exibir lista de pets cadastrados no dashboard
- [x] Cards visuais para cada pet com foto e informações
- [x] Mensagens de erro/sucesso
- [x] Validação frontend e backend
- [x] Funcionalidade de editar pet (modal de edição)
- [x] Backend para atualizar pet (`pets/update.php`)
- [x] Atualização de foto (substitui foto antiga)
- [x] Funcionalidade de excluir pet (soft delete)
- [x] Backend para excluir pet (`pets/delete.php`)
- [x] Botões de editar e excluir nos cards de pets
- [x] Confirmação antes de excluir
- [ ] Vincular serviços (banho & tosa, adestramento, passeios) aos pets

---

## 📁 Estrutura de Arquivos Sugerida

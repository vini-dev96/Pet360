# Configuração do Banco de Dados - Pet360

Este diretório contém os scripts SQL necessários para configurar o banco de dados do sistema de autenticação Pet360.

## Pré-requisitos

- XAMPP instalado e rodando
- MySQL/MariaDB ativo no XAMPP
- phpMyAdmin acessível (geralmente em `http://localhost/phpmyadmin`)

## Passo a Passo

### 1. Iniciar o XAMPP

Certifique-se de que o MySQL está rodando no XAMPP Control Panel.

### 2. Acessar o phpMyAdmin

Abra seu navegador e acesse:
```
http://localhost/phpmyadmin
```

### 3. Executar o Script SQL

**Opção A: Executar script completo (recomendado)**

1. No phpMyAdmin, clique na aba **"SQL"** no topo
2. Abra o arquivo `schema.sql` deste diretório
3. Copie todo o conteúdo do arquivo
4. Cole no campo de texto do phpMyAdmin
5. Clique em **"Executar"** ou pressione `Ctrl + Enter`

**Opção B: Executar em etapas**

1. Primeiro, execute apenas a parte do `CREATE DATABASE`:
   ```sql
   CREATE DATABASE IF NOT EXISTS `pet360_db`
       DEFAULT CHARACTER SET utf8mb4
       DEFAULT COLLATE utf8mb4_unicode_ci;
   ```

2. Selecione o banco `pet360_db` no menu lateral esquerdo

3. Execute o `CREATE TABLE` para criar a tabela `usuarios`

### 4. Verificar a Criação

Após executar o script, você deve ver:
- Um novo banco de dados chamado `pet360_db` no menu lateral
- Uma tabela chamada `usuarios` dentro do banco `pet360_db`

### 5. Verificar a Estrutura da Tabela

1. Clique no banco `pet360_db` no menu lateral
2. Clique na tabela `usuarios`
3. Vá na aba **"Estrutura"** para verificar os campos criados

## Estrutura da Tabela `usuarios`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT(11) | Identificador único (chave primária, auto-incremento) |
| `nome` | VARCHAR(255) | Nome completo do usuário |
| `email` | VARCHAR(255) | Email único (usado para login) |
| `telefone` | VARCHAR(20) | Telefone de contato (opcional) |
| `senha` | VARCHAR(255) | Hash da senha (nunca texto plano!) |
| `data_criacao` | TIMESTAMP | Data e hora de criação (automático) |
| `data_atualizacao` | TIMESTAMP | Data e hora da última atualização (automático) |
| `ativo` | TINYINT(1) | Status da conta (1=ativo, 0=inativo) |

## Configuração do Arquivo de Conexão

Após criar o banco de dados, verifique se as credenciais no arquivo `config/database.php` estão corretas:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'pet360_db');
define('DB_USER', 'root');        // Usuário padrão do XAMPP
define('DB_PASS', '');            // Senha padrão do XAMPP (vazio)
```

**Importante:** Se você configurou uma senha para o usuário `root` do MySQL, atualize a constante `DB_PASS` no arquivo `config/database.php`.

## Testar a Conexão

Para testar se a conexão está funcionando, você pode criar um arquivo PHP temporário:

```php
<?php
require_once 'config/database.php';

if (testDatabaseConnection()) {
    echo "Conexão com banco de dados: OK!";
} else {
    echo "Erro ao conectar com o banco de dados.";
}
?>
```

## Troubleshooting

### Erro: "Access denied for user 'root'@'localhost'"

- Verifique se o MySQL está rodando no XAMPP
- Verifique se você configurou uma senha para o root e atualize `DB_PASS` em `config/database.php`

### Erro: "Unknown database 'pet360_db'"

- Execute o script SQL novamente para criar o banco de dados
- Verifique se o nome do banco está correto em `config/database.php`

### Erro: "Table 'usuarios' doesn't exist"

- Execute a parte do `CREATE TABLE` do script SQL
- Certifique-se de que está no banco de dados correto (`pet360_db`)

## Criar Tabela de Pets

Após criar a tabela de usuários, você precisa criar a tabela de pets:

1. No phpMyAdmin, selecione o banco `pet360_db` no menu lateral esquerdo
2. Clique na aba **"SQL"** no topo
3. Abra o arquivo `create_pets_table.sql` deste diretório
4. Copie todo o conteúdo do arquivo
5. Cole no campo de texto do phpMyAdmin
6. Clique em **"Executar"** ou pressione `Ctrl + Enter`

**OU** execute apenas o comando CREATE TABLE da tabela `pets` que está no arquivo `schema.sql`.

## Criar Tabelas de Serviços e Agendamentos

Para habilitar o sistema de compra de serviços, você precisa criar as tabelas de serviços e agendamentos:

1. No phpMyAdmin, selecione o banco `pet360_db` no menu lateral esquerdo
2. Clique na aba **"SQL"** no topo
3. Abra o arquivo `create_servicos_tables.sql` deste diretório
4. Copie todo o conteúdo do arquivo
5. Cole no campo de texto do phpMyAdmin
6. Clique em **"Executar"** ou pressione `Ctrl + Enter`

Este script irá:
- Criar a tabela `servicos` com os serviços disponíveis
- Criar a tabela `agendamentos` para armazenar as compras/agendamentos
- Inserir os serviços padrão (Banho & Tosa, Adestramento, Passeios) com seus preços

**Nota:** Os serviços padrão são inseridos automaticamente com os seguintes preços:
- **Banho & Tosa Premium**: R$ 60,00 (120 minutos) - Tipo: `banho_tosa`
- **Adestramento Positivo**: R$ 80,00 (60 minutos) - Tipo: `adestramento`
- **Passeio Monitorado**: R$ 40,00 (45 minutos) - Tipo: `passeios`

### Inserir apenas os serviços (se a tabela já existir)

Se você já criou a tabela `servicos` mas os serviços não foram inseridos, execute o arquivo `insert_servicos.sql`:

1. No phpMyAdmin, selecione o banco `pet360_db` no menu lateral esquerdo
2. Clique na aba **"SQL"** no topo
3. Abra o arquivo `insert_servicos.sql` deste diretório
4. Copie todo o conteúdo do arquivo
5. Cole no campo de texto do phpMyAdmin
6. Clique em **"Executar"** ou pressione `Ctrl + Enter`

Este script usa `INSERT IGNORE`, então pode ser executado múltiplas vezes sem criar duplicatas.

**Verificar se os serviços foram inseridos:**
Após executar o script, você pode verificar se os serviços foram inseridos corretamente:
1. No phpMyAdmin, selecione a tabela `servicos`
2. Clique na aba **"Visualizar"** para ver os registros
3. Você deve ver 3 serviços: Banho & Tosa Premium, Adestramento Positivo e Passeio Monitorado

## Próximos Passos

Após configurar o banco de dados, você pode:
1. Criar o modal de registro (no `index.php`) ✅
2. Criar o sistema de login (modal no `index.php`) ✅
3. Criar a página de dashboard (`dashboard.php`) ✅
4. Criar a tabela de pets ✅
5. Criar as tabelas de serviços e agendamentos ✅

Consulte o arquivo `TAREFAS.md` na raiz do projeto para ver todas as tarefas pendentes.

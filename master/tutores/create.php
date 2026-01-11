<?php
/**
 * Processamento de Cadastro de Tutor (Dashboard Master)
 * Pet360 - Sistema de Gerenciamento Master
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

// Iniciar sessão
startSecureSession();

// Verificar se o usuário está autenticado e é admin
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Location: ../../index.php');
    exit;
}

if ($_SESSION['usuario_nome'] !== 'admin' && $_SESSION['usuario_email'] !== 'admin@pet360.com.br') {
    header('Location: ../../dashboard.php');
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../dashboard_master.php');
    exit;
}

// Array para armazenar erros
$erros = [];

// Receber e sanitizar dados
$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

// Validação
if (empty($nome)) {
    $erros[] = 'Nome é obrigatório';
}

if (empty($telefone)) {
    $erros[] = 'Telefone é obrigatório';
}

// Limpar telefone (apenas números)
$telefone_numeros = preg_replace('/\D/', '', $telefone);

if (strlen($telefone_numeros) < 10 || strlen($telefone_numeros) > 11) {
    $erros[] = 'Telefone inválido (deve ter 10 ou 11 dígitos)';
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_tutor'] = $erros;
    $_SESSION['nome_tutor'] = $nome;
    $_SESSION['telefone_tutor'] = $telefone;
    header('Location: ../../dashboard_master.php?erro=tutor');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Gerar email único temporário baseado no nome + timestamp
    $email_base = 'tutor_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nome)) . '_' . time();
    $email = $email_base . '@pet360.local';
    
    // Garantir que o email seja único
    $tentativas = 0;
    while ($tentativas < 10) {
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt_check->execute(['email' => $email]);
        if (!$stmt_check->fetch()) {
            break; // Email único encontrado
        }
        $email = $email_base . '_' . $tentativas . '@pet360.local';
        $tentativas++;
    }
    
    if ($tentativas >= 10) {
        throw new Exception('Não foi possível gerar um email único');
    }
    
    // Senha padrão (não será usada, mas é obrigatória na estrutura da tabela)
    $senha_hash = password_hash('tutor123', PASSWORD_DEFAULT);
    
    // Inserir tutor
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, telefone, senha, data_criacao, data_atualizacao, ativo)
        VALUES (:nome, :email, :telefone, :senha, NOW(), NOW(), 1)
    ");
    
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone_numeros,
        'senha' => $senha_hash
    ]);
    
    $_SESSION['sucesso_tutor'] = 'Tutor cadastrado com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=tutor');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao cadastrar tutor: ' . $e->getMessage());
    $_SESSION['erros_tutor'] = ['Erro ao cadastrar tutor. Tente novamente.'];
    $_SESSION['nome_tutor'] = $nome;
    $_SESSION['telefone_tutor'] = $telefone;
    header('Location: ../../dashboard_master.php?erro=tutor');
    exit;
} catch (Exception $e) {
    error_log('Erro ao cadastrar tutor: ' . $e->getMessage());
    $_SESSION['erros_tutor'] = [$e->getMessage()];
    $_SESSION['nome_tutor'] = $nome;
    $_SESSION['telefone_tutor'] = $telefone;
    header('Location: ../../dashboard_master.php?erro=tutor');
    exit;
}

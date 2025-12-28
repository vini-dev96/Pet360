<?php
/**
 * Processamento de Login de Usuário
 * Pet360 - Sistema de Autenticação
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Iniciar sessão
startSecureSession();

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Receber e sanitizar dados
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

// Array para armazenar erros
$erros = [];

// Validação de dados
if (empty($email)) {
    $erros[] = 'Email é obrigatório';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'Email inválido';
}

if (empty($senha)) {
    $erros[] = 'Senha é obrigatória';
}

// Se houver erros de validação, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_login'] = $erros;
    $_SESSION['email_login'] = $email;
    header('Location: ../index.php?erro=login');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Buscar usuário pelo email
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, ativo FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();
    
    // Verificar se o usuário existe e se a senha está correta
    if (!$usuario) {
        $_SESSION['erros_login'] = ['Email ou senha incorretos'];
        $_SESSION['email_login'] = $email;
        header('Location: ../index.php?erro=login');
        exit;
    }
    
    // Verificar se a conta está ativa
    if (!$usuario['ativo']) {
        $_SESSION['erros_login'] = ['Sua conta está desativada. Entre em contato com o suporte.'];
        $_SESSION['email_login'] = $email;
        header('Location: ../index.php?erro=login');
        exit;
    }
    
    // Verificar senha
    if (!password_verify($senha, $usuario['senha'])) {
        $_SESSION['erros_login'] = ['Email ou senha incorretos'];
        $_SESSION['email_login'] = $email;
        header('Location: ../index.php?erro=login');
        exit;
    }
    
    // Login bem-sucedido - criar variáveis de sessão
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_logado'] = true;
    
    // Redirecionar para dashboard
    header('Location: ../dashboard.php');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao fazer login: ' . $e->getMessage());
    $_SESSION['erros_login'] = ['Erro ao fazer login. Tente novamente mais tarde.'];
    $_SESSION['email_login'] = $email;
    header('Location: ../index.php?erro=login');
    exit;
}


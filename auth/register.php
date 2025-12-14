<?php
/**
 * Processamento de Registro de UsuÃ¡rio
 * Pet360 - Sistema de AutenticaÃ§Ã£o
 */

require_once __DIR__ . '/../config/database.php';

// Iniciar sessÃ£o
session_start();

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Receber e sanitizar dados
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
// Remove todos os caracteres não numéricos do telefone
$telefone = preg_replace('/\D/', '', $telefone); // Apenas números
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Array para armazenar erros
$erros = [];

// Validação de dados
if (empty($nome)) {
    $erros[] = 'Nome é obrigatório';
}

if (empty($email)) {
    $erros[] = 'Email é obrigatório';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'Email inválido';
}

if (empty($senha)) {
    $erros[] = 'Senha é obrigatória';
} else {
    // Validar senha forte: mínimo 7 caracteres, 1 maiúscula, 1 caractere especial
    if (strlen($senha) < 7) {
        $erros[] = 'Senha deve ter no mínimo 7 caracteres';
    }
    
    if (!preg_match('/[A-Z]/', $senha)) {
        $erros[] = 'Senha deve conter pelo menos 1 letra maiúscula';
    }
    
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\\\|,.<>\/?]/', $senha)) {
        $erros[] = 'Senha deve conter pelo menos 1 caractere especial';
    }
}

if ($senha !== $confirmar_senha) {
    $erros[] = 'As senhas não coincidem';
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_registro'] = $erros;
    $_SESSION['dados_registro'] = ['nome' => $nome, 'email' => $email, 'telefone' => $telefone];
    header('Location: ../index.php?erro=registro');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->fetch()) {
        $_SESSION['erros_registro'] = ['Este email já está cadastrado'];
        $_SESSION['dados_registro'] = ['nome' => $nome, 'email' => $email, 'telefone' => $telefone];
        header('Location: ../index.php?erro=registro');
        exit;
    }
    
    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Inserir usuário no banco
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, telefone, senha) 
        VALUES (:nome, :email, :telefone, :senha)
    ");
    
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'telefone' => !empty($telefone) ? $telefone : null,
        'senha' => $senha_hash
    ]);
    
    // Sucesso - redirecionar para index com sucesso
    $_SESSION['sucesso_registro'] = true;
    header('Location: ../index.php?sucesso=registro');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao registrar usuário: ' . $e->getMessage());
    $_SESSION['erros_registro'] = ['Erro ao criar conta. Tente novamente mais tarde.'];
    $_SESSION['dados_registro'] = ['nome' => $nome, 'email' => $email, 'telefone' => $telefone];
    header('Location: ../index.php?erro=registro');
    exit;
}

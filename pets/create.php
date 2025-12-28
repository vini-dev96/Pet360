<?php
/**
 * Processamento de Cadastro de Pet
 * Pet360 - Sistema de Gerenciamento de Pets
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Iniciar sessão
startSecureSession();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Location: ../index.php');
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

// Array para armazenar erros
$erros = [];

// Receber e sanitizar dados
$nome = trim($_POST['nome'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$raca = trim($_POST['raca'] ?? '');
$idade = !empty($_POST['idade']) ? (int)$_POST['idade'] : null;

// Processar upload de foto
$foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        $erros[] = 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP.';
    } elseif ($file['size'] > $maxSize) {
        $erros[] = 'Arquivo muito grande. Tamanho máximo: 5MB.';
    } else {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('pet_', true) . '.' . $extension;
        $uploadDir = __DIR__ . '/../uploads/pets/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $foto = 'uploads/pets/' . $filename;
        } else {
            $erros[] = 'Erro ao fazer upload da foto.';
        }
    }
} elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $erros[] = 'Erro no upload da foto.';
}

// Validação de dados
if (empty($nome)) {
    $erros[] = 'Nome do pet é obrigatório';
} elseif (strlen($nome) < 2) {
    $erros[] = 'Nome do pet deve ter pelo menos 2 caracteres';
} elseif (strlen($nome) > 255) {
    $erros[] = 'Nome do pet deve ter no máximo 255 caracteres';
}

if (empty($tipo)) {
    $erros[] = 'Tipo do pet é obrigatório';
} elseif (!in_array($tipo, ['cachorro', 'gato', 'outro'])) {
    $erros[] = 'Tipo inválido';
}

if (!empty($raca) && strlen($raca) > 255) {
    $erros[] = 'Raça deve ter no máximo 255 caracteres';
}

if ($idade !== null && ($idade < 0 || $idade > 30)) {
    $erros[] = 'Idade deve estar entre 0 e 30 anos';
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_pet'] = $erros;
    header('Location: ../dashboard.php?erro=pet');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Inserir pet no banco
    $stmt = $pdo->prepare("
        INSERT INTO pets (usuario_id, nome, tipo, raca, idade, foto) 
        VALUES (:usuario_id, :nome, :tipo, :raca, :idade, :foto)
    ");
    
    $stmt->execute([
        'usuario_id' => $_SESSION['usuario_id'],
        'nome' => $nome,
        'tipo' => $tipo,
        'raca' => !empty($raca) ? $raca : null,
        'idade' => $idade,
        'foto' => $foto
    ]);
    
    // Sucesso - redirecionar para dashboard com sucesso
    header('Location: ../dashboard.php?sucesso=pet');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao cadastrar pet: ' . $e->getMessage());
    $_SESSION['erros_pet'] = ['Erro ao cadastrar pet. Tente novamente mais tarde.'];
    header('Location: ../dashboard.php?erro=pet');
    exit;
}

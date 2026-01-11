<?php
/**
 * Processamento de Cadastro de Pet (Dashboard Master)
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
$usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
$nome = trim($_POST['nome'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$raca = trim($_POST['raca'] ?? '');
$idade = !empty($_POST['idade']) ? (int)$_POST['idade'] : null;

// Validação
if ($usuario_id <= 0) {
    $erros[] = 'Tutor é obrigatório';
}

if (empty($nome)) {
    $erros[] = 'Nome do pet é obrigatório';
}

if (empty($tipo)) {
    $erros[] = 'Tipo é obrigatório';
}

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
        $uploadDir = __DIR__ . '/../../uploads/pets/';
        
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
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_pet_master'] = $erros;
    header('Location: ../../dashboard_master.php?erro=pet');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o tutor existe
    $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE id = :usuario_id");
    $stmt_check->execute(['usuario_id' => $usuario_id]);
    $tutor = $stmt_check->fetch();
    
    if (!$tutor) {
        $_SESSION['erros_pet_master'] = ['Tutor não encontrado'];
        header('Location: ../../dashboard_master.php?erro=pet');
        exit;
    }
    
    // Inserir pet
    $stmt = $pdo->prepare("
        INSERT INTO pets (usuario_id, nome, tipo, raca, idade, foto, data_criacao, data_atualizacao, ativo)
        VALUES (:usuario_id, :nome, :tipo, :raca, :idade, :foto, NOW(), NOW(), 1)
    ");
    
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'nome' => $nome,
        'tipo' => $tipo,
        'raca' => $raca ?: null,
        'idade' => $idade,
        'foto' => $foto
    ]);
    
    $_SESSION['sucesso_pet'] = 'Pet cadastrado com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=pet');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao cadastrar pet: ' . $e->getMessage());
    $_SESSION['erros_pet_master'] = ['Erro ao cadastrar pet. Tente novamente.'];
    header('Location: ../../dashboard_master.php?erro=pet');
    exit;
}

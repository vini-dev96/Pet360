<?php
/**
 * Processamento de Atualização de Pet
 * Pet360 - Sistema de Gerenciamento de Pets
 */

require_once __DIR__ . '/../config/database.php';

// Iniciar sessão
session_start();

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

// Receber e sanitizar dados
$id = (int)($_POST['id'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$raca = trim($_POST['raca'] ?? '');
$idade = !empty($_POST['idade']) ? (int)$_POST['idade'] : null;

// Array para armazenar erros
$erros = [];

// Validação de dados
if (empty($id)) {
    $erros[] = 'ID do pet inválido';
}

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
    $_SESSION['erros_editar_pet'] = $erros;
    $_SESSION['pet_edit_id'] = $id;
    header('Location: ../dashboard.php?erro=editar_pet');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o pet pertence ao usuário
    $stmt = $pdo->prepare("SELECT id, foto FROM pets WHERE id = :id AND usuario_id = :usuario_id AND ativo = 1");
    $stmt->execute(['id' => $id, 'usuario_id' => $_SESSION['usuario_id']]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        $_SESSION['erros_editar_pet'] = ['Pet não encontrado ou você não tem permissão para editá-lo.'];
        header('Location: ../dashboard.php?erro=editar_pet');
        exit;
    }
    
    // Processar upload de nova foto (se houver)
    $foto = $pet['foto']; // Manter foto atual por padrão
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $erros[] = 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP.';
        } elseif ($file['size'] > $maxSize) {
            $erros[] = 'Arquivo muito grande. Tamanho máximo: 5MB.';
        } else {
            // Deletar foto antiga se existir
            if (!empty($pet['foto']) && file_exists(__DIR__ . '/../' . $pet['foto'])) {
                unlink(__DIR__ . '/../' . $pet['foto']);
            }
            
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
        
        if (!empty($erros)) {
            $_SESSION['erros_editar_pet'] = $erros;
            $_SESSION['pet_edit_id'] = $id;
            header('Location: ../dashboard.php?erro=editar_pet');
            exit;
        }
    }
    
    // Atualizar pet no banco
    $stmt = $pdo->prepare("
        UPDATE pets 
        SET nome = :nome, tipo = :tipo, raca = :raca, idade = :idade, foto = :foto
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->execute([
        'id' => $id,
        'usuario_id' => $_SESSION['usuario_id'],
        'nome' => $nome,
        'tipo' => $tipo,
        'raca' => !empty($raca) ? $raca : null,
        'idade' => $idade,
        'foto' => $foto
    ]);
    
    // Sucesso - redirecionar para dashboard
    header('Location: ../dashboard.php?sucesso=editar_pet');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao atualizar pet: ' . $e->getMessage());
    $_SESSION['erros_editar_pet'] = ['Erro ao atualizar pet. Tente novamente mais tarde.'];
    $_SESSION['pet_edit_id'] = $id;
    header('Location: ../dashboard.php?erro=editar_pet');
    exit;
}

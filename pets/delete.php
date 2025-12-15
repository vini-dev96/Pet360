<?php
/**
 * Processamento de Exclusão de Pet
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

// Receber ID do pet
$id = (int)($_GET['id'] ?? 0);

if (empty($id)) {
    header('Location: ../dashboard.php');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o pet pertence ao usuário
    $stmt = $pdo->prepare("SELECT id, foto FROM pets WHERE id = :id AND usuario_id = :usuario_id AND ativo = 1");
    $stmt->execute(['id' => $id, 'usuario_id' => $_SESSION['usuario_id']]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        header('Location: ../dashboard.php');
        exit;
    }
    
    // Deletar foto se existir
    if (!empty($pet['foto']) && file_exists(__DIR__ . '/../' . $pet['foto'])) {
        unlink(__DIR__ . '/../' . $pet['foto']);
    }
    
    // Hard delete (remover do banco de dados)
    $stmt = $pdo->prepare("DELETE FROM pets WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->execute(['id' => $id, 'usuario_id' => $_SESSION['usuario_id']]);
    
    // Sucesso - redirecionar para dashboard
    header('Location: ../dashboard.php?sucesso=excluir_pet');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao excluir pet: ' . $e->getMessage());
    header('Location: ../dashboard.php?erro=excluir_pet');
    exit;
}

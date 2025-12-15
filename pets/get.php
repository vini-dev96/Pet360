<?php
/**
 * Buscar dados de um pet (para edição)
 * Pet360 - Sistema de Gerenciamento de Pets
 */

require_once __DIR__ . '/../config/database.php';

// Iniciar sessão
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Receber ID do pet
$id = (int)($_GET['id'] ?? 0);

if (empty($id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Buscar pet do usuário
    $stmt = $pdo->prepare("SELECT id, nome, tipo, raca, idade, foto FROM pets WHERE id = :id AND usuario_id = :usuario_id AND ativo = 1");
    $stmt->execute(['id' => $id, 'usuario_id' => $_SESSION['usuario_id']]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Pet não encontrado']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'pet' => $pet]);
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao buscar pet: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar pet']);
    exit;
}

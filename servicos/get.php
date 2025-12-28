<?php
/**
 * Buscar dados de um serviço
 * Pet360 - Sistema de Gerenciamento de Serviços
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Iniciar sessão
startSecureSession();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Receber ID do serviço
$id = (int)($_GET['id'] ?? 0);

if (empty($id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Buscar serviço
    $stmt = $pdo->prepare("SELECT id, nome, descricao, preco, duracao, tipo FROM servicos WHERE id = :id AND ativo = 1");
    $stmt->execute(['id' => $id]);
    $servico = $stmt->fetch();
    
    if (!$servico) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Serviço não encontrado']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'servico' => $servico]);
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao buscar serviço: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar serviço']);
    exit;
}

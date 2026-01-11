<?php
/**
 * Processamento de Exclusão de Tutor (Dashboard Master)
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

// Receber ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    $_SESSION['erros_tutor'] = ['ID do tutor inválido'];
    header('Location: ../../dashboard_master.php?erro=tutor');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o tutor existe e não é o admin
    $stmt_check = $pdo->prepare("SELECT id, nome FROM usuarios WHERE id = :id");
    $stmt_check->execute(['id' => $id]);
    $tutor = $stmt_check->fetch();
    
    if (!$tutor) {
        $_SESSION['erros_tutor'] = ['Tutor não encontrado'];
        header('Location: ../../dashboard_master.php?erro=tutor');
        exit;
    }
    
    if ($tutor['nome'] === 'admin') {
        $_SESSION['erros_tutor'] = ['Não é possível excluir o administrador'];
        header('Location: ../../dashboard_master.php?erro=tutor');
        exit;
    }
    
    // Excluir tutor (CASCADE vai excluir pets e consultas vinculadas)
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    $_SESSION['sucesso_tutor'] = 'Tutor excluído com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=tutor');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao excluir tutor: ' . $e->getMessage());
    $_SESSION['erros_tutor'] = ['Erro ao excluir tutor. Tente novamente.'];
    header('Location: ../../dashboard_master.php?erro=tutor');
    exit;
}

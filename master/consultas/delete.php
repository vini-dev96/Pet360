<?php
/**
 * Processamento de Exclusão de Consulta (Dashboard Master)
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
    $_SESSION['erros_consulta'] = ['ID da consulta inválido'];
    header('Location: ../../dashboard_master.php?erro=consulta');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se a consulta existe
    $stmt_check = $pdo->prepare("SELECT id FROM consultas_adestramento WHERE id = :id");
    $stmt_check->execute(['id' => $id]);
    $consulta = $stmt_check->fetch();
    
    if (!$consulta) {
        $_SESSION['erros_consulta'] = ['Consulta não encontrada'];
        header('Location: ../../dashboard_master.php?erro=consulta');
        exit;
    }
    
    // Excluir consulta
    $stmt = $pdo->prepare("DELETE FROM consultas_adestramento WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    $_SESSION['sucesso_consulta'] = 'Consulta excluída com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=consulta');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao excluir consulta: ' . $e->getMessage());
    $_SESSION['erros_consulta'] = ['Erro ao excluir consulta. Tente novamente.'];
    header('Location: ../../dashboard_master.php?erro=consulta');
    exit;
}

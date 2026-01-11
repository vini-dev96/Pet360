<?php
/**
 * Processamento de Atualização de Consulta de Adestramento (Dashboard Master)
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
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$data_consulta = trim($_POST['data_consulta'] ?? '');
$hora_consulta = trim($_POST['hora_consulta'] ?? '');

// Validação
if ($id <= 0) {
    $erros[] = 'ID da consulta inválido';
}

if (empty($data_consulta)) {
    $erros[] = 'Data é obrigatória';
}

if (empty($hora_consulta)) {
    $erros[] = 'Hora é obrigatória';
}

// Combinar data e hora
$data_hora = $data_consulta . ' ' . $hora_consulta . ':00';

// Validar data
try {
    $data_consulta_obj = new DateTime($data_hora);
} catch (Exception $e) {
    $erros[] = 'Data/hora inválida';
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_consulta_editar'] = $erros;
    header('Location: ../../dashboard_master.php?erro=consulta_editar&id=' . $id);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se a consulta existe
    $stmt_check = $pdo->prepare("SELECT id FROM consultas_adestramento WHERE id = :id");
    $stmt_check->execute(['id' => $id]);
    $consulta = $stmt_check->fetch();
    
    if (!$consulta) {
        $_SESSION['erros_consulta_editar'] = ['Consulta não encontrada'];
        header('Location: ../../dashboard_master.php?erro=consulta_editar');
        exit;
    }
    
    // Atualizar consulta
    $stmt = $pdo->prepare("
        UPDATE consultas_adestramento 
        SET data_consulta = :data_consulta, data_atualizacao = NOW()
        WHERE id = :id
    ");
    
    $stmt->execute([
        'id' => $id,
        'data_consulta' => $data_hora
    ]);
    
    $_SESSION['sucesso_consulta'] = 'Consulta atualizada com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=consulta');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao atualizar consulta: ' . $e->getMessage());
    $_SESSION['erros_consulta_editar'] = ['Erro ao atualizar consulta. Tente novamente.'];
    header('Location: ../../dashboard_master.php?erro=consulta_editar&id=' . $id);
    exit;
}

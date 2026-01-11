<?php
/**
 * Processamento de Adição de Observação em Consulta (Dashboard Master)
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
$observacoes = trim($_POST['observacoes'] ?? '');

// Validação
if ($id <= 0) {
    $erros[] = 'ID da consulta inválido';
}

if (empty($observacoes)) {
    $erros[] = 'Observações são obrigatórias';
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_observacao'] = $erros;
    header('Location: ../../dashboard_master.php?erro=observacao&id=' . $id);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se a consulta existe
    $stmt_check = $pdo->prepare("SELECT id, data_consulta FROM consultas_adestramento WHERE id = :id");
    $stmt_check->execute(['id' => $id]);
    $consulta = $stmt_check->fetch();
    
    if (!$consulta) {
        $_SESSION['erros_observacao'] = ['Consulta não encontrada'];
        header('Location: ../../dashboard_master.php?erro=observacao');
        exit;
    }
    
    // Verificar se a data da consulta já passou (permitir adicionar observação apenas em consultas já realizadas)
    try {
        $data_consulta_obj = new DateTime($consulta['data_consulta']);
        $agora = new DateTime();
        
        // Permitir adicionar observação mesmo se a consulta ainda não aconteceu
        // (pode ser usado para anotações pré-consulta)
    } catch (Exception $e) {
        // Data inválida - continuar mesmo assim
    }
    
    // Atualizar observações (adicionar ao texto existente se houver)
    $stmt_get = $pdo->prepare("SELECT observacoes FROM consultas_adestramento WHERE id = :id");
    $stmt_get->execute(['id' => $id]);
    $consulta_atual = $stmt_get->fetch();
    
    $observacoes_finais = $observacoes;
    if (!empty($consulta_atual['observacoes'])) {
        $observacoes_finais = $consulta_atual['observacoes'] . "\n\n--- " . date('d/m/Y H:i') . " ---\n" . $observacoes;
    }
    
    // Atualizar consulta
    $stmt = $pdo->prepare("
        UPDATE consultas_adestramento 
        SET observacoes = :observacoes, data_atualizacao = NOW()
        WHERE id = :id
    ");
    
    $stmt->execute([
        'id' => $id,
        'observacoes' => $observacoes_finais
    ]);
    
    $_SESSION['sucesso_observacao'] = 'Observação adicionada com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=observacao&id=' . $id);
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao adicionar observação: ' . $e->getMessage());
    $_SESSION['erros_observacao'] = ['Erro ao adicionar observação. Tente novamente.'];
    header('Location: ../../dashboard_master.php?erro=observacao&id=' . $id);
    exit;
}

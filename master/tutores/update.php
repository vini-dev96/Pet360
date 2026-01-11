<?php
/**
 * Processamento de Atualização de Tutor (Dashboard Master)
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
$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

// Validação
if ($id <= 0) {
    $erros[] = 'ID do tutor inválido';
}

if (empty($nome)) {
    $erros[] = 'Nome é obrigatório';
}

if (empty($telefone)) {
    $erros[] = 'Telefone é obrigatório';
}

// Limpar telefone (apenas números)
$telefone_numeros = preg_replace('/\D/', '', $telefone);

if (strlen($telefone_numeros) < 10 || strlen($telefone_numeros) > 11) {
    $erros[] = 'Telefone inválido (deve ter 10 ou 11 dígitos)';
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_tutor_editar'] = $erros;
    header('Location: ../../dashboard_master.php?erro=tutor_editar&id=' . $id);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o tutor existe e não é o admin
    $stmt_check = $pdo->prepare("SELECT id, nome FROM usuarios WHERE id = :id");
    $stmt_check->execute(['id' => $id]);
    $tutor = $stmt_check->fetch();
    
    if (!$tutor) {
        $_SESSION['erros_tutor_editar'] = ['Tutor não encontrado'];
        header('Location: ../../dashboard_master.php?erro=tutor_editar');
        exit;
    }
    
    if ($tutor['nome'] === 'admin') {
        $_SESSION['erros_tutor_editar'] = ['Não é possível editar o administrador'];
        header('Location: ../../dashboard_master.php?erro=tutor_editar');
        exit;
    }
    
    // Atualizar tutor
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET nome = :nome, telefone = :telefone, data_atualizacao = NOW()
        WHERE id = :id
    ");
    
    $stmt->execute([
        'id' => $id,
        'nome' => $nome,
        'telefone' => $telefone_numeros
    ]);
    
    $_SESSION['sucesso_tutor'] = 'Tutor atualizado com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=tutor');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao atualizar tutor: ' . $e->getMessage());
    $_SESSION['erros_tutor_editar'] = ['Erro ao atualizar tutor. Tente novamente.'];
    header('Location: ../../dashboard_master.php?erro=tutor_editar&id=' . $id);
    exit;
}

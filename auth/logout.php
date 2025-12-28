<?php
/**
 * Processamento de Logout
 * Pet360 - Sistema de Autenticação
 */

require_once __DIR__ . '/../config/session.php';

// Iniciar sessão
startSecureSession();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se é desejado matar a sessão, também delete o cookie de sessão.
// Nota: Isto destruirá a sessão, e não apenas os dados da sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir a sessão
session_destroy();

// Redirecionar para a página inicial
header('Location: ../index.php');
exit;

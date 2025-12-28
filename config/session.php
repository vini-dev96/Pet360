<?php
/**
 * Configuração de Sessão
 * Pet360 - Sistema de Autenticação
 * 
 * Configura a sessão PHP com duração de 8 horas (28800 segundos)
 */

// Tempo de vida da sessão: 8 horas em segundos
define('SESSION_LIFETIME', 8 * 60 * 60); // 28800 segundos

/**
 * Inicia a sessão com configurações de segurança e duração de 8 horas
 */
function startSecureSession() {
    // Configurar o tempo de vida da sessão (garbage collection)
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    
    // Configurar parâmetros do cookie de sessão
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,  // 8 horas
        'path' => '/',
        'domain' => '',                   // Deixe vazio para usar o domínio atual
        'secure' => false,                // true em produção com HTTPS
        'httponly' => true,               // Previne acesso via JavaScript
        'samesite' => 'Lax'               // Proteção CSRF
    ]);
    
    // Iniciar a sessão
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar se a sessão expirou (8 horas desde a última atividade)
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_LIFETIME)) {
        // Sessão expirada - limpar todas as variáveis de autenticação
        unset($_SESSION['usuario_id']);
        unset($_SESSION['usuario_nome']);
        unset($_SESSION['usuario_email']);
        unset($_SESSION['usuario_logado']);
        unset($_SESSION['LAST_ACTIVITY']);
        // Não destruir a sessão completamente, apenas limpar dados de autenticação
    }
    
    // Atualizar timestamp da última atividade
    $_SESSION['LAST_ACTIVITY'] = time();
}

<?php
/**
 * Configuração de Conexão com Banco de Dados
 * Pet360 - Sistema de Autenticação
 * 
 * Este arquivo contém as configurações e função para conexão com MySQL/MariaDB
 * usando PDO (PHP Data Objects) para maior segurança.
 */

// Configurações do banco de dados
// Ajuste estas variáveis conforme seu ambiente XAMPP
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'pet360_db');
define('DB_USER', 'root');        // Usuário padrão do XAMPP
define('DB_PASS', '');            // Senha padrão do XAMPP (vazio)
define('DB_CHARSET', 'utf8mb4');  // Suporta emojis e caracteres especiais

/**
 * Cria e retorna uma conexão PDO com o banco de dados
 * 
 * @return PDO Objeto de conexão PDO
 * @throws PDOException Se a conexão falhar
 */
function getDatabaseConnection() {
    try {
        // DSN (Data Source Name) para MySQL
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );
        
        // Opções do PDO para segurança e tratamento de erros
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Lança exceções em erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Retorna arrays associativos
            PDO::ATTR_EMULATE_PREPARES   => false,                    // Usa prepared statements reais
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        // Cria a conexão
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Em produção, não exponha detalhes do erro
        // Log o erro em um arquivo de log
        error_log('Erro de conexão com banco de dados: ' . $e->getMessage());
        
        // Mensagem genérica para o usuário
        die('Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.');
    }
}

/**
 * Testa a conexão com o banco de dados
 * Útil para verificar se as configurações estão corretas
 * 
 * @return bool True se a conexão foi bem-sucedida, False caso contrário
 */
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

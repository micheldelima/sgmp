<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Consulta os últimos 5 logins
    $stmt = $pdo->query("SELECT h.data_hora, h.ip, h.dispositivo, u.nome 
                         FROM historico_logins h
                         JOIN usuario u ON h.usuario_id = u.id
                         ORDER BY h.data_hora DESC 
                         LIMIT 5");

    $resultados = $stmt->fetchAll();

    echo json_encode($resultados);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar histórico: ' . $e->getMessage()]);
}

<?php
header('Content-Type: application/json');
require '../config/database.php';

header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_GET['telefone'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Telefone não informado']);
    exit;
}

//máscara do telefone
$telefone = preg_replace('/\D/', '', $_GET['telefone']);

//busca o telefone no banco de dados
try {
    $sql = "SELECT id, nome, telefone FROM clientes WHERE telefone = :telefone LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':telefone' => $telefone]);

    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        echo json_encode($cliente);
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor']);
}

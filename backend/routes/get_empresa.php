<?php
require_once '../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->query("SELECT telefone FROM empresa LIMIT 1");
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empresa) {
        echo json_encode($empresa);
    } else {
        echo json_encode(["error" => "Dados da empresa não encontrados."]);
    }

} catch (PDOException $e) {
    echo json_encode(["error" => "Erro no banco de dados: " . $e->getMessage()]);
}

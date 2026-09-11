<?php
session_start();
require_once '../config/database.php';

// Permite CORS APENAS para o React em localhost
$origem_permitida = "http://localhost:3000";
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $origem_permitida) {
    header("Access-Control-Allow-Origin: $origem_permitida");
}

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Se for uma requisição OPTIONS, apenas responde e encerra (CORS pré-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Conexão com banco de dados usando configurações seguras
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false, // Protege contra SQL Injection
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Retorna apenas arrays associativos
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao conectar ao banco de dados: " . $e->getMessage()]);
    exit;
}

// Endpoint para listar todos os itens do portfólio sem paginação, ordenados pelos mais recentes
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT id, titulo, descricao, imagem, DATE_FORMAT(data_criacao, '%d/%m/%Y') AS data_criacao 
                  FROM portfolio 
                  ORDER BY data_criacao DESC"; // Ordenação por data mais recente primeiro
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $portfolio = $stmt->fetchAll();

        // Verifica se há resultados
        if (empty($portfolio)) {
            echo json_encode(["error" => "Nenhum item encontrado no portfólio."]);
            exit;
        }

        // Retorna os dados no formato esperado pelo frontend
        echo json_encode([
            "portfolio" => $portfolio
        ]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Método não permitido!"]);
}

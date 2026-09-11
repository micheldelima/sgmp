<?php
// Permitir acesso local e remoto
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once '../config/database.php';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $stmt = $pdo->query("SELECT logo FROM empresa WHERE id = 1");
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empresa && !empty($empresa['logo'])) {
        // Detectar protocolo automaticamente (http/https)
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

        // Detectar diretório base (app-uepg)
        $basePath = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0];
        $basePath = $basePath ? '/' . $basePath : '';

        // Caminho da logo
        $baseURL = $protocolo . $_SERVER['HTTP_HOST'] . $basePath . "/backend/public/assets/img/logo/";

        echo json_encode([
            "success" => true,
            "logo" => $baseURL . $empresa['logo']
        ]);
    } else {
        echo json_encode(["success" => false, "logo" => null]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Erro ao buscar logo: " . $e->getMessage()]);
}

exit;

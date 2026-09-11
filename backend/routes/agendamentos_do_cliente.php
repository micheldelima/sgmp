<?php
require_once '../config/database.php';

// ==== CORS ====
$origem_permitida = "http://localhost:3000";
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $origem_permitida) {
    header("Access-Control-Allow-Origin: $origem_permitida");
}
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

// Pré-flight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error'=>'Método não permitido']);
    exit;
}

// ===== Conexão segura =====
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Erro ao conectar ao banco de dados!']);
    exit;
}

// ===== Util =====
function normaliza_tel($tel) {
    return preg_replace('/\D+/', '', $tel ?? '');
}

try {
    $cliente_id = isset($_GET['cliente_id']) ? (int) $_GET['cliente_id'] : 0;
    $telefone   = normaliza_tel($_GET['telefone'] ?? '');

    // Se não veio cliente_id, tenta achar pelo telefone
    if ($cliente_id <= 0 && $telefone !== '') {
        $sql = "
            SELECT id 
            FROM clientes 
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(telefone,'(',''),')',''),'-',''),' ','') = :tel
            LIMIT 1
        ";
        $st = $pdo->prepare($sql);
        $st->execute([':tel' => $telefone]);
        if ($row = $st->fetch()) {
            $cliente_id = (int)$row['id'];
        }
    }

    $rows = [];

    if ($cliente_id > 0) {
        // 1ª tentativa: por cliente_id
        $sql = "
            SELECT 
                a.id,
                a.data,
                a.horario,
                COALESCE(s.nome, a.servico) AS servico_nome
            FROM agendamentos a
            LEFT JOIN servicos s
                   ON CAST(a.servico AS UNSIGNED) = s.id
                   OR LOWER(a.servico) = LOWER(s.nome)
            WHERE a.cliente_id = :cid
              AND a.data >= CURDATE()
            ORDER BY a.data ASC, a.horario ASC
        ";
        $st = $pdo->prepare($sql);
        $st->execute([':cid' => $cliente_id]);
        $rows = $st->fetchAll();
    }

    // Fallback: se não achou por cliente_id e temos telefone, tenta associar pelo telefone do cliente
    if (empty($rows) && $telefone !== '') {
        $sql = "
            SELECT 
                a.id,
                a.data,
                a.horario,
                COALESCE(s.nome, a.servico) AS servico_nome
            FROM agendamentos a
            LEFT JOIN clientes c ON c.id = a.cliente_id
            LEFT JOIN servicos s
                   ON CAST(a.servico AS UNSIGNED) = s.id
                   OR LOWER(a.servico) = LOWER(s.nome)
            WHERE c.id IS NOT NULL
              AND REPLACE(REPLACE(REPLACE(REPLACE(c.telefone,'(',''),')',''),'-',''),' ','') = :tel
              AND a.data >= CURDATE()
            ORDER BY a.data ASC, a.horario ASC
        ";
        $st = $pdo->prepare($sql);
        $st->execute([':tel' => $telefone]);
        $rows = $st->fetchAll();
    }

    echo json_encode(['agendamentos' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Erro no servidor']);
}

<?php
session_start();
require_once '../config/database.php';

// CORS
$origem_permitida = "http://localhost:3000";
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $origem_permitida) {
    header("Access-Control-Allow-Origin: $origem_permitida");
}
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao conectar ao banco de dados!"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido!"]);
    exit;
}

try {
    $filtro = $_GET['filtro'] ?? '';
    $busca  = trim($_GET['busca'] ?? '');

    // mês alvo: YYYY-MM (ex.: 2025-08). Se não vier, usa o mês atual.
    $mes = (isset($_GET['mes']) && preg_match('/^\d{4}-\d{2}$/', $_GET['mes']))
         ? $_GET['mes']
         : date('Y-m');

    // coluna opcional
    $hasCreatedAt = (bool) $pdo->query("SHOW COLUMNS FROM servicos LIKE 'created_at'")->fetch();

    /* =============== Lançamentos (mensal) =============== */
    if ($filtro === 'lancamentos') {
        if ($hasCreatedAt) {
            $sql = "SELECT id, nome, descricao, valor, duracao, created_at
                    FROM servicos
                    WHERE DATE_FORMAT(created_at, '%Y-%m') = :mes
                    ORDER BY created_at DESC, nome ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':mes' => $mes]);
        } else {
            // fallback (sem created_at): “mais novos” por ID
            $stmt = $pdo->query("SELECT id, nome, descricao, valor, duracao
                                 FROM servicos
                                 ORDER BY id DESC
                                 LIMIT 6");
        }
        echo json_encode(['servicos' => $stmt->fetchAll()]);
        exit;
    }

    /* ============ Mais agendados (mensal) ============== */
    if ($filtro === 'mais_agendados') {
        $sql = "
            SELECT 
                s.id, s.nome, s.descricao, s.valor, s.duracao,
                COUNT(a.id) AS total_agendamentos
            FROM agendamentos a
            JOIN servicos s 
              ON CAST(a.servico AS UNSIGNED) = s.id
            WHERE DATE_FORMAT(a.data, '%Y-%m') = :mes
            GROUP BY s.id, s.nome, s.descricao, s.valor, s.duracao
            HAVING total_agendamentos > 0
            ORDER BY total_agendamentos DESC
            LIMIT 50
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':mes' => $mes]);
        echo json_encode(['servicos' => $stmt->fetchAll()]);
        exit;
    }

    /* ================ Listagem padrão A–Z =============== */
    $sql = "SELECT id, nome, descricao, valor, duracao FROM servicos";
    $params = [];
    if ($busca !== '') {
        $sql .= " WHERE nome LIKE :busca";
        $params[':busca'] = "%{$busca}%";
    }
    $sql .= " ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['servicos' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor.', 'detail' => $e->getMessage()]);
}

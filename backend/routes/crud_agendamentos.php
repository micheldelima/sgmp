<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

date_default_timezone_set('America/Sao_Paulo');
$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$method = $_SERVER['REQUEST_METHOD'];

// Listar agendamentos com paginação e busca
if ($method === 'GET') {
    $pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
    $itensPorPagina = 10;
    $offset = ($pagina - 1) * $itensPorPagina;
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    
    $where = " WHERE 1=1";
    $params = [];

    if (!empty($busca)) {
        $where .= " AND (a.nome LIKE :busca OR a.telefone LIKE :busca)";
        $params[':busca'] = "%$busca%";
    }

    // Consulta para contagem total
    $totalQuery = $pdo->prepare("SELECT COUNT(*) FROM agendamentos a $where");
    $totalQuery->execute($params);
    $totalAgendamentos = $totalQuery->fetchColumn();
    $totalPaginas = ceil($totalAgendamentos / $itensPorPagina);

    // Consulta para listar agendamentos
    $stmt = $pdo->prepare("SELECT a.id, a.nome, a.telefone, DATE_FORMAT(a.data, '%Y-%m-%d') as data, TIME_FORMAT(a.horario, '%H:%i') as horario, s.nome as servico 
                            FROM agendamentos a 
                            JOIN servicos s ON a.servico = s.id 
                            $where 
                            ORDER BY a.data DESC, a.horario ASC 
                            LIMIT :offset, :limit");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["agendamentos" => $agendamentos, "totalPaginas" => $totalPaginas]);
    exit;
}

// Criar ou Atualizar Agendamento
if ($method === 'POST' || $method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validação dos campos obrigatórios
    if (empty($data['nome']) || empty($data['telefone']) || empty($data['data']) || empty($data['horario']) || empty($data['servico'])) {
        echo json_encode(["error" => "Todos os campos são obrigatórios."]);
        exit;
    }

    // Verificar se o serviço existe
    $stmtServico = $pdo->prepare("SELECT id FROM servicos WHERE id = :servico");
    $stmtServico->execute([':servico' => (int) $data['servico']]);
    if (!$stmtServico->fetch()) {
        echo json_encode(["error" => "Serviço não encontrado."]);
        exit;
    }

    // Verificar conflito de horário
    $stmtConflito = $pdo->prepare("SELECT id FROM agendamentos WHERE data = :data AND horario = :horario AND servico = :servico");
    $stmtConflito->execute([
        ':data' => $data['data'],
        ':horario' => $data['horario'],
        ':servico' => (int) $data['servico']
    ]);
    if ($stmtConflito->fetch()) {
        echo json_encode(["error" => "Horário já ocupado para este serviço."]);
        exit;
    }

    if ($method === 'POST') {
        // Criar novo agendamento
        $stmt = $pdo->prepare("INSERT INTO agendamentos (nome, telefone, data, horario, servico) VALUES (:nome, :telefone, :data, :horario, :servico)");
        $stmt->execute([
            ':nome' => htmlspecialchars($data['nome']),
            ':telefone' => htmlspecialchars($data['telefone']),
            ':data' => $data['data'],
            ':horario' => $data['horario'],
            ':servico' => (int) $data['servico']
        ]);
        echo json_encode(["success" => "Agendamento criado com sucesso!"]);
    } else {
        // Atualizar agendamento existente
        if (empty($data['id'])) {
            echo json_encode(["error" => "ID do agendamento é obrigatório para atualização."]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE agendamentos SET nome = :nome, telefone = :telefone, data = :data, horario = :horario, servico = :servico WHERE id = :id");
        $stmt->execute([
            ':id' => (int) $data['id'],
            ':nome' => htmlspecialchars($data['nome']),
            ':telefone' => htmlspecialchars($data['telefone']),
            ':data' => $data['data'],
            ':horario' => $data['horario'],
            ':servico' => (int) $data['servico']
        ]);
        echo json_encode(["success" => "Agendamento atualizado com sucesso!"]);
    }
    exit;
}

// Excluir Agendamento
if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    if (!isset($data['id'])) {
        echo json_encode(["error" => "ID do agendamento é obrigatório"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = :id");
    $stmt->execute([':id' => (int) $data['id']]);
    echo json_encode(["success" => "Agendamento excluído com sucesso!"]);
    exit;
}

// Método não permitido
http_response_code(405);
echo json_encode(["error" => "Método não permitido"]);
exit;
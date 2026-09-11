<?php
session_start();
require_once '../config/database.php';

// Permite CORS APENAS para o React em localhost
$origem_permitida = "http://localhost:3000";
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $origem_permitida) {
    header("Access-Control-Allow-Origin: $origem_permitida");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Se for uma requisição OPTIONS, apenas responde e encerra (CORS pré-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Protege operações de escrita (POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    require_once '../config/auth_middleware.php'; // Autenticação necessária
}

// Conexão com banco de dados usando configurações seguras
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false, // Protege contra SQL Injection
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Retorna apenas arrays associativos
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao conectar ao banco de dados!"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // LISTAR SERVIÇOS COM PAGINAÇÃO E BUSCA GLOBAL
    header("Content-Type: application/json");

    try {
        $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
        $pagina = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
        $itensPorPagina = 7;
        $offset = ($pagina - 1) * $itensPorPagina;

        $query = "SELECT id, nome, descricao, valor, duracao FROM servicos";
        $queryTotal = "SELECT COUNT(*) FROM servicos";
        $params = [];

        if (!empty($busca)) {
            $query .= " WHERE nome LIKE :busca";
            $queryTotal .= " WHERE nome LIKE :busca";
            $params[':busca'] = "%$busca%";
        }

        $query .= " ORDER BY nome ASC LIMIT :offset, :limit";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $itensPorPagina, PDO::PARAM_INT);

        if (!empty($busca)) {
            $stmt->bindParam(':busca', $params[':busca'], PDO::PARAM_STR);
        }

        $stmt->execute();
        $servicos = $stmt->fetchAll();

        // Verifica se há erro
        if ($servicos === false) {
            echo json_encode(["error" => "Erro ao buscar serviços"]);
            exit;
        }

        $stmtTotal = $pdo->prepare($queryTotal);
        if (!empty($busca)) {
            $stmtTotal->bindParam(':busca', $params[':busca'], PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $totalServicos = (int) $stmtTotal->fetchColumn();
        $totalPaginas = max(1, ceil($totalServicos / $itensPorPagina));

        echo json_encode([
            "servicos" => $servicos,
            "totalPaginas" => $totalPaginas
        ]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
    }
    break;

// CADASTRAR SERVIÇO
    case 'POST': 
    $data = json_decode(file_get_contents("php://input"), true);

    $nome = trim($data['nome'] ?? '');
    $descricao = trim($data['descricao'] ?? '');
    $valor = isset($data['valor']) ? (float)$data['valor'] : 0;
    $duracao = isset($data['duracao']) ? (int)$data['duracao'] : 0;

    // Validações
    if ($nome === '' || $descricao === '' || $valor <= 0 || $duracao <= 0) {
        echo json_encode(["error" => "Todos os campos são obrigatórios e válidos!"]);
        exit;
    }

    // Duplicidade (case-insensitive)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM servicos WHERE LOWER(nome) = LOWER(:nome)");
    $stmt->execute([':nome' => $nome]);
    if ((int)$stmt->fetchColumn() > 0) {
        echo json_encode(["error" => "Já existe um serviço com este nome."]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO servicos (nome, descricao, valor, duracao)
        VALUES (:nome, :descricao, :valor, :duracao)
    ");
    $stmt->execute([
        ':nome' => htmlspecialchars($nome),
        ':descricao' => htmlspecialchars($descricao),
        ':valor' => $valor,
        ':duracao' => $duracao
    ]);

    echo json_encode(["success" => "Serviço cadastrado com sucesso!"]);
    break;

// EDITAR SERVIÇO
case 'PUT':  
    $data = json_decode(file_get_contents("php://input"), true);

    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $nome = trim($data['nome'] ?? '');
    $descricao = trim($data['descricao'] ?? '');
    $valor = isset($data['valor']) ? (float)$data['valor'] : 0;
    $duracao = isset($data['duracao']) ? (int)$data['duracao'] : 0;

    // Validações
    if ($id <= 0 || $nome === '' || $descricao === '' || $valor <= 0 || $duracao <= 0) {
        echo json_encode(["error" => "Todos os campos são obrigatórios e válidos!"]);
        exit;
    }

    // Duplicidade (case-insensitive) ignorando o próprio ID
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM servicos
        WHERE LOWER(nome) = LOWER(:nome) AND id != :id
    ");
    $stmt->execute([':nome' => $nome, ':id' => $id]);
    if ((int)$stmt->fetchColumn() > 0) {
        echo json_encode(["error" => "Já existe um serviço com este nome."]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE servicos
        SET nome = :nome, descricao = :descricao, valor = :valor, duracao = :duracao
        WHERE id = :id
    ");
    $stmt->execute([
        ':id' => $id,
        ':nome' => htmlspecialchars($nome),
        ':descricao' => htmlspecialchars($descricao),
        ':valor' => $valor,
        ':duracao' => $duracao
    ]);

    echo json_encode(["success" => "Serviço atualizado com sucesso!"]);
    break;

    case 'DELETE': // EXCLUIR SERVIÇO
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(["error" => "ID do serviço é obrigatório!"]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM servicos WHERE id = :id");
            $stmt->execute([':id' => (int) $data['id']]);
            echo json_encode(["success" => "Serviço excluído com sucesso!"]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Erro ao excluir serviço!"]);
        }
        break;

    default:
        echo json_encode(["error" => "Método não permitido!"]);
        break;
}
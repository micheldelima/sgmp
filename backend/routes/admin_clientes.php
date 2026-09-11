<?php
require_once '../config/database.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$method = $_SERVER['REQUEST_METHOD'];

// Função para resposta JSON
function responder($data) {
    echo json_encode($data);
    exit;
}

// GET — Buscar clientes com paginação
if ($method === 'GET') {
    $busca = $_GET['busca'] ?? '';
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $itensPorPagina = 7;
    $offset = ($pagina - 1) * $itensPorPagina;

    $sqlBusca = $busca ? "WHERE nome LIKE :busca OR telefone LIKE :busca" : "";
    $stmt = $pdo->prepare("SELECT * FROM clientes $sqlBusca ORDER BY nome LIMIT :offset, :limite");

    if ($busca) {
        $stmt->bindValue(':busca', "%$busca%");
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $itensPorPagina, PDO::PARAM_INT);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM clientes $sqlBusca");
    if ($busca) {
        $stmtTotal->bindValue(':busca', "%$busca%");
    }
    $stmtTotal->execute();
    $total = $stmtTotal->fetchColumn();

    responder([
        'clientes' => $clientes,
        'totalPaginas' => ceil($total / $itensPorPagina)
    ]);
}

// POST — Adicionar novo cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $nome = trim($data['nome'] ?? '');
    $telefone = preg_replace('/\D+/', '', $data['telefone'] ?? '');

    if (!$nome || !$telefone) {
        echo json_encode(['error' => 'Todos os campos são obrigatórios']);
        exit;
    }

    // Verifica se já existe cliente com esse telefone
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE telefone = ?");
    $stmt->execute([$telefone]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Já existe um cliente com este número.']);
        exit;
    }

    // Inserir novo cliente
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, telefone, data_cadastro) VALUES (?, ?, NOW())");
    $stmt->execute([$nome, $telefone]);
    echo json_encode(['success' => 'Cliente cadastrado com sucesso!']);
    exit;
}


// PUT — Editar cliente
if ($method === 'PUT') {
    $dados = json_decode(file_get_contents("php://input"), true);
    if (empty($dados['id']) || empty($dados['nome']) || empty($dados['telefone'])) {
        responder(['error' => 'Todos os campos são obrigatórios.']);
    }

    $telefoneLimpo = preg_replace('/\D/', '', $dados['telefone']);

    // Verifica se já existe outro cliente com o mesmo telefone
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE telefone = :telefone AND id != :id");
    $stmt->execute([
        ':telefone' => $telefoneLimpo,
        ':id' => $dados['id']
    ]);

    if ($stmt->fetch()) {
        responder(['error' => 'Este número de telefone já está cadastrado para outro cliente.']);
    }

    // Atualiza o cliente
    $stmt = $pdo->prepare("UPDATE clientes SET nome = :nome, telefone = :telefone WHERE id = :id");
    $stmt->execute([
        ':id' => $dados['id'],
        ':nome' => trim($dados['nome']),
        ':telefone' => $telefoneLimpo
    ]);

    responder(['success' => 'Cliente atualizado com sucesso.']);
}

// DELETE — Excluir cliente
if ($method === 'DELETE') {
    $dados = json_decode(file_get_contents("php://input"), true);
    if (empty($dados['id'])) {
        responder(['error' => 'ID não informado.']);
    }

    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $dados['id']]);

    responder(['success' => 'Cliente excluído com sucesso.']);
}

responder(['error' => 'Método não suportado.']);
?>
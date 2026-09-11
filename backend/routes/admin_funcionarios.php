<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$pdo = new PDO($dsn, $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$method = $_SERVER['REQUEST_METHOD'];

//busca
switch ($method) {
    case 'GET':

    if (isset($_GET['usuarios'])) {
        $stmt = $pdo->query("SELECT id, nome, nivel FROM usuario WHERE nivel IN ('Administrador', 'Funcionario') ORDER BY nome ASC");
        echo json_encode(['usuarios' => $stmt->fetchAll()]);
        exit;
    }

    $busca = $_GET['busca'] ?? '';
    $pagina = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
    $itensPorPagina = 7;
    $offset = ($pagina - 1) * $itensPorPagina;

    $query = "SELECT f.id, f.usuario_id, u.nome, u.telefone, f.profissao, f.disponivel, f.data_criacao
              FROM profissionais f
              JOIN usuario u ON f.usuario_id = u.id
              WHERE (u.nome LIKE :busca OR u.email LIKE :busca)
              AND u.nivel IN ('Administrador', 'Funcionario')
              ORDER BY u.nome ASC
              LIMIT :offset, :limit";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
    $stmt->execute();
    $profissionais = $stmt->fetchAll();

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) 
                                FROM profissionais f 
                                JOIN usuario u ON f.usuario_id = u.id 
                                WHERE (u.nome LIKE :busca OR u.email LIKE :busca)
                                AND u.nivel IN ('Administrador', 'Funcionario')");
    $stmtTotal->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
    $stmtTotal->execute();
    $total = $stmtTotal->fetchColumn();

    echo json_encode([
        'profissionais' => $profissionais,
        'totalPaginas' => ceil($total / $itensPorPagina)
    ]);
    break;

//adiciona
    case 'POST':
    $data = json_decode(file_get_contents("php://input"), true);
    $usuario_id = $data['usuario_id'] ?? null;
    $profissao = $data['profissao'] ?? '';
    $disponivel = isset($data['status']) ? (int)$data['status'] : 1;

    if (!$usuario_id || !$profissao) {
        echo json_encode(['error' => 'Usuário e profissão são obrigatórios.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM profissionais WHERE usuario_id = :usuario_id");
    $stmt->execute([':usuario_id' => $usuario_id]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Este usuário já está registrado como profissional.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO profissionais (usuario_id, profissao, disponivel, data_criacao) 
                           VALUES (:usuario_id, :profissao, :disponivel, NOW())");
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':profissao' => $profissao,
        ':disponivel' => $disponivel
    ]);

    echo json_encode(['success' => 'Profissional cadastrado com sucesso!']);
    break;

//edita
    case 'PUT':
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;
    $profissao = $data['profissao'] ?? '';
    $disponivel = isset($data['status']) ? (int)$data['status'] : 1;

    if (!$id || !$profissao) {
        echo json_encode(['error' => 'ID e profissão são obrigatórios.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE profissionais SET profissao = :profissao, disponivel = :disponivel WHERE id = :id");
    $stmt->execute([
        ':profissao' => $profissao,
        ':disponivel' => $disponivel,
        ':id' => $id
    ]);

    echo json_encode(['success' => 'Profissional atualizado com sucesso!']);
    break;

    case 'DELETE':
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['error' => 'ID é obrigatório.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM profissionais WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['success' => 'Profissional excluído com sucesso!']);
    break;
}

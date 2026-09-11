<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$method = $_SERVER['REQUEST_METHOD'];

//busca
switch ($method) {
    case 'GET':
        $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
        $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $itensPorPagina = 7;
        $offset = ($pagina - 1) * $itensPorPagina;

        $buscaSql = $busca ? "WHERE u.nome LIKE :busca" : '';

        $query = "SELECT b.id, b.profissional_id, b.data, b.hora_inicio, b.hora_fim, b.motivo, b.criado_em,
                         u.nome AS nome_profissional
                  FROM bloqueios_profissionais b
                  JOIN profissionais p ON b.profissional_id = p.id
                  JOIN usuario u ON p.usuario_id = u.id
                  $buscaSql
                  ORDER BY b.data DESC, b.hora_inicio ASC
                  LIMIT :offset, :limit";

        $stmt = $pdo->prepare($query);
        if ($busca) {
            $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
        $stmt->execute();
        $bloqueios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $queryTotal = "SELECT COUNT(*)
                       FROM bloqueios_profissionais b
                       JOIN profissionais p ON b.profissional_id = p.id
                       JOIN usuario u ON p.usuario_id = u.id
                       $buscaSql";
        $stmtTotal = $pdo->prepare($queryTotal);
        if ($busca) {
            $stmtTotal->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = (int)$stmtTotal->fetchColumn();
        $totalPaginas = max(1, ceil($total / $itensPorPagina));

        echo json_encode(['bloqueios' => $bloqueios, 'totalPaginas' => $totalPaginas]);
        break;

//cadastra
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['profissional_id']) || empty($data['data']) || empty($data['hora_inicio']) || empty($data['hora_fim']) || empty($data['motivo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO bloqueios_profissionais (profissional_id, data, hora_inicio, hora_fim, motivo, criado_em)
                               VALUES (:profissional_id, :data, :hora_inicio, :hora_fim, :motivo, NOW())");
        $stmt->execute([
            ':profissional_id' => $data['profissional_id'],
            ':data' => $data['data'],
            ':hora_inicio' => $data['hora_inicio'],
            ':hora_fim' => $data['hora_fim'],
            ':motivo' => $data['motivo']
        ]);

        echo json_encode(['success' => 'Bloqueio cadastrado com sucesso!']);
        break;

//edita
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['id']) || empty($data['profissional_id']) || empty($data['data']) || empty($data['hora_inicio']) || empty($data['hora_fim']) || empty($data['motivo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE bloqueios_profissionais
                               SET profissional_id = :profissional_id,
                                   data = :data,
                                   hora_inicio = :hora_inicio,
                                   hora_fim = :hora_fim,
                                   motivo = :motivo
                               WHERE id = :id");
        $stmt->execute([
            ':id' => $data['id'],
            ':profissional_id' => $data['profissional_id'],
            ':data' => $data['data'],
            ':hora_inicio' => $data['hora_inicio'],
            ':hora_fim' => $data['hora_fim'],
            ':motivo' => $data['motivo']
        ]);

        echo json_encode(['success' => 'Bloqueio atualizado com sucesso!']);
        break;

//deleta 
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID do bloqueio é obrigatório.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM bloqueios_profissionais WHERE id = :id");
        $stmt->execute([':id' => $data['id']]);

        echo json_encode(['success' => 'Bloqueio excluído com sucesso!']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido.']);
        break;
}
?>

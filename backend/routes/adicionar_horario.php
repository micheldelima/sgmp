<?php
require_once '../config/database.php';
header("Content-Type: application/json");

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Captura o método da requisição
    $method = $_SERVER['REQUEST_METHOD'];

    //Post
    if ($method === 'POST') {
    // Adicionar horário
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['dia_inicio'], $data['dia_fim'], $data['horario_abertura'], $data['horario_fechamento'], $data['feriado'])) {
        echo json_encode(["status" => "error", "mensagem" => "Dados incompletos"]);
        exit;
    }

    // Verificar se já existe horário cadastrado
    $stmt = $pdo->query("SELECT COUNT(*) FROM horarios_atendimento");
    $total = $stmt->fetchColumn();

    if ($total > 0) {
        echo json_encode([
            "status" => "error",
            "mensagem" => "Já existe um horário de atendimento cadastrado. Exclua o existente para adicionar outro."
        ]);
        exit;
    }

    // Inserção se não houver nenhum horário
    $stmt = $pdo->prepare("INSERT INTO horarios_atendimento (dia_inicio, dia_fim, horario_abertura, horario_fechamento, feriado) 
                           VALUES (:dia_inicio, :dia_fim, :horario_abertura, :horario_fechamento, :feriado)");
    $stmt->execute([
        ':dia_inicio' => $data['dia_inicio'],
        ':dia_fim' => $data['dia_fim'],
        ':horario_abertura' => $data['horario_abertura'],
        ':horario_fechamento' => $data['horario_fechamento'],
        ':feriado' => $data['feriado']
    ]);

    echo json_encode(["status" => "success", "mensagem" => "Horário adicionado com sucesso!"]);
    exit;
}

    //Get
    if ($method === 'GET') {
        // Listar horários
        $stmt = $pdo->query("SELECT * FROM horarios_atendimento ORDER BY FIELD(dia_inicio, 'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado')");
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($horarios);
        exit;
    } 

    //Delete
    if ($method === 'DELETE') {
        // Remover horário
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int) ($data['id'] ?? 0);

        if (!$id) {
            echo json_encode(["status" => "error", "mensagem" => "ID inválido!"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM horarios_atendimento WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode(["status" => "success", "mensagem" => "Horário removido com sucesso!"]);
        exit;
    }

    // Método inválido
    echo json_encode(["status" => "error", "mensagem" => "Método não permitido!"]);
    exit;

} catch (Exception $e) {
    echo json_encode(["status" => "error", "mensagem" => "Erro: " . $e->getMessage()]);
    exit;
}
?>

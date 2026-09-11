<?php
session_start();
require_once '../config/database.php';

// Configuração CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$pdo = new PDO($dsn, $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Definir fuso horário correto
date_default_timezone_set('America/Sao_Paulo');

$method = $_SERVER['REQUEST_METHOD'];

// Listar serviços
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listar_servicos') {
    $stmt = $pdo->query("SELECT id, nome, valor, duracao FROM servicos");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Listar atendentes
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listar_atendentes') {
    try {
        $stmt = $pdo->prepare("
            SELECT f.usuario_id, u.nome AS nome_atendente
            FROM profissionais f
            JOIN usuario u ON f.usuario_id = u.id
            WHERE f.profissao = 'manicure/pedicure' AND f.disponivel = 1
            ORDER BY u.nome ASC
        ");
        $stmt->execute();
        $atendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($atendentes)) {
            echo json_encode(["error" => "Nenhum atendente disponível no momento."]);
            exit;
        }

        echo json_encode($atendentes);
        exit;

    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro ao buscar atendentes: " . $e->getMessage()]);
        exit;
    }
}

// Listar horários disponíveis para um atendente específico
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listar_horarios') {
    $servico_id = $_GET['servico_id'] ?? null;
    $data = $_GET['data'] ?? null;
    $atendente = $_GET['atendente'] ?? null;

    if (!$servico_id || !$data || !$atendente) {
        echo json_encode(["error" => "Serviço, data e atendente são obrigatórios"]);
        exit;
    }

    $dataFormatada = date('Y-m-d', strtotime($data));
    $dataAtual = date('Y-m-d');
    if ($dataFormatada < $dataAtual) {
        echo json_encode(["error" => "Não é possível agendar para datas passadas."]);
        exit;
    }

    // Buscar duração do serviço
    $stmt = $pdo->prepare("SELECT duracao FROM servicos WHERE id = :servico_id");
    $stmt->execute([':servico_id' => $servico_id]);
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$servico) {
        echo json_encode(["error" => "Serviço não encontrado"]);
        exit;
    }

    $duracao = (int) $servico['duracao'];

    // Buscar horário de atendimento
    $stmt = $pdo->prepare("SELECT horario_abertura, horario_fechamento FROM horarios_atendimento LIMIT 1");
    $stmt->execute();
    $horario_atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario_atendimento) {
        echo json_encode(["error" => "Horário de atendimento não configurado"]);
        exit;
    }

    $inicio = strtotime($horario_atendimento['horario_abertura']);
    $fim = strtotime($horario_atendimento['horario_fechamento']);
    $intervalo = 15 * 60;

    $almoco_inicio = strtotime("12:00");
    $almoco_fim = strtotime("12:30");

    $horarios_grade = [];
    while ($inicio < $fim) {
        if ($inicio < $almoco_inicio || $inicio >= $almoco_fim) {
            $horarios_grade[] = date("H:i", $inicio);
        }
        $inicio += $intervalo;
    }
    $horarios_grade[] = date("H:i", $fim);

    // Buscar agendamentos do atendente
    $stmt = $pdo->prepare("
        SELECT a.horario, s.duracao
        FROM agendamentos a
        JOIN servicos s ON a.servico = s.id
        WHERE a.data = :data AND a.atendente = :atendente
    ");
    $stmt->execute([':data' => $dataFormatada, ':atendente' => $atendente]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar bloqueios do profissional
    // Descobrir o ID do profissional a partir do usuario_id
        $stmt = $pdo->prepare("SELECT id FROM profissionais WHERE usuario_id = :usuario_id");
        $stmt->execute([':usuario_id' => $atendente]);
        $prof = $stmt->fetch(PDO::FETCH_ASSOC);

        $profissional_id = $prof ? $prof['id'] : null;

        // Buscar bloqueios se encontrou
        $bloqueios = [];
        if ($profissional_id) {
            $stmt = $pdo->prepare("
                SELECT hora_inicio, hora_fim
                FROM bloqueios_profissionais
                WHERE profissional_id = :id AND data = :data
            ");
            $stmt->execute([':id' => $profissional_id, ':data' => $dataFormatada]);
            $bloqueios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }


    // Verificar horários disponíveis
    $horarios_livres = [];
    foreach ($horarios_grade as $horario) {
        $hora_inicio = strtotime($horario);
        $hora_fim = $hora_inicio + ($duracao * 60);
        $livre = true;

        // Bloquear horário de almoço
        if ($hora_inicio < $almoco_fim && $hora_fim > $almoco_inicio) {
            $livre = false;
        }

        // Verifica conflitos com agendamentos
        foreach ($agendamentos as $ag) {
            $ag_inicio = strtotime($ag['horario']);
            $ag_fim = $ag_inicio + ((int) $ag['duracao'] * 60);

            if (!($hora_fim <= $ag_inicio || $hora_inicio >= $ag_fim)) {
                $livre = false;
                break;
            }
        }

        // Verifica conflitos com bloqueios
        foreach ($bloqueios as $b) {
            $b_inicio = strtotime($b['hora_inicio']);
            $b_fim = strtotime($b['hora_fim']);

            if (!($hora_fim <= $b_inicio || $hora_inicio >= $b_fim)) {
                $livre = false;
                break;
            }
        }

        if ($livre) {
            $horarios_livres[] = $horario;
        }
    }

    // Resposta
    $response = [
        "horarios_disponiveis" => $horarios_livres
    ];

    echo json_encode($response);
    exit;
}

// Função POST
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['nome'], $data['telefone'], $data['data'], $data['horario'], $data['servico'], $data['atendente'])) {
        echo json_encode(["error" => "Todos os campos são obrigatórios"]);
        exit;
    }

    $dataFormatada = date('Y-m-d', strtotime($data['data']));
    $horarioFormatado = date('H:i:s', strtotime($data['horario']));
    $ipUsuario = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    try {
        // Limite de 5 agendamentos por IP OU user_agent baseado em created_at
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM agendamentos 
            WHERE (ipUsuario = :ip OR user_agent = :ua)
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ");
        $stmt->execute([
            ':ip' => $ipUsuario,
            ':ua' => $userAgent
        ]);
        if ($stmt->fetchColumn() >= 5) {
            echo json_encode(["error" => "Limite de 5 agendamentos por dia para este dispositivo foi atingido."]);
            exit;
        }

        // Verifica se o serviço existe
        $stmtServico = $pdo->prepare("SELECT nome, valor FROM servicos WHERE id = :servico_id");
        $stmtServico->execute([':servico_id' => $data['servico']]);
        $servico = $stmtServico->fetch(PDO::FETCH_ASSOC);
        if (!$servico) {
            echo json_encode(["error" => "Serviço não encontrado"]);
            exit;
        }

        // Verifica se o atendente existe
        $stmtAtendente = $pdo->prepare("
            SELECT u.nome AS nome_atendente
            FROM profissionais f
            JOIN usuario u ON f.usuario_id = u.id
            WHERE f.usuario_id = :usuario_id
        ");
        $stmtAtendente->execute([':usuario_id' => $data['atendente']]);
        $atendente = $stmtAtendente->fetch(PDO::FETCH_ASSOC);
        if (!$atendente) {
            echo json_encode(["error" => "Atendente não encontrado"]);
            exit;
        }

        // Insere o agendamento
        $stmt = $pdo->prepare("
            INSERT INTO agendamentos 
            (data, horario, servico, ipUsuario, user_agent, atendente, observacao, cliente_id, created_at) 
            VALUES 
            (:data, :horario, :servico, :ip, :ua, :atendente, :obs, :cliente_id, NOW())
        ");

        $stmt->execute([
            ':data' => $dataFormatada,
            ':horario' => $horarioFormatado,
            ':servico' => $data['servico'],
            ':ip' => $ipUsuario,
            ':ua' => $userAgent,
            ':atendente' => $data['atendente'],
            ':obs' => htmlspecialchars($data['observacao'] ?? ""),
            ':cliente_id' => $data['cliente_id'] ?? null,
        ]);

        echo json_encode([
            "success" => "Agendamento realizado com sucesso!",
            "nome" => $data['nome'],
            "telefone" => $data['telefone'],
            "data" => date('d/m/Y', strtotime($dataFormatada)),
            "horario" => $horarioFormatado,
            "servico" => $servico['nome'],
            "valor" => "R$ " . number_format($servico['valor'], 2, ',', '.'),
            "atendente" => $atendente['nome_atendente']
        ]);

    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro no banco de dados: " . $e->getMessage()]);
        exit;
    }
}

// Confirmar agendamento via botão
if (isset($_GET['acao']) && $_GET['acao'] === 'confirmar' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $valor = isset($_GET['valor']) ? (int) $_GET['valor'] : 1;
    
    $stmt = $pdo->prepare("UPDATE agendamentos SET confirmado = :valor WHERE id = :id");
    $stmt->execute([':id' => $id, ':valor' => $valor]);
    
    echo json_encode(['status' => 'success']);
    exit;
}


//bloquear dias para não atender
if (isset($_GET['action']) && $_GET['action'] === 'dias_permitidos') {
    $diasSemana = [
    'Segunda-feira' => 0,
    'Terça-feira' => 1,
    'Quarta-feira' => 2,
    'Quinta-feira' => 3,
    'Sexta-feira' => 4,
    'Sábado' => 5,
    'Domingo' => 6
];

    $stmt = $pdo->query("SELECT dia_inicio, dia_fim FROM horarios_atendimento LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Trim para evitar espaços invisíveis ou erros silenciosos
        $inicio = $diasSemana[trim($row['dia_inicio'])];
        $fim = $diasSemana[trim($row['dia_fim'])];

        $diasPermitidos = [];
        for ($i = $inicio; $i <= $fim; $i++) {
            $diasPermitidos[] = $i;
        }

        echo json_encode($diasPermitidos);
    } else {
        echo json_encode([]);
    }
    exit;
}

// Editar agendamento  
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'editar') {
    $dados = json_decode(file_get_contents('php://input'), true);

    $id = $dados['id'] ?? null;
    $data = $dados['data'] ?? null;
    $horario = $dados['horario'] ?? null;
    $servico = $dados['servico'] ?? null;
    $atendente = $dados['atendente'] ?? null;
    $observacao = $dados['observacao'] ?? null;

    if (!$data || !$horario || !$servico || !$atendente) {
        echo json_encode(['status' => 'error', 'mensagem' => 'Campos obrigatórios ausentes.']);
        exit;
    }

    $dataFormatada = date('Y-m-d', strtotime($data));
    $horarioFormatado = date('H:i:s', strtotime($horario));

    try {
        $stmt = $pdo->prepare("UPDATE agendamentos 
                               SET data = :data, horario = :horario, servico = :servico, atendente = :atendente, observacao = :obs
                               WHERE id = :id");
        $stmt->execute([
            ':data' => $dataFormatada,
            ':horario' => $horarioFormatado,
            ':servico' => $servico,
            ':atendente' => $atendente,
            ':obs' => $observacao,
            ':id' => $id
        ]);

        echo json_encode(['status' => 'success']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()]);
        exit;
    }
}



if (isset($_GET['action']) && $_GET['action'] === 'buscar' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    $id = (int) $_GET['id'];

    // Buscar dados do agendamento
    $stmt = $pdo->prepare("
        SELECT a.id, a.data, a.horario, a.atendente, a.observacao, c.nome, c.telefone
        FROM agendamentos a
        LEFT JOIN clientes c ON a.cliente_id = c.id
        WHERE a.id = :id

    ");
    $stmt->execute([':id' => $id]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        echo json_encode(['error' => 'Agendamento não encontrado']);
        exit;
    }

    // Buscar lista de atendentes para o <select>
    $stmtAt = $pdo->prepare("
        SELECT u.id as usuario_id, u.nome as nome_atendente
        FROM profissionais p
        JOIN usuario u ON p.usuario_id = u.id
        WHERE p.profissao = 'manicure/pedicure'
        ORDER BY u.nome ASC
    ");
    $stmtAt->execute();
    $atendentes = $stmtAt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
    'id' => $agendamento['id'],
    'data' => $agendamento['data'],
    'horario' => $agendamento['horario'],
    'atendente_id' => $agendamento['atendente'],
    'observacao' => $agendamento['observacao'],
    'nome' => $agendamento['nome'],
    'telefone' => $agendamento['telefone'],
    'atendentes' => $atendentes
    ]);
    exit;
}

?>

<?php
session_start();
require_once '../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($action === 'buscar' && isset($_GET['id'])) {
    $id = (int) $_GET['id']; // Certifique-se de que é um número inteiro válido

    $stmt = $pdo->prepare("SELECT id, nome, telefone, data, horario, servico FROM agendamentos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        echo json_encode(["error" => "Nenhum agendamento encontrado."]);
        exit;
    }

    echo json_encode($agendamento);
    exit;
}

// Listar todos os serviços disponíveis
if ($action === 'listar_servicos') {
    $stmt = $pdo->query("SELECT id, nome FROM servicos ORDER BY nome ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}


// Listar horários disponíveis corretamente
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'listar_horarios') {
    $servico_id = $_GET['servico_id'] ?? null;
    $data = $_GET['data'] ?? null;
    $agendamento_id = $_GET['agendamento_id'] ?? null; // ID do agendamento em edição

    if (!$servico_id || !$data) {
        echo json_encode(["error" => "Serviço e data são obrigatórios"]);
        exit;
    }

    // Formatar a data corretamente
    $dataFormatada = date('Y-m-d', strtotime($data));
    $dataAtual = date('Y-m-d');
    if ($dataFormatada < $dataAtual) {
        echo json_encode(["error" => "Não é possível agendar para datas passadas."]);
        exit;
    }

    // Buscar duração do serviço selecionado
    $stmt = $pdo->prepare("SELECT duracao FROM servicos WHERE id = :servico_id");
    $stmt->execute([':servico_id' => $servico_id]);
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$servico) {
        echo json_encode(["error" => "Serviço não encontrado"]);
        exit;
    }

    $duracao = (int) $servico['duracao']; // Duração do serviço em minutos

    // Buscar horários de funcionamento do dia
    $stmt = $pdo->prepare("SELECT horario_abertura, horario_fechamento FROM horarios_atendimento LIMIT 1");
    $stmt->execute();
    $horario_atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario_atendimento) {
        echo json_encode(["error" => "Horário de atendimento não configurado"]);
        exit;
    }

    $inicio = strtotime($horario_atendimento['horario_abertura']);
    $fim = strtotime($horario_atendimento['horario_fechamento']);
    $intervalo = 15 * 60; // Intervalo fixo de 15 minutos

    // Definir intervalo de refeição (12:00 - 12:30)
    $almoco_inicio = strtotime("12:00");
    $almoco_fim = strtotime("12:30");

    // Criar lista de horários possíveis
    $horarios_grade = [];
    while ($inicio < $fim) {
        $hora_fim = $inicio + ($duracao * 60);

        // Bloquear serviços que começam antes do almoço e terminam dentro dele
        // Bloquear serviços que começam dentro do almoço e terminam depois dele
        if (!($hora_fim <= $almoco_inicio || $inicio >= $almoco_fim)) {
            $inicio += $intervalo; // Pula esse horário e continua
            continue;
        }

        $horarios_grade[] = date("H:i", $inicio);
        $inicio += $intervalo;
    }

    // Adicionar o último horário do expediente como opção SEM restrição de fechamento
    $horarios_grade[] = date("H:i", $fim); // Agora, permite ultrapassar o fechamento

    // Buscar horários já ocupados no dia (excluindo o próprio agendamento em edição)
    $stmt = $pdo->prepare("SELECT horario, s.duracao FROM agendamentos a
                           JOIN servicos s ON a.servico = s.id
                           WHERE a.data = :data AND a.id != :agendamento_id");
    $stmt->execute([
        ':data' => $dataFormatada,
        ':agendamento_id' => $agendamento_id ?? 0 // Evita erro se for um novo agendamento
    ]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filtrar horários disponíveis, removendo os que não têm espaço suficiente
    $horarios_disponiveis = [];
    foreach ($horarios_grade as $horario) {
        $hora_inicio = strtotime($horario);
        $hora_fim = $hora_inicio + ($duracao * 60);
        $livre = true;

        foreach ($agendamentos as $agendamento) {
            $agend_hora_inicio = strtotime($agendamento['horario']);
            $agend_hora_fim = $agend_hora_inicio + ((int) $agendamento['duracao'] * 60);

            // Se o horário do novo serviço se sobrepõe a um agendamento existente, bloqueia
            if (!($hora_fim <= $agend_hora_inicio || $hora_inicio >= $agend_hora_fim)) {
                $livre = false;
                break;
            }
        }

        if ($livre) {
            $horarios_disponiveis[] = $horario;
        }
    }

    echo json_encode(["horarios_disponiveis" => array_values($horarios_disponiveis)]);
    exit;
}


// Atualizar um agendamento existente
if ($action === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $data = $_POST['data'];
    $horario = $_POST['horario'];
    $servico = $_POST['servico'];

    // Validação básica dos dados
    if (empty($nome) || empty($telefone) || empty($data) || empty($horario) || empty($servico)) {
        echo json_encode(["error" => "Todos os campos são obrigatórios."]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE agendamentos 
                           SET nome = :nome, telefone = :telefone, data = :data, horario = :horario, servico = :servico 
                           WHERE id = :id");
    $stmt->execute([
        ':nome' => $nome,
        ':telefone' => $telefone,
        ':data' => $data,
        ':horario' => $horario,
        ':servico' => $servico,
        ':id' => $id
    ]);

    echo json_encode(["mensagem" => "Agendamento atualizado com sucesso!"]);
    exit;
}

echo json_encode(["error" => "Ação inválida!"]);
exit;
?>

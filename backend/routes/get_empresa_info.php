<?php
require_once '../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Buscar dados da empresa 
    $stmt = $pdo->query("SELECT endereco, telefone, rede_social, cnpj, email, razao_social, descricao, logo FROM empresa WHERE id = 1 LIMIT 1");
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar horários de atendimento
    $stmt = $pdo->query("SELECT dia_inicio, dia_fim, horario_abertura, horario_fechamento, feriado FROM horarios_atendimento ORDER BY FIELD(dia_inicio, 'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado')");
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar serviços (limite de 5)
    $stmt = $pdo->query("SELECT id, nome FROM servicos ORDER BY id DESC LIMIT 5");
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retornar os dados como JSON
    echo json_encode(["empresa" => $empresa, "horarios" => $horarios, "servicos" => $servicos]);
} catch (Exception $e) {
    echo json_encode(["erro" => "Erro ao buscar informações: " . $e->getMessage()]);
}
?>
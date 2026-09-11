<?php
session_start();
require_once '../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$pdo = new PDO($dsn, $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Buscar os horários de atendimento cadastrados
$stmt = $pdo->query("SELECT dia_inicio, dia_fim, horario_abertura, horario_fechamento, feriado FROM horarios_atendimento LIMIT 1");
$horario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($horario) {
    // Retorna os dados corretamente formatados
    echo json_encode([
        "dia_inicio" => $horario["dia_inicio"],
        "dia_fim" => $horario["dia_fim"],
        "horario_abertura" => date("H:i", strtotime($horario["horario_abertura"])),
        "horario_fechamento" => date("H:i", strtotime($horario["horario_fechamento"])),
        "feriado" => $horario["feriado"]
    ]);
} else {
    echo json_encode(["error" => "Horário de atendimento não encontrado"]);
}
?>

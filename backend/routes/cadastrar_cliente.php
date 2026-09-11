<?php
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Requisição inválida.']);
    exit;
}

require '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['nome']) || empty($data['telefone'])) {
    http_response_code(422);
    echo json_encode(['error' => 'Nome e telefone são obrigatórios.']);
    exit;
}

// Limpa e valida telefone
$telefone = preg_replace('/\D/', '', $data['telefone']);

// Validação e normalização do nome
$nome = trim($data['nome']);

// Regras: 3–50 caracteres, apenas letras (acentos ok), espaço, hífen, apóstrofo
if (mb_strlen($nome) < 3 || mb_strlen($nome) > 50) {
    http_response_code(422);
    echo json_encode(['error' => 'O nome deve ter entre 3 e 50 caracteres.']);
    exit;
}

// Regra: pode conter letras, espaços, hífen e apóstrofo.
if (!preg_match("/^[A-Za-zÀ-ÿ' -]+$/u", $nome)) {
    http_response_code(422);
    echo json_encode(['error' => 'O nome só pode conter letras, espaços, hífen e apóstrofo.']);
    exit;
}

// Limita repetições seguidas a no máximo 5 caracteres iguais
if (preg_match('/([A-Za-zÀ-ÿ])\\1{5,}/u', $nome)) {
  http_response_code(422);
  echo json_encode(['error' => 'Nome com caracteres repetidos em excesso.']);
  exit;
}

// Padroniza para Maiúsculas: Cada palavra com inicial maiúscula
$nome = mb_convert_case($nome, MB_CASE_TITLE, "UTF-8");

try {
    // Verifica duplicidade de telefone
    $check = $pdo->prepare("SELECT id FROM clientes WHERE telefone = :telefone");
    $check->execute([':telefone' => $telefone]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Já existe um cliente com este telefone.']);
        exit;
    }

    // Insere cliente
    $sql = "INSERT INTO clientes (nome, telefone, data_cadastro) 
            VALUES (:nome, :telefone, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':telefone' => $telefone
    ]);

    echo json_encode([
        'id' => $pdo->lastInsertId(),
        'nome' => $nome,
        'telefone' => $telefone,
        'atualizado' => false
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro PDO: " . $e->getMessage());
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>

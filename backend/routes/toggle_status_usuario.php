<?php 
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_nivel'])) {
    echo json_encode(['error' => 'Sessão inválida ou expirada.']);
    exit;
}

$nivel = strtolower($_SESSION['admin_nivel']);
if (!in_array($nivel, ['admin', 'administrador'])) {
    echo json_encode(['error' => 'Acesso não autorizado.']);
    exit;
}


// Lê o JSON enviado
$dados = json_decode(file_get_contents('php://input'), true);
$id = $dados['id'] ?? null;
$statusAtual = $dados['status'] ?? null;

if (!$id || !$statusAtual) {
    echo json_encode(['error' => 'Dados inválidos.']);
    exit;
}

$novoStatus = $statusAtual === 'ativo' ? 'inativo' : 'ativo';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Verifica se o usuário existe
    $stmt = $pdo->prepare("SELECT nivel FROM usuario WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['error' => 'Usuário não encontrado.']);
        exit;
    }

    // Verificação flexível de administradores
    if (strtolower($usuario['nivel']) === 'admin' || strtolower($usuario['nivel']) === 'administrador') {
        echo json_encode(['error' => 'Não é possível alterar o status de administradores.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE usuario SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $novoStatus, ':id' => $id]);

    echo json_encode(['success' => "Usuário {$novoStatus} com sucesso."]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao alterar status: ' . $e->getMessage()]);
}

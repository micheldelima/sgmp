<?php
session_start();
require_once '../config/database.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado.']);
    exit;
}

$idUsuario = $_SESSION['admin_id'];

// Lê os dados recebidos via JSON
$dados = json_decode(file_get_contents("php://input"), true);
$senhaInformada = $dados['senha'] ?? '';

if (empty($senhaInformada)) {
    echo json_encode(['error' => 'Senha não informada.']);
    exit;
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Busca a senha atual no banco
    $stmt = $pdo->prepare("SELECT senha FROM usuario WHERE id = :id");
    $stmt->execute([':id' => $idUsuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        echo json_encode(['error' => 'Usuário não encontrado.']);
        exit;
    }

    // Verifica se a senha confere
    if (!password_verify($senhaInformada, $usuario['senha'])) {
        echo json_encode(['error' => 'Senha incorreta.']);
        exit;
    }

    // Exclui o usuário
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = :id");
    $stmt->execute([':id' => $idUsuario]);

    // Destroi a sessão
    session_destroy();

    echo json_encode(['success' => 'Conta excluída com sucesso.']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao excluir conta: ' . $e->getMessage()]);
}

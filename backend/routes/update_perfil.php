<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = $_SESSION['admin_id'];
$nome = trim($data['nome'] ?? '');
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$telefone = trim($data['telefone'] ?? '');
$senhaAtual = trim($data['senha_atual'] ?? '');
$novaSenha = trim($data['senha'] ?? '');

if (!$nome || !$username || !$email) {
    echo json_encode(['error' => 'Preencha todos os campos obrigatórios.']);
    exit;
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Verifica se username ou email já estão em uso por outro usuário
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE (username = :username OR email = :email) AND id != :id");
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':id' => $id
    ]);

    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Username ou email já estão em uso por outro usuário.']);
        exit;
    }

    // Verificação de senha atual se uma nova senha foi informada
    if (!empty($novaSenha)) {
        $stmt = $pdo->prepare("SELECT senha FROM usuario WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) {
            echo json_encode(['error' => 'Senha atual incorreta.']);
            exit;
        }

        if (strlen($novaSenha) < 8 || !preg_match('/[A-Za-z]/', $novaSenha) || !preg_match('/\d/', $novaSenha)) {
            echo json_encode(['error' => 'A nova senha deve ter pelo menos 8 caracteres, incluindo letras e números.']);
            exit;
        }

        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE usuario SET nome = :nome, username = :username, email = :email, telefone = :telefone, senha = :senha WHERE id = :id");
        $stmt->execute([
            ':nome' => $nome,
            ':username' => $username,
            ':email' => $email,
            ':telefone' => $telefone,
            ':senha' => $senhaHash,
            ':id' => $id
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuario SET nome = :nome, username = :username, email = :email, telefone = :telefone WHERE id = :id");
        $stmt->execute([
            ':nome' => $nome,
            ':username' => $username,
            ':email' => $email,
            ':telefone' => $telefone,
            ':id' => $id
        ]);
    }

    echo json_encode(['success' => 'Perfil atualizado com sucesso!']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao atualizar perfil: ' . $e->getMessage()]);
}

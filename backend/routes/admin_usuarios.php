<?php
session_start();
require_once '../config/database.php';

// Configuração CORS
$origem_permitida = "http://localhost:3000";
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $origem_permitida) {
    header("Access-Control-Allow-Origin: $origem_permitida");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// CORS pré-flight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autenticação para métodos que não sejam GET
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    require_once '../config/auth_middleware.php';
}

// Conexão segura com o banco de dados
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao conectar ao banco de dados!"]);
    exit;
}

// Função para validar senha
function validarSenha($senha) {
    return strlen($senha) >= 8 && preg_match('/[A-Za-z]/', $senha) && preg_match('/\d/', $senha);
}

switch ($method) {
    // lista os usuarios
        case 'GET':
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $limite = 7;
            $offset = ($pagina - 1) * $limite;

            try {
                $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS id, nome, username, email, telefone, nivel, status, data_criacao
                                       FROM usuario
                                       ORDER BY nome ASC
                                       LIMIT :limite OFFSET :offset");
                $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();

                $usuarios = $stmt->fetchAll();
                $total = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

                echo json_encode([
                    "usuarios" => $usuarios,
                    "total" => (int)$total,
                    "pagina" => $pagina,
                    "limite" => $limite
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Erro ao buscar usuários: " . $e->getMessage()]);
            }
            break;


    case 'POST': // CADASTRAR USUÁRIO
        $data = json_decode(file_get_contents("php://input"), true);

        // Validação
        if (empty($data['nome']) || empty($data['username']) || empty($data['email']) || 
            empty($data['nivel']) || empty($data['senha'])) {
            echo json_encode(["error" => "Todos os campos são obrigatórios!"]);
            exit;
        }

        // Validação de formato
        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $data['username'])) {
            echo json_encode(["error" => "Nome de usuário inválido (4-20 caracteres, letras, números ou _)"]);
            exit;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => "E-mail inválido!"]);
            exit;
        }

        if (!validarSenha($data['senha'])) {
            echo json_encode(["error" => "Senha deve ter 8+ caracteres com letras e números"]);
            exit;
        }

        // Verifica se e-mail ou username já existem
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = :email OR username = :username");
            $stmt->execute([
                ':email' => $data['email'],
                ':username' => $data['username']
            ]);

            if ($stmt->fetch()) {
                $pdo->rollBack();
                echo json_encode(["error" => "E-mail ou nome de usuário já cadastrado"]);
                exit;
            }

            // Cadastra o usuário
            $stmt = $pdo->prepare("INSERT INTO usuario 
                (nome, username, email, telefone, senha, nivel, data_criacao) 
                VALUES (:nome, :username, :email, :telefone, :senha, :nivel, NOW())");

            $stmt->execute([
                ':nome' => htmlspecialchars($data['nome']),
                ':username' => htmlspecialchars($data['username']),
                ':email' => htmlspecialchars($data['email']),
                ':telefone' => htmlspecialchars($data['telefone'] ?? ''),
                ':senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
                ':nivel' => in_array($data['nivel'], ['Administrador', 'Funcionario']) ? $data['nivel'] : 'Funcionario'
            ]);

            $pdo->commit();
            echo json_encode(["success" => "Usuário cadastrado com sucesso!"]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["error" => "Erro ao cadastrar usuário: " . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["error" => "Método não permitido!"]);
        break;
}
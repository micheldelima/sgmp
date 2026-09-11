<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Método POST
if ($method === 'POST' && isset($_FILES['logo'])) {

    // Verifica se o arquivo foi enviado corretamente
    if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Erro ao enviar o arquivo.']);
        exit;
    }

    $arquivo = $_FILES['logo'];

    // Verifica tamanho (máximo 2 MB)
    $tamanhoMaximo = 2 * 1024 * 1024; // 2 MB
    if ($arquivo['size'] > $tamanhoMaximo) {
        echo json_encode(['error' => 'A imagem não pode exceder 2 MB.']);
        exit;
    }

    // Extensões permitidas
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    if (!in_array($extensao, $extensoesPermitidas)) {
        echo json_encode(['error' => 'Formato inválido! Envie JPG, PNG, GIF ou WEBP.']);
        exit;
    }

    // Gera nome único e define caminho
    $nomeArquivo = 'logo_empresa_' . uniqid() . '.' . $extensao;
    $diretorioDestino = __DIR__ . '/../public/assets/img/logo/';

    // Cria o diretório se não existir
    if (!is_dir($diretorioDestino)) {
        mkdir($diretorioDestino, 0777, true);
    }

    $caminhoFinal = $diretorioDestino . $nomeArquivo;

    // Move o arquivo enviado
    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoFinal)) {
        echo json_encode(['error' => 'Erro ao salvar a imagem no servidor.']);
        exit;
    }

    // Atualiza a logo no banco de dados
    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare("UPDATE empresa SET logo = :logo WHERE id = 1");
        $stmt->execute([':logo' => $nomeArquivo]);

        echo json_encode(['success' => 'Logo atualizada com sucesso!', 'logo' => $nomeArquivo]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao atualizar o banco: ' . $e->getMessage()]);
    }

    exit;
}

//(GET, PUT)
switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM empresa LIMIT 1");
        $empresa = $stmt->fetch();
        echo json_encode($empresa ?: []);
        break;

    case 'PUT':
    $data = json_decode(file_get_contents('php://input'), true);
    $camposObrigatorios = ['razao_social', 'endereco', 'cnpj', 'telefone', 'email'];

    foreach ($camposObrigatorios as $campo) {
        if (empty($data[$campo])) {
            http_response_code(400);
            echo json_encode(['error' => "Campo '$campo' é obrigatório."]);
            exit;
        }
    }

    // Validação do telefone (somente números com 10 ou 11 dígitos)
    $telefoneLimpo = preg_replace('/\D/', '', $data['telefone']);
    if (!preg_match('/^\d{10,11}$/', $telefoneLimpo)) {
        http_response_code(400);
        echo json_encode(['error' => 'Número de telefone inválido. Informe um número com DDD no formato (99) 99999-9999.']);
        exit;
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM empresa");
    $existe = $stmt->fetchColumn();

    if ($existe) {
        $stmt = $pdo->prepare("UPDATE empresa SET razao_social = :razao_social, endereco = :endereco, cnpj = :cnpj, telefone = :telefone, email = :email, rede_social = :rede_social, descricao = :descricao");
    } else {
        $stmt = $pdo->prepare("INSERT INTO empresa (razao_social, endereco, cnpj, telefone, email, rede_social, descricao) VALUES (:razao_social, :endereco, :cnpj, :telefone, :email, :rede_social, :descricao)");
    }

    $stmt->execute([
        ':razao_social' => $data['razao_social'],
        ':endereco' => $data['endereco'],
        ':cnpj' => $data['cnpj'],
        ':telefone' => $telefoneLimpo,
        ':email' => $data['email'],
        ':rede_social' => $data['rede_social'] ?? '',
        ':descricao' => $data['descricao'] ?? ''
    ]);

    echo json_encode(['success' => 'Dados da empresa salvos com sucesso.']);
    break;
}

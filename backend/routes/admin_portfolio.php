<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$pdo = new PDO($dsn, $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$method = $_SERVER['REQUEST_METHOD'];

//busca
switch ($method) {
    case 'GET':
        $busca = $_GET['busca'] ?? '';
        $pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
        $itensPorPagina = 3;

        $pagina = max(1, $pagina);
        $offset = ($pagina - 1) * $itensPorPagina;

        $buscaSql = $busca ? "WHERE titulo LIKE :busca" : '';
        $query = "SELECT id, titulo, imagem, descricao, data_criacao FROM portfolio $buscaSql ORDER BY id DESC LIMIT :offset, :limit";
        $stmt = $pdo->prepare($query);

        if ($busca) {
            $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
        $stmt->execute();

        $portfolio = $stmt->fetchAll();
        foreach ($portfolio as &$item) {
            $item['imagem'] = '../public/assets/img/portfolio/' . $item['imagem'];
        }

        $queryTotal = "SELECT COUNT(*) FROM portfolio $buscaSql";
        $stmtTotal = $pdo->prepare($queryTotal);
        if ($busca) {
            $stmtTotal->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $totalItens = (int) $stmtTotal->fetchColumn();
        $totalPaginas = max(1, ceil($totalItens / $itensPorPagina));

        echo json_encode([
            'portfolio' => $portfolio,
            'totalPaginas' => $totalPaginas
        ]);
        break;

//POST
    case 'POST':
    $id = $_POST['id'] ?? null;
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $imagem = $_FILES['imagem'] ?? null;
    $tamanhoMaximo = 2 * 1024 * 1024; // 2MB

    if (!$titulo || !$descricao) {
        echo json_encode(['error' => 'Título e descrição são obrigatórios.']);
        exit;
    }

    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // EDIÇÃO
    if ($id) {
        $stmtCheck = $pdo->prepare("SELECT imagem FROM portfolio WHERE id = :id");
        $stmtCheck->execute([':id' => $id]);
        $item = $stmtCheck->fetch();
        if (!$item) {
            echo json_encode(['error' => 'Item não encontrado.']);
            exit;
        }

        $imagemNome = $item['imagem'];

        if ($imagem && $imagem['error'] === UPLOAD_ERR_OK) {
            if ($imagem['size'] > $tamanhoMaximo) {
                echo json_encode(['error' => 'A imagem excede o tamanho máximo de 2MB.']);
                exit;
            }

            $tipo = mime_content_type($imagem['tmp_name']);
            if (!in_array($tipo, $tiposPermitidos)) {
                echo json_encode(['error' => 'Tipo de imagem inválido.']);
                exit;
            }

            $infoImagem = getimagesize($imagem['tmp_name']);
            if ($infoImagem === false) {
                echo json_encode(['error' => 'O arquivo enviado não é uma imagem válida.']);
                exit;
            }

            $ext = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $extensoesPermitidas)) {
                echo json_encode(['error' => 'Extensão de arquivo não permitida.']);
                exit;
            }

            $imagemNome = uniqid('img_', true) . '.' . $ext;
            $destino = '../public/assets/img/portfolio/' . $imagemNome;

            if (!move_uploaded_file($imagem['tmp_name'], $destino)) {
                echo json_encode(['error' => 'Erro ao salvar imagem.']);
                exit;
            }

            if ($item['imagem'] && file_exists('../public/assets/img/portfolio/' . $item['imagem'])) {
                unlink('../public/assets/img/portfolio/' . $item['imagem']);
            }
        }

        $stmt = $pdo->prepare("UPDATE portfolio SET titulo = :titulo, descricao = :descricao, imagem = :imagem WHERE id = :id");
        $stmt->execute([
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':imagem' => $imagemNome,
            ':id' => $id
        ]);

        echo json_encode(['success' => 'Item atualizado com sucesso!']);
        exit;
    }

    // CADASTRO NOVO
    if (!$imagem || $imagem['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'A imagem é obrigatória.']);
        exit;
    }

    if ($imagem['size'] > $tamanhoMaximo) {
        echo json_encode(['error' => 'A imagem excede o tamanho máximo de 2MB.']);
        exit;
    }

    $tipo = mime_content_type($imagem['tmp_name']);
    if (!in_array($tipo, $tiposPermitidos)) {
        echo json_encode(['error' => 'Tipo de imagem inválido.']);
        exit;
    }

    $infoImagem = getimagesize($imagem['tmp_name']);
    if ($infoImagem === false) {
        echo json_encode(['error' => 'O arquivo enviado não é uma imagem válida.']);
        exit;
    }

    $ext = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extensoesPermitidas)) {
        echo json_encode(['error' => 'Extensão de arquivo não permitida.']);
        exit;
    }

    $imagemNome = uniqid('img_', true) . '.' . $ext;
    $destino = '../public/assets/img/portfolio/' . $imagemNome;

    if (!move_uploaded_file($imagem['tmp_name'], $destino)) {
        echo json_encode(['error' => 'Erro ao salvar imagem.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO portfolio (titulo, descricao, imagem, data_criacao) VALUES (:titulo, :descricao, :imagem, NOW())");
    $stmt->execute([
        ':titulo' => $titulo,
        ':descricao' => $descricao,
        ':imagem' => $imagemNome
    ]);

    echo json_encode(['success' => 'Item cadastrado com sucesso!']);
    break;

//PUT
    case 'PUT':
        // Reencaminha para o POST  
        $_POST = $_REQUEST;
        $_FILES = $_FILES ?? [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        include __FILE__;
        break;

    case 'DELETE': 
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        echo json_encode(['error' => 'ID inválido.']);
        exit;
    }

    // Verifica se o item existe
    $stmtImg = $pdo->prepare("SELECT imagem FROM portfolio WHERE id = :id");
    $stmtImg->execute([':id' => $id]);
    $img = $stmtImg->fetchColumn();

    if (!$img) {
        echo json_encode(['error' => 'Item não encontrado no banco.']);
        exit;
    }

    // Caminho absoluto da imagem
    $caminhoImagem = "../public/assets/img/portfolio/" . basename($img);

    // Tenta excluir a imagem
    if (file_exists($caminhoImagem)) {
        if (!unlink($caminhoImagem)) {
            echo json_encode(['error' => 'Não foi possível excluir a imagem do servidor.']);
            exit;
        }
    }

    // Excluir do banco
    $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        echo json_encode(['success' => 'Item excluído com sucesso!']);
    } else {
        echo json_encode(['error' => 'Erro ao excluir item do banco.']);
    }
    break;


}

<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

// Conectar ao banco de dados
$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Filtro de busca
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';
$buscaSql = $busca ? "WHERE titulo LIKE :busca" : '';

// Configuração da paginação
$itensPorPagina = 3;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Buscar portfólios com paginação e busca
$query = "SELECT id, titulo, imagem, descricao FROM portfolio $buscaSql ORDER BY titulo ASC LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);

if (!empty($busca)) {
    $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$portfolio = $stmt->fetchAll();

// Contar total de portfólios para paginação
$queryTotal = "SELECT COUNT(*) FROM portfolio $buscaSql";
$stmtTotal = $pdo->prepare($queryTotal);
if (!empty($busca)) {
    $stmtTotal->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalPortfolio = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, ceil($totalPortfolio / $itensPorPagina));

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfólio - SGMP</title>
    <!-- head.php -->
    <?php include __DIR__ . '/../includes/head.php'; ?>

</head>

<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <!-- Conteúdo Principal centralizado -->
    <div class="container content py-4">
      <h2><i class="bi bi-images"></i> Gerenciar Portfólio</h2>

<!-- Alerta com botão de ajuda -->
<div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
  <div>
    <i class="bi bi-question-circle-fill fs-4 me-2"></i>
    Precisa de ajuda?
  </div>
  <button 
    class="btn btn-primary btn-sm ms-5 text-write"
    data-bs-toggle="modal" 
    data-bs-target="#modalHelpPortfolio"
  >
    <i class="bi bi-question-circle me-1"></i> Ver Ajuda
  </button>
</div>

<!-- Modal de Ajuda -->
<div class="modal fade" id="modalHelpPortfolio" tabindex="-1" aria-labelledby="helpPortfolioLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="helpPortfolioLabel">
          <i class="bi bi-question-circle"></i> Ajuda - Gerenciar Portfólio
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="list-unstyled">
          <li>
            <i class="bi bi-plus-circle-fill me-2 text-success"></i>
            Clique em <strong>Cadastrar Item</strong> para cadastrar um novo item no portfólio com <strong>título, descrição e imagem</strong>.
          </li>
          <li>
            <i class="bi bi-image-fill me-2 text-primary"></i>
            Envie imagens no formato <strong>JPG, PNG, JPEG, WEBP</strong> de até <strong>2 MB</strong>.
          </li>
          <li>
            <i class="bi bi-pencil-square me-2 text-warning"></i>
            Utilize o botão <strong>Editar</strong> para alterar as informações de um item já cadastrado.
          </li>
          <li>
            <i class="bi bi-search me-2 text-primary"></i>
            Use o campo de <strong>busca</strong> para localizar rapidamente um item pelo título.
          </li>
          <li>
            <i class="bi bi-trash-fill me-2 text-danger"></i>
            Clique em <strong>Excluir</strong> para remover um item permanentemente do portfólio.
          </li>
          <li>
            <i class="bi bi-eye-fill me-2 text-secondary"></i>
            A listagem mostra todos os itens cadastrados com pré-visualização da imagem.
          </li>
          <li>
            <i class="bi bi-lightbulb-fill me-2 text-info"></i>
            Dica: mantenha o portfólio atualizado com fotos recentes para atrair novos clientes.
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<hr>

                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 gap-md-3 mb-3">

                  <!-- Botão -->
                  <div class="w-100 w-md-auto">
                    <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalPortfolio">
                      <i class="bi bi-plus-circle me-2"></i> Cadastrar Item
                    </button>
                  </div>

                  <!-- Campo de busca -->
                  <div class="position-relative w-100 w-md-auto flex-grow-1 search-box">
                    <input type="text" id="busca-portfolio" class="form-control ps-5" placeholder="Buscar item">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-dark" style="z-index: 5; pointer-events: none;"></i>
                  </div>
                </div>

                <small class="d-block mb-2 text-primary fw-bold">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                        Role para o lado para ver mais informações
                </small>
                
                <!-- Tabela de portfólio -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                          <tr>
                            <th style="min-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                              <i class="bi bi-card-heading me-1"></i> Título
                            </th>
                            <th style="min-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                              <i class="bi bi-image me-1"></i> Imagem
                            </th>
                            <th style="min-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                              <i class="bi bi-card-text me-1"></i> Descrição
                            </th>
                            <th style="min-width: 100px; white-space: nowrap;">
                              <i class="bi bi-gear me-1"></i> Ações
                            </th>
                          </tr>
                        </thead>

                        <tbody id="portfolio-table">
                            <!-- Os itens do portfólio serão carregados aqui dinamicamente -->
                        </tbody>
                    </table>
                </div>

                <!-- Mensagem de nenhum item encontrado -->
                <p id="mensagem-vazia" class="text-center text-muted mt-3" style="display: none;">Nenhum item encontrado.</p>

                <small class="d-block mb-2 text-primary fw-bold">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                        Role para o lado para ver mais informações
                </small>
                
                <!-- Paginação -->
                <nav>
                    <ul id="paginacao" class="pagination justify-content-center">
                        <!-- Os links de paginação serão carregados aqui dinamicamente -->
                    </ul>
                </nav>
        </div>
    </div>

    <!-- Modal de Cadastro e Edição -->
    <div class="modal fade" id="modalPortfolio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title"><i class="bi bi-plus-lg"></i> Cadastrar Item ao Portfólio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="portfolio-id">
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-card-heading"></i> Título</label>
                        <input type="text" id="portfolio-titulo" class="form-control" required maxlength="100" placeholder="Digite o título">
                    </div>
                    <div class="mb-3">
                      <label class="form-label"><i class="bi bi-image"></i> Imagem</label>
                      <input type="file" id="portfolio-imagem" class="form-control" accept="image/*" required placeholder="Selecione uma imagem">
                      <div class="form-text text-muted">
                        Arquivos permitidos: <strong>.jpg, .jpeg, .png, .webp.</strong> Tamanho máximo: <strong>2MB</strong>
                      </div>
                    </div>

                    <div id="progressoPortfolio" class="mt-2" style="display: none;">
                      <div class="progress">
                        <div id="barraProgressoPortfolio" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                      </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-card-text"></i> Descrição</label>
                        <textarea id="portfolio-descricao" class="form-control" required maxlength="200" placeholder="Digite a descrição"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-lg" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Fechar</button>
                    <button class="btn btn-primary btn-lg" onclick="salvarPortfolio()"><i class="bi bi-check-circle"></i> Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/portfolio.js"></script>

    <?php include __DIR__ . '/../includes/rodape.php'; ?>
</body>
</html>
<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

// Conectar ao banco de dados
$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Filtro de busca
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';
$buscaSql = $busca ? "WHERE nome LIKE :busca" : '';

// Configuração da paginação
$itensPorPagina = 7;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Buscar serviços com paginação e busca
$query = "SELECT id, nome, descricao, valor, duracao FROM servicos $buscaSql ORDER BY nome ASC LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);

if (!empty($busca)) {
    $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$servicos = $stmt->fetchAll();

// Contar total de serviços para paginação
$queryTotal = "SELECT COUNT(*) FROM servicos $buscaSql";
$stmtTotal = $pdo->prepare($queryTotal);
if (!empty($busca)) {
    $stmtTotal->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalServicos = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, ceil($totalServicos / $itensPorPagina));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - SGMP</title>
    <!-- head.php -->
    <?php include __DIR__ . '/../includes/head.php'; ?>

</head>

<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <!-- Conteúdo Principal centralizado -->
    <div class="container content py-4">
  <h2><i class="bi bi-scissors"></i> Gerenciar Serviços</h2>
              
<!-- Alerta com botão de ajuda -->
<div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
  <div>
    <i class="bi bi-question-circle-fill fs-4 me-2"></i>
    Precisa de ajuda?
  </div>
  <button 
    class="btn btn-primary btn-sm ms-5 text-write"
    data-bs-toggle="modal" 
    data-bs-target="#modalHelpServicos"
  >
    <i class="bi bi-question-circle me-1"></i> Ver Ajuda
  </button>
</div>

<!-- Modal de Ajuda -->
<div class="modal fade" id="modalHelpServicos" tabindex="-1" aria-labelledby="helpServicosLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="helpServicosLabel">
          <i class="bi bi-question-circle"></i> Ajuda - Gerenciar Serviços
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="list-unstyled">
          <li>
            <i class="bi bi-plus-circle-fill me-2 text-success"></i>
            Clique em <strong>Cadastrar Serviço</strong> para cadastrar um novo serviço com nome, descrição, valor e duração em minutos.
          </li>
          <li>
            <i class="bi bi-pencil-square me-2 text-primary"></i>
            Use o botão <strong>Editar</strong> para atualizar informações do serviço, como nome, descrição, valor e duração.
          </li>
          <li>
            <i class="bi bi-trash me-2 text-danger"></i>
            O botão <strong>Excluir</strong> remove o serviço selecionado do sistema de forma permanente.
          </li>
          <li>
            <i class="bi bi-search me-2 text-primary"></i>
            Utilize o campo de <strong>busca</strong> para localizar serviços rapidamente pelo nome.
          </li>
          <li>
            <i class="bi bi-filter-circle me-2 text-warning"></i>
            Use a <strong> paginação</strong> para navegar entre os serviços cadastrados de maneira mais fácil.
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<hr>

                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 gap-md-3 mb-3">

                  <!-- Botão: full-width no mobile, compacto no desktop -->
                  <div class="w-100 w-md-auto">
                    <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalServico">
                      <i class="bi bi-plus-circle me-2"></i> Cadastrar Serviço
                    </button>
                  </div>

                  <!-- Busca: full-width no mobile; largura limitada no desktop -->
                  <div class="position-relative w-100 w-md-auto flex-grow-1 search-box">
                    <input type="text" id="busca-servico" class="form-control ps-5" placeholder="Buscar serviço">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-dark" style="z-index: 5; pointer-events:none;"></i>
                  </div>
                </div>

                <small class="d-block mb-2 text-primary fw-bold">
                <i class="bi bi-arrow-right-circle me-1"></i>
                    Role para o lado para ver mais informações
                </small>

                <!-- Tabela de serviços -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                          <tr>
                            <th class="text-truncate" style="min-width: 180px;"><i class="bi bi-file-text"></i> Nome</th>
                            <th class="text-truncate" style="min-width: 400px;"><i class="bi bi-card-text"></i> Descrição</th>
                            <th class="text-truncate" style="min-width: 140px;"><i class="bi bi-currency-dollar"></i> Valor (R$)</th>
                            <th class="text-truncate" style="min-width: 120px;"><i class="bi bi-clock"></i> Duração</th>
                            <th class="text-truncate" style="min-width: 130px;"><i class="bi bi-gear"></i> Ações</th>
                          </tr>
                        </thead>
                        <tbody id="servicos-table">
                            <!-- Os serviços serão carregados aqui dinamicamente -->
                        </tbody>
                    </table>
                </div>

                <!-- Mensagem de nenhum serviço encontrado -->
                <p id="mensagem-vazia" class="text-center text-muted mt-3" style="display: none;">Nenhum serviço encontrado.</p>

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
            </main>
        </div>
    </div>

    <!-- Modal de Cadastro e Edição -->
    <div class="modal fade" id="modalServico" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title"><i class="bi bi-plus-lg"></i> Cadastrar Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="servico-id">
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-tag"></i> Nome</label>
                        <input type="text" id="servico-nome" class="form-control" required maxlength="100" placeholder="Digite o nome do serviço">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-file-text"></i> Descrição</label>
                        <input type="text" id="servico-descricao" class="form-control" required maxlength="200" placeholder="Um resumo breve do serviço">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-cash"></i> Valor (R$)</label>
                        <input type="number" id="servico-valor" class="form-control" step="0.01" required placeholder="Digite o Valor">
                    </div>
                    <div class="mb-3">
                    <label for="servico-duracao" class="form-label">
                      <i class="bi bi-clock"></i> Tempo de duração do serviço
                    </label>
                    <input 
                      type="number" 
                      id="servico-duracao" 
                      class="form-control" 
                      required 
                      placeholder="Digite a duração em minutos"
                      min="5" 
                      step="5"
                    >
                    <small class="form-text text-muted">
                      Informe o tempo total do serviço em minutos. Exemplo.: 1h = 60 min
                    </small>
                </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-lg" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Fechar</button>
                    <button class="btn btn-primary btn-lg" onclick="salvarServico()"><i class="bi bi-check-circle"></i> Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/servicos.js"></script>
    
    <?php include __DIR__ . '/../includes/rodape.php'; ?>
</body>
</html>

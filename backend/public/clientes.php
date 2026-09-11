<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$busca = isset($_GET['busca']) ? $_GET['busca'] : '';
$buscaSql = $busca ? "WHERE nome LIKE :busca OR telefone LIKE :busca" : '';

$itensPorPagina = 7;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

$query = "SELECT id, telefone, nome, data_cadastro FROM clientes $buscaSql ORDER BY nome ASC LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);

if (!empty($busca)) {
    $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$clientes = $stmt->fetchAll();

$queryTotal = "SELECT COUNT(*) FROM clientes $buscaSql";
$stmtTotal = $pdo->prepare($queryTotal);
if (!empty($busca)) {
    $stmtTotal->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalClientes = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, ceil($totalClientes / $itensPorPagina));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Clientes - SGMP</title>
    
    <?php include __DIR__ . '/../includes/head.php'; ?>
    
</head>

<body class="bg-light">
<?php include __DIR__ . '/../includes/menu_superior.php'; ?>

<div class="container content py-4">
    <h2><i class="bi bi-person"></i> Gerenciar Clientes</h2>

<!-- Alerta com botão de ajuda -->
<div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
  <div>
    <i class="bi bi-question-circle-fill fs-4 me-2"></i>
    Precisa de ajuda?
  </div>
  <button 
    class="btn btn-primary btn-sm ms-5 text-write"
    data-bs-toggle="modal" 
    data-bs-target="#modalHelpClientes"
  >
    <i class="bi bi-question-circle me-1"></i> Ver Ajuda
  </button>
</div>

<!-- Modal de Ajuda -->
<div class="modal fade" id="modalHelpClientes" tabindex="-1" aria-labelledby="helpClientesLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="helpClientesLabel">
          <i class="bi bi-question-circle"></i> Ajuda - Gerenciar Clientes
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="list-unstyled">
          <li>
            <i class="bi bi-person-plus-fill me-2 text-success"></i>
            Clique em <strong>Cadastrar Cliente</strong> para cadastrar um novo registro com nome e telefone.
          </li>
          <li>
            <i class="bi bi-pencil-square me-2 text-primary"></i>
            Use o botão <strong>Editar</strong> para atualizar informações existentes, como nome ou telefone.
          </li>
          <li>
            <i class="bi bi-trash me-2 text-danger"></i>
            O botão <strong>Excluir</strong> remove o cliente do sistema de forma permanente.
          </li>
          <li>
            <i class="bi bi-search me-2 text-primary"></i>
            Utilize o campo de <strong>busca</strong> para localizar clientes rapidamente pelo nome ou telefone.
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
    <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalCliente">
      <i class="bi bi-plus-circle me-2"></i> Cadastrar Cliente
    </button>
  </div>

  <!-- Busca -->
  <div class="position-relative w-100 w-md-auto flex-grow-1 search-box">
    <input type="text" id="busca-cliente" class="form-control ps-5" placeholder="Buscar cliente">
    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-dark" style="z-index: 5; pointer-events: none;"></i>
  </div>
</div>

<small class="d-block mb-2 text-primary fw-bold">
  <i class="bi bi-arrow-right-circle me-1"></i>
    Role para o lado para ver mais informações
</small>

    <div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th style="min-width: 150px; white-space: nowrap;">
                    <i class="bi bi-person me-1"></i> Nome
                </th>
                <th style="min-width: 150px; white-space: nowrap;">
                    <i class="bi bi-telephone me-1"></i> Telefone
                </th>
                <th style="min-width: 150px; white-space: nowrap;">
                    <i class="bi bi-calendar-check me-1"></i> Data Cadastro
                </th>
                <th style="min-width: 100px; white-space: nowrap;">
                    <i class="bi bi-gear me-1"></i> Ações
                </th>
            </tr>
        </thead>
            <tbody id="clientes-table">
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= htmlspecialchars($cliente['nome']) ?></td>
                        <td><?= htmlspecialchars($cliente['telefone']) ?></td>      
                        <td><?= date('d/m/Y', strtotime($cliente['data_cadastro'])) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editarCliente(<?= $cliente['id'] ?>, '<?= htmlspecialchars($cliente['nome']) ?>', '<?= htmlspecialchars($cliente['telefone']) ?>',)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="excluirCliente(<?= $cliente['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p id="mensagem-vazia" class="text-center text-muted mt-3" style="display: none;">
          Nenhum cliente encontrado.
    </p>

    <small class="d-block mb-2 text-primary fw-bold">
        <i class="bi bi-arrow-right-circle me-1"></i>
        Role para o lado para ver mais informações
    </small>

    <nav>
        <ul id="paginacao" class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i === $paginaAtual ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modal-title" class="modal-title"><i class="bi bi-person-plus"></i> Cadastrar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cliente-id">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person"></i> Nome</label>
                    <input type="text" id="nome" class="form-control" maxlength="100" required placeholder="Digite o nome">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-whatsapp"></i> WhatsApp</label>
                    <input type="text" id="cliente-telefone" class="form-control" maxlength="20" required placeholder="(xx) xxxxx - xxxx">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Fechar
                </button>
                <button class="btn btn-primary btn-lg" onclick="salvarCliente()">
                    <i class="bi bi-check-circle"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/clientes.js"></script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
</body>
</html>

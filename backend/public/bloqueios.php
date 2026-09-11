<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$busca = isset($_GET['busca']) ? $_GET['busca'] : '';
$buscaSql = $busca ? "WHERE u.nome LIKE :busca" : '';

$itensPorPagina = 7;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Buscar profissionais disponíveis 
$stmtProfissionais = $pdo->prepare("SELECT p.id, u.nome 
    FROM profissionais p 
    JOIN usuario u ON p.usuario_id = u.id 
    WHERE p.disponivel = 1 
    ORDER BY u.nome ASC");
$stmtProfissionais->execute();
$profissionais = $stmtProfissionais->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT b.id, b.data, b.hora_inicio, b.hora_fim, b.motivo, u.nome as profissional FROM bloqueios_profissionais b JOIN profissionais p ON b.profissional_id = p.id JOIN usuario u ON p.usuario_id = u.id $buscaSql ORDER BY b.data DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);

if (!empty($busca)) {
    $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$bloqueios = $stmt->fetchAll();

$queryTotal = "SELECT COUNT(*) FROM bloqueios_profissionais b JOIN profissionais p ON b.profissional_id = p.id JOIN usuario u ON p.usuario_id = u.id $buscaSql";
$stmtTotal = $pdo->prepare($queryTotal);
if (!empty($busca)) {
    $stmtTotal->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalBloqueios = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, ceil($totalBloqueios / $itensPorPagina));

  try {
      $pdo = new PDO($dsn, $db_user, $db_pass, $options);
      $stmt = $pdo->prepare("SELECT nome FROM usuario WHERE nivel = 'Administrador'");
      $stmt->execute();
      $administradores = $stmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (PDOException $e) {
      $administradores = [];
  }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bloqueios - SGMP</title>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body class="bg-light">
  <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

  <div class="container content py-4">
  <?php if (isset($_SESSION['admin_nivel']) && $_SESSION['admin_nivel'] === 'funcionario'): ?>
    <h2><i class="bi bi-x-octagon"></i> Gerenciar Bloqueios de Horários dos Profissionais</h2>
    <hr>
    <h3 class="text-danger"><i class="bi bi-shield-lock-fill me-2"></i> Acesso Restrito</h3>
    <p class="lead">Você não tem permissão para acessar esta página.</p>
    <div class="alert alert-warning mt-3">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> Esta área é exclusiva para administradores.
    </div>
    <p class="text-muted">
      Se você acredita que isso é um erro, entre em contato com o(s) administrador(es):
      <strong><?= implode(', ', $administradores) ?: 'Não encontrado' ?></strong>.
    </p>
  <?php else: ?>

    <h2><i class="bi bi-x-octagon"></i> Gerenciar Bloqueios de Horários dos Profissionais</h2>

    <!-- Alerta com botão de ajuda -->
    <div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
      <div>
        <i class="bi bi-question-circle-fill fs-4 me-2"></i>
        Precisa de ajuda?
      </div>
      <button 
        class="btn btn-primary btn-sm ms-5 text-write"
        data-bs-toggle="modal" 
        data-bs-target="#modalHelpBloqueios"
      >
        <i class="bi bi-question-circle me-1"></i> Ver Ajuda
      </button>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal fade" id="modalHelpBloqueios" tabindex="-1" aria-labelledby="helpBloqueiosLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="helpBloqueiosLabel">
              <i class="bi bi-question-circle"></i> Ajuda - Gerenciar Bloqueios de Horários
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <ul class="list-unstyled">
              <li>
                <i class="bi bi-plus-circle-fill me-2 text-success"></i>
                Clique em <strong>Adicionar Bloqueio</strong> para registrar períodos em que o profissional estará indisponível.
              </li>
              <li>
                <i class="bi bi-calendar-event-fill me-2 text-primary"></i>
                Escolha a <strong>data</strong> e o <strong>intervalo de horário</strong> (início e fim) em que o bloqueio se aplica.
              </li>
              <li>
                <i class="bi bi-person-fill-gear me-2 text-warning"></i>
                Selecione o <strong>profissional</strong> afetado pelo bloqueio antes de salvar.
              </li>
              <li>
                <i class="bi bi-file-earmark-text-fill me-2 text-secondary"></i>
                Utilize o campo <strong>motivo</strong> para indicar a razão do bloqueio (ex: consulta médica, manutenção, folga, etc).
              </li>
              <li>
                <i class="bi bi-trash-fill me-2 text-danger"></i>
                Clique em <strong>Excluir</strong> para remover bloqueios antigos ou criados por engano.
              </li>
              <li>
                <i class="bi bi-search me-2 text-primary"></i>
                Use o campo de <strong>busca</strong> para filtrar bloqueios por nome do profissional.
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
    <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalBloqueio">
      <i class="bi bi-plus-circle me-2"></i> Adicionar Bloqueio
    </button>
  </div>

  <!-- Busca -->
  <div class="position-relative w-100 w-md-auto flex-grow-1 search-box">
    <input type="text" id="busca-bloqueio" class="form-control ps-5" placeholder="Buscar por profissional">
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
            <th style="min-width: 180px; white-space: nowrap;">
              <i class="bi bi-person-badge me-1"></i> Profissional
            </th>
            <th style="min-width: 140px; white-space: nowrap;">
              <i class="bi bi-calendar me-1"></i> Data
            </th>
            <th style="min-width: 120px; white-space: nowrap;">
              <i class="bi bi-clock me-1"></i> Início
            </th>
            <th style="min-width: 120px; white-space: nowrap;">
              <i class="bi bi-clock-history me-1"></i> Fim
            </th>
            <th style="min-width: 200px; white-space: nowrap;">
              <i class="bi bi-chat-left-text me-1"></i> Motivo
            </th>
            <th style="min-width: 100px; white-space: nowrap;">
              <i class="bi bi-gear me-1"></i> Ações
            </th>
          </tr>
        </thead>
            <tbody id="bloqueios-table">
                <?php foreach ($bloqueios as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['profissional']) ?></td>
                        <td><?= date('d/m/Y', strtotime($b['data'])) ?></td>
                        <td><?= substr($b['hora_inicio'], 0, 5) ?></td>
                        <td><?= substr($b['hora_fim'], 0, 5) ?></td>
                        <td><?= htmlspecialchars($b['motivo']) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editarBloqueio(<?= $b['id'] ?>)"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="excluirBloqueio(<?= $b['id'] ?>)"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p id="mensagem-vazia" class="text-center text-muted mt-3" style="display: none;">Nenhum bloqueio encontrado.</p>

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
    <?php endif; ?>
</div>

<!-- Modal para adicionar/editar bloqueio -->
<div class="modal fade" id="modalBloqueio" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="modal-title" class="modal-title"><i class="bi bi-x-octagon"></i> Adicionar Bloqueio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="bloqueio-id">
        
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-person-badge"></i> Profissional</label>
          <select id="bloqueio-profissional" class="form-select" required>
            <option value="">Selecione</option>
            <?php
              $stmtProfissionais = $pdo->query("SELECT p.id, u.nome FROM profissionais p JOIN usuario u ON p.usuario_id = u.id WHERE p.disponivel = 1 ORDER BY u.nome");
              while ($prof = $stmtProfissionais->fetch()) {
                  echo '<option value="' . $prof['id'] . '">' . htmlspecialchars($prof['nome']) . '</option>';
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-calendar-event"></i> Data</label>
          <input type="date" id="bloqueio-data" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-clock"></i> Hora Início</label>
          <input type="time" id="bloqueio-hora-inicio" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-clock-history"></i> Hora Fim</label>
          <input type="time" id="bloqueio-hora-fim" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-chat-left-text"></i> Motivo</label>
          <input type="text" id="bloqueio-motivo" class="form-control" required placeholder="Ex: consulta médica">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-lg" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Fechar</button>
        <button class="btn btn-primary btn-lg" onclick="salvarBloqueio()"><i class="bi bi-check-circle"></i> Salvar</button>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/bloqueios.js"></script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>

</body>
</html>

<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

// Definir fuso horário para Brasília
date_default_timezone_set('America/Sao_Paulo');
$dataAtual = date('d/m/Y');
$diasSemana = [
    'Monday' => 'Segunda-feira', 'Tuesday' => 'Terça-feira',
    'Wednesday' => 'Quarta-feira', 'Thursday' => 'Quinta-feira',
    'Friday' => 'Sexta-feira', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
];
$diaSemanaTraduzido = $diasSemana[date('l')];

// Conectar ao banco de dados
$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Inicializar a variável $buscaSql
$buscaSql = "WHERE 1=1";

// Verifica se atendente foi selecionado
$atendenteId = isset($_GET['atendente_id']) ? $_GET['atendente_id'] : '';
if (!empty($atendenteId)) {
    $buscaSql .= " AND a.atendente = :atendente_id";
}


// Se nenhum filtro ou data for especificado, carregar agendamentos do dia atual
if (empty($_GET['filtro']) && empty($_GET['data']) && empty($_GET['busca'])) {
    $filtro = 'hoje';
    $buscaSql .= " AND a.data = CURDATE()";
}

// Filtro de busca
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';
$dataFiltro = isset($_GET['data']) ? $_GET['data'] : '';
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';

if (!empty($busca)) {
$buscaSql .= " AND (c.nome LIKE :busca OR c.telefone LIKE :busca)";
}
if (!empty($dataFiltro)) {
    $buscaSql .= " AND a.data = :data";
}
if ($filtro === "hoje") {
    $buscaSql .= " AND a.data = CURDATE()";
}

// Configuração da paginação
$itensPorPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Buscar agendamentos com paginação e busca
$query = "SELECT a.id, c.nome, c.telefone, a.observacao, a.confirmado,
          DATE_FORMAT(a.data, '%d/%m/%Y') as data,
          TIME_FORMAT(a.horario, '%H:%i') as horario,
          s.nome as servico, s.valor, u.nome as atendente
          FROM agendamentos a
          LEFT JOIN clientes c ON a.cliente_id = c.id
          JOIN servicos s ON a.servico = s.id 
          LEFT JOIN usuario u ON a.atendente = u.id
          $buscaSql 
          ORDER BY a.data DESC, a.horario ASC 
          LIMIT :offset, :limit";

$stmt = $pdo->prepare($query);

if (!empty($busca)) {
    $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
if (!empty($dataFiltro)) {
    $stmt->bindValue(':data', $dataFiltro, PDO::PARAM_STR);
}
if (!empty($atendenteId)) {
    $stmt->bindValue(':atendente_id', $atendenteId, PDO::PARAM_INT);
}

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();

$agendamentos = $stmt->fetchAll();

// Contar total de agendamentos para paginação
$queryTotal = "SELECT COUNT(*) FROM agendamentos a
               LEFT JOIN clientes c ON a.cliente_id = c.id
               $buscaSql";
$stmtTotal = $pdo->prepare($queryTotal);
if (!empty($busca)) {
    $stmtTotal->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
}
if (!empty($dataFiltro)) {
    $stmtTotal->bindValue(':data', $dataFiltro, PDO::PARAM_STR);
}
if (!empty($atendenteId)) {
    $stmtTotal->bindValue(':atendente_id', $atendenteId, PDO::PARAM_INT);
}
$stmtTotal->execute();

$totalAgendamentos = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, ceil($totalAgendamentos / $itensPorPagina));

// Verificar se a requisição é uma busca dinâmica
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $busca = $_GET['busca'];
    $buscaSql = "WHERE (c.nome LIKE :busca OR c.telefone LIKE :busca)";

    if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $busca = $_GET['busca'];
    $buscaSql = "WHERE (c.nome LIKE :busca OR c.telefone LIKE :busca)";
    if (!empty($atendenteId)) {
        $buscaSql .= " AND a.atendente = :atendente_id";
    }

    // Requisição AJAX para filtrar por atendente (sem busca)
if (isset($_GET['atendente_id']) && !isset($_GET['busca']) && !isset($_GET['filtro']) && !isset($_GET['data'])) {
    $query = "SELECT a.id, c.nome, c.telefone, ...
    FROM agendamentos a
    JOIN clientes c ON a.cliente_id = c.id
    JOIN servicos s ON a.servico = s.id 
    LEFT JOIN usuario u ON a.atendente = u.id
    $buscaSql ORDER BY a.data DESC, a.horario ASC";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':atendente_id', $_GET['atendente_id'], PDO::PARAM_INT);
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($agendamentos);
    exit;
}

// Consulta padrão
    $query = "SELECT a.id, c.nome, c.telefone, a.observacao, a.confirmado,
       DATE_FORMAT(a.data, '%d/%m/%Y') as data,
       TIME_FORMAT(a.horario, '%H:%i') as horario,
       s.nome as servico, 
       s.valor,
       u.nome as atendente
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN servicos s ON a.servico = s.id 
        LEFT JOIN usuario u ON a.atendente = u.id
        $buscaSql 
        ORDER BY a.data DESC, a.horario ASC";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
    if (!empty($atendenteId)) {
        $stmt->bindValue(':atendente_id', $atendenteId, PDO::PARAM_INT);
    }
    $stmt->execute();
    $agendamentos = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($agendamentos);
    exit;
}

}

//confirmar agendamento via whatsapp
if (isset($_GET['acao']) && $_GET['acao'] === 'confirmar' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("UPDATE agendamentos SET confirmado = 1 WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['status' => 'success']);
    exit;
}

// Excluir agendamento
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['status' => 'success', 'mensagem' => 'Agendamento excluído com sucesso!']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Agendamentos</title>
    <!-- head.php -->
    <?php include __DIR__ . '/../includes/head.php'; ?>

</head>
<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <!-- Conteúdo Principal centralizado -->
<div class="container content py-4">

  <h2><i class="bi bi-calendar-week"></i> Gerenciar Agendamentos</h2>

  <!-- Alerta de Ajuda -->
  <div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="bi bi-question-circle-fill fs-4 me-2"></i>
    <div>
      Precisa de ajuda?
      <button 
        class="btn btn-primary btn-sm ms-5 text-write"
        data-bs-toggle="modal" 
        data-bs-target="#modalHelp"
      >
        <i class="bi bi-question-circle me-1"></i> Ver Ajuda
      </button>
    </div>
  </div>

  <!-- Modal de Ajuda -->
  <div class="modal fade" id="modalHelp" tabindex="-1" aria-labelledby="helpLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="helpLabel">
            <i class="bi bi-question-circle"></i> Ajuda - Gerenciar Agendamentos
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <ul class="list-unstyled">
            <li>
              <i class="bi bi-search me-2 text-primary"></i>
              Use o campo de busca para localizar clientes ou datas específicas.
            </li>
            <li>
              <i class="bi bi-funnel me-2 text-primary"></i>
              Utilize os filtros para visualizar agendamentos para hoje ou ver todos.
            </li>
            <li>
              <i class="bi bi-pencil-square me-2 text-primary"></i>
              Clique em <strong>Editar</strong> para alterar data, horário, atendente e observações.
            </li>
            <li>
              <i class="bi bi-trash me-2 text-danger"></i>
              Use o botão <strong>Excluir</strong> para remover agendamentos incorretos.
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <hr>

               <div class="row g-3 mb-4 align-items-center">

                    <!-- Filtro por atendente -->
                    <div class="col-md-3">
                      <div class="input-group">
                        <span class="fw-bold input-group-text"><i class="bi bi-person-badge me-2"></i>Atendente:</span>
                        <select id="filtro-atendente" class="form-select">
                          <option value="">Todos</option>
                          <?php
                            $stmtAtendentes = $pdo->prepare("
                                SELECT u.id, u.nome 
                                FROM profissionais p
                                JOIN usuario u ON p.usuario_id = u.id
                                WHERE p.profissao = 'manicure/pedicure'
                                ORDER BY u.nome ASC
                            ");
                            $stmtAtendentes->execute();
                            $atendenteSelecionado = isset($_GET['atendente_id']) ? $_GET['atendente_id'] : '';
                            while ($row = $stmtAtendentes->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($row['id'] == $atendenteSelecionado) ? 'selected' : '';
                                echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['nome']) . "</option>";
                            }
                          ?>
                        </select>
                      </div>
                    </div>

                                
                <!-- Grupo de botões de filtro -->
                <div class="col-md-4"> <!-- Ajustado para col-md-4 para compensar a redução do botão -->
                    <div class="btn-group w-100" role="group">
                        <span class="fw-bold input-group-text"><i class="bi bi-funnel me-2"></i>Filtrar:</span>
                        <button class="btn btn-success" onclick="filtrar('hoje')">
                            <i class="bi bi-calendar-day me-2"></i>Para Hoje
                        </button>
                        <button class="btn btn-secondary" onclick="filtrar('todos')">
                            <i class="bi bi-calendar-range me-2"></i>Ver Todos
                        </button>
                    </div>
                </div>

                <!-- Campo de data -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="fw-bold input-group-text"><i class="bi bi-calendar me-2"></i>Data:</span>
                        <input type="date" id="busca-data" class="form-control" value="<?= $dataFiltro ?>">
                    </div>
                </div>

                <!-- Campo de busca -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="fw-bold input-group-text"><i class="bi bi-search me-2"></i></span>
                        <input type="text" id="busca-agendamento" class="form-control" placeholder="Buscar nome ou telefone" value="<?= $busca ?>">
                    </div>
                </div>
            </div>

                <!-- Tabela de agendamentos -->
                 <small class="d-block mb-2 text-primary fw-bold">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                    Role para o lado para ver mais informações
                  </small>

                    <div class="table-responsive">
                      <table class="table table-striped table-hover">
                        <thead class="table-dark">
                          <tr>
                            <th style="min-width: 150px; white-space: nowrap;"><i class="bi bi-calendar2-week me-1"></i> Data / Horário</th>
                            <th style="min-width: 180px; white-space: nowrap;"><i class="bi bi-person me-1"></i> Cliente</th>
                            <th style="min-width: 150px; white-space: nowrap;"><i class="bi bi-list-task me-1"></i> Serviço</th>
                            <th style="min-width: 150px; white-space: nowrap;"><i class="bi bi-person-badge me-1"></i> Atendente</th>
                            <th style="min-width: 150px; white-space: nowrap;"><i class="bi bi-chat-dots me-1"></i> Observações</th>
                            <th style="min-width: 150px; white-space: nowrap;" class="text-center"><i class="bi bi-whatsapp me-1"></i> Confirmar via WhatsApp</th>
                            <th style="min-width: 150px; white-space: nowrap;" class="text-center"><i class="bi bi-check-circle me-1"></i> Confirmado?</th>
                            <th style="min-width: 150px; white-space: nowrap;" class="text-center"><i class="bi bi-gear me-1"></i> Ações</th>
                          </tr>
                        </thead>

                        <tbody id="agendamentos-table">
                          <?php if (empty($agendamentos)) : ?>
                            <tr>
                              <td colspan="8" class="text-center">
                                <i class="bi bi-exclamation-circle me-2"></i> Nenhum agendamento encontrado.
                              </td>
                            </tr>
                          <?php else : ?>
                            <?php foreach ($agendamentos as $agendamento) : ?>
                              <tr>
                                <td><?= $agendamento['data'] ?> - <?= $agendamento['horario'] ?></td>
                                <td>
                                  <?php if ($agendamento['nome'] === null): ?>
                                    <i class="bi bi-exclamation-circle text-warning" title="Cliente removido"></i>
                                  <?php endif; ?>
                                  <?= htmlspecialchars($agendamento['nome'] ?? 'Cliente removido') ?>
                                  -
                                  <span class="text-dark small fw-bold"><?= htmlspecialchars($agendamento['telefone'] ?? 'Número removido') ?></span>
                                </td>
                                <td>
                                  <?= htmlspecialchars($agendamento['servico']) ?> 
                                  <span class="text-dark small fw-bold">(<?= "R$ " . number_format((float)$agendamento['valor'], 2, ',', '.') ?>)</span>
                                </td>
                                <td><?= htmlspecialchars($agendamento['atendente']) ?></td>
                                <td><?= htmlspecialchars($agendamento['observacao']) ?></td>

                                <!-- Botão WhatsApp com mensagem formatada -->
                                <td class="text-center">
                                  <?php
                                    $telefoneLimpo = preg_replace('/\D/', '', $agendamento['telefone']);
                                    $valorFormatado = "R$ " . number_format($agendamento['valor'], 2, ',', '.');
                                    $mensagem = "Olá {$agendamento['nome']}, seu agendamento foi confirmado com sucesso! 💅\n"
                                              . "- Data: {$agendamento['data']}\n"
                                              . "- Horário: {$agendamento['horario']}\n"
                                              . "- Serviço: {$agendamento['servico']}\n"
                                              . "- Valor: {$valorFormatado}\n"
                                              . "- Atendente: {$agendamento['atendente']}\n"
                                              . "- Observação: " . (!empty($agendamento['observacao']) ? $agendamento['observacao'] : "Nenhuma") . "\n"
                                              . "Aguardamos você! 💖";
                                    $linkZap = "https://wa.me/55$telefoneLimpo?text=" . urlencode($mensagem);
                                  ?>
                                  <a href="<?= $linkZap ?>" target="_blank" class="btn btn-success btn-sm" title="Abrir WhatsApp com mensagem">
                                    <i class="bi bi-whatsapp"></i>
                                  </a>
                                </td>

                                <!-- Botão de confirmação -->
                                <td class="text-center">
                                      <?php if ($agendamento['confirmado']) : ?>
                                        <button class="btn btn-success btn-sm"
                                          onclick="confirmarAgendamento(<?= $agendamento['id'] ?>, false)">
                                          <i class="bi bi-check-circle-fill"></i>
                                        </button>
                                      <?php else : ?>
                                        <button class="btn btn-outline-secondary btn-sm"
                                          onclick="confirmarAgendamento(<?= $agendamento['id'] ?>, true)">
                                          <i class="bi bi-check-circle"></i>
                                        </button>
                                      <?php endif; ?>
                                    </td>

                                <!-- Ações -->
                                <td class="text-center">
                                  <button class="btn btn-warning btn-sm me-1" onclick="abrirModalEditarAgendamento(<?= $agendamento['id'] ?>)">
                                    <i class="bi bi-pencil-fill"></i>
                                  </button>
                                  <button class="btn btn-danger btn-sm" onclick="excluirAgendamento(<?= $agendamento['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                  </button>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                    <small class="d-block mb-2 text-primary fw-bold">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                    Role para o lado para ver mais informações
                  </small>
                </div>
            </div>

                <!-- Paginação -->
                <nav aria-label="Paginação">
                    <ul class="pagination justify-content-center">
                        <?php if ($paginaAtual > 1) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?>&busca=<?= htmlspecialchars($busca) ?>&data=<?= htmlspecialchars($dataFiltro) ?>&filtro=<?= htmlspecialchars($filtro) ?>">
                                    Anterior
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                            <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>&busca=<?= htmlspecialchars($busca) ?>&data=<?= htmlspecialchars($dataFiltro) ?>&filtro=<?= htmlspecialchars($filtro) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($paginaAtual < $totalPaginas) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?>&busca=<?= htmlspecialchars($busca) ?>&data=<?= htmlspecialchars($dataFiltro) ?>&filtro=<?= htmlspecialchars($filtro) ?>">
                                    Próxima
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

            <!-- Modal de Edição de Agendamento -->
                    <div class="modal fade" id="modalEditarAgendamento" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-warning text-white">
                                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Agendamento</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <form id="formEditarAgendamento">
                                    <input type="hidden" name="id" id="edit-id">

                                    <div class="mb-3">
                                      <label class="form-label">
                                        <i class="bi bi-person"></i> Nome
                                      </label>
                                      <input type="text" class="form-control" id="edit-nome" readonly>
                                    </div>

                                    <div class="mb-3">
                                      <label class="form-label">
                                        <i class="bi bi-telephone"></i> Telefone
                                      </label>
                                      <input type="text" class="form-control" id="edit-telefone" readonly>
                                    </div>

                                    <div class="mb-3">
                                      <label class="form-label">
                                        <i class="bi bi-person-badge"></i> Atendente
                                      </label>
                                      <select class="form-select" id="edit-atendente" name="atendente" required></select>
                                    </div>

                                    <label class="form-label mt-2">
                                      <i class="bi bi-calendar-event"></i> Data
                                    </label>
                                    <input type="date" class="form-control" name="data" id="edit-data" required>

                                    <label class="form-label mt-2">
                                      <i class="bi bi-scissors"></i> Serviço
                                    </label>
                                    <select class="form-control" name="servico" id="edit-servico" required></select>

                                    <label class="form-label mt-2">
                                      <i class="bi bi-clock"></i> Horário
                                    </label>
                                    <select class="form-control" name="horario" id="edit-horario" required></select>

                                    <div class="mb-3 mt-3">
                                      <label class="form-label">
                                        <i class="bi bi-chat-left-text"></i> Observações
                                      </label>
                                      <textarea class="form-control" id="edit-observacao" name="observacao" rows="2"></textarea>
                                    </div>

                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                      </button>
                                      <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-save"></i> Salvar Alterações
                                      </button>
                                    </div>
                                  </form>
                                </div>

                                
                            </div>
                        </div>
                    </div>

<script src="assets/js/gerenciar_agendamentos.js"></script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>

</body>
</html>

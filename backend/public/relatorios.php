<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $stmt = $pdo->prepare("SELECT nome FROM usuario WHERE nivel = 'Administrador'");
    $stmt->execute();
    $administradores = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $administradores = [];
}

date_default_timezone_set('America/Sao_Paulo');
$dataAtual = date('d/m/Y');

$diasSemana = [
    'Monday' => 'Segunda-feira',
    'Tuesday' => 'Terça-feira',
    'Wednesday' => 'Quarta-feira',
    'Thursday' => 'Quinta-feira',
    'Friday' => 'Sexta-feira',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];
$diaSemanaTraduzido = $diasSemana[date('l')];

$mesSelecionado = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
$mesNumero = date('m/Y', strtotime($mesSelecionado));

$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$relatorio = ['faturamento' => 0];

//  Faturamento
$stmt = $pdo->prepare("SELECT SUM(s.valor) as faturamento FROM agendamentos a 
                       JOIN servicos s ON a.servico = s.id 
                       WHERE DATE_FORMAT(a.data, '%Y-%m') = :mes");
$stmt->execute(['mes' => $mesSelecionado]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$relatorio['faturamento'] = $result ? $result['faturamento'] ?: 0 : 0;

// Serviços mais agendados
$stmt = $pdo->prepare("SELECT s.nome as servico, COUNT(a.id) as quantidade 
                       FROM agendamentos a 
                       JOIN servicos s ON a.servico = s.id 
                       WHERE DATE_FORMAT(a.data, '%Y-%m') = :mes 
                       GROUP BY s.id 
                       ORDER BY quantidade DESC 
                       LIMIT 5");
$stmt->execute(['mes' => $mesSelecionado]);
$servicosMais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Clientes mais frequentes
$stmt = $pdo->prepare("SELECT c.nome as cliente, COUNT(a.id) as quantidade 
                       FROM agendamentos a 
                       JOIN clientes c ON a.cliente_id = c.id
                       WHERE DATE_FORMAT(a.data, '%Y-%m') = :mes 
                       GROUP BY c.id 
                       ORDER BY quantidade DESC 
                       LIMIT 5");
$stmt->execute(['mes' => $mesSelecionado]);
$clientesTop = $stmt->fetchAll(PDO::FETCH_ASSOC);

//  Atendentes mais ativos
$stmt = $pdo->prepare("SELECT u.nome as atendente, COUNT(a.id) as quantidade 
                       FROM agendamentos a 
                       JOIN usuario u ON a.atendente = u.id 
                       WHERE DATE_FORMAT(a.data, '%Y-%m') = :mes 
                       GROUP BY u.id 
                       ORDER BY quantidade DESC 
                       LIMIT 5");
$stmt->execute(['mes' => $mesSelecionado]);
$atendentesTop = $stmt->fetchAll(PDO::FETCH_ASSOC);

//  Dias mais movimentados
$stmt = $pdo->prepare("
    SELECT 
        CASE DAYOFWEEK(a.data) 
            WHEN 2 THEN 'Segunda' 
            WHEN 3 THEN 'Terça' 
            WHEN 4 THEN 'Quarta' 
            WHEN 5 THEN 'Quinta' 
            WHEN 6 THEN 'Sexta' 
            WHEN 7 THEN 'Sábado' 
            WHEN 1 THEN 'Domingo' 
        END as dia, 
        COUNT(a.id) as quantidade 
    FROM agendamentos a 
    WHERE DATE_FORMAT(a.data, '%Y-%m') = :mes 
    GROUP BY dia 
    HAVING dia IS NOT NULL 
    ORDER BY quantidade DESC
");
$stmt->execute(['mes' => $mesSelecionado]);
$diasMais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Horários mais agendados
$stmt = $pdo->prepare("SELECT a.horario, COUNT(a.id) as quantidade 
                       FROM agendamentos a 
                       WHERE DATE_FORMAT(a.data, '%Y-%m') = :mes 
                       GROUP BY a.horario 
                       ORDER BY quantidade DESC 
                       LIMIT 5");
$stmt->execute(['mes' => $mesSelecionado]);
$horariosTop = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Relatórios - SGMP</title>
    <!-- head.php -->
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>

<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <!-- Conteúdo Principal centralizado -->
    <div class="container content py-4">
  <!-- Bloquear usuários com nível de funcionário -->
  <?php if (isset($_SESSION['admin_nivel']) && $_SESSION['admin_nivel'] === 'funcionario'): ?>
    <h2 class="text-primary"><i class="bi bi-bar-chart-line"></i> Relatório Mensal de Agendamentos</h2>
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

    <h2 class="text-primary"><i class="bi bi-bar-chart-line"></i> Relatório Mensal de Agendamentos</h2>

    <!-- Alerta com botão de ajuda -->
    <div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
      <div>
        <i class="bi bi-question-circle-fill fs-4 me-2"></i>
        Precisa de ajuda?
      </div>
      <button 
        class="btn btn-primary btn-sm ms-5 text-write"
        data-bs-toggle="modal" 
        data-bs-target="#modalHelpRelatorio"
      >
        <i class="bi bi-question-circle me-1"></i> Ver Ajuda
      </button>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal fade" id="modalHelpRelatorio" tabindex="-1" aria-labelledby="helpRelatorioLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="helpRelatorioLabel">
              <i class="bi bi-question-circle"></i> Ajuda - Relatório Mensal de Agendamentos
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <ul class="list-unstyled">
              <li>
                <i class="bi bi-calendar-month-fill me-2 text-primary"></i>
                Este relatório exibe um resumo completo dos agendamentos realizados no mês atual.
              </li>
              <li>
                <i class="bi bi-graph-up-arrow me-2 text-success"></i>
                Visualize o <strong>faturamento mensal</strong> e acompanhe a evolução dos resultados.
              </li>
              <li>
                <i class="bi bi-people-fill me-2 text-info"></i>
                Consulte os <strong>clientes mais frequentes</strong> e entenda o perfil do público atendido.
              </li>
              <li>
                <i class="bi bi-scissors me-2 text-warning"></i>
                Veja quais são os <strong>serviços mais agendados</strong> e planeje promoções ou ajustes de agenda.
              </li>
              <li>
                <i class="bi bi-calendar-day-fill me-2 text-secondary"></i>
                Analise os <strong>dias e horários de maior movimento</strong> para otimizar os atendimentos.
              </li>
              <li>
                <i class="bi bi-download me-2 text-danger"></i>
                Clique em <strong>Imprimir Relatório</strong> para gerar um arquivo e salvar em PDF, ou imprimir o relatório.
              </li>
              <li>
                <i class="bi bi-lightbulb-fill me-2 text-warning"></i>
                Dica: use estas informações para identificar tendências e tomar decisões estratégicas sobre os serviços e horários.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <hr>

                <!-- Seletor de Mês e Botão de Impressão -->
                <?php
                    // Extrair apenas o número do mês e o ano do formato "YYYY-MM"
                    $mesNumero = date('m/Y', strtotime($mesSelecionado));
                ?>

                <div class="row align-items-center justify-content-between mb-4 p-3 bg-light rounded shadow-sm">
                    <!-- Coluna do Seletor de Mês -->
                    <div class="col-md-4">
                        <label class="fw-bold text-primary">
                            <i class="bi bi-calendar-check"></i> Selecionar Mês:
                        </label>
                        <input type="month" id="selecionar-mes" class="form-control" 
                               value="<?= htmlspecialchars($mesSelecionado) ?>" onchange="atualizarRelatorio()">
                    </div>

                    <!-- Coluna do Texto do Mês Selecionado -->
                    <div class="col-md-4 text-left">
                        <p class="fw-bold text-dark m-0">Mês Selecionado: </p>
                        <p class="fw-bold text-dark m-0 p-2 border shadow-sm">
                            <i class="bi bi-calendar-event"></i> <?= $mesNumero ?>
                        </p>
                    </div>

                    <!-- Coluna do Botão de Impressão -->
                    <div class="col-md-4">
                    <button 
                        class="btn btn-primary btn-lg w-100 shadow-sm d-flex align-items-center justify-content-center" 
                        onclick="imprimirRelatorio()"
                    >
                        <i class="bi bi-printer me-2 fs-5"></i> 
                        Imprimir Relatório
                    </button>
                    </div>
                </div>

                <div id="relatorio">
                    <div class="row g-4">
                        <!-- Coluna Esquerda -->
                        <div class="col-md-6">
                            <div class="card text-white bg-success shadow-sm">
                                <div class="card-header"><i class="bi bi-cash-coin"></i> Faturamento do Mês</div>
                                <div class="card-body text-center">
                                    <h1 class="fw-bold">R$ <?= number_format($relatorio['faturamento'], 2, ',', '.') ?></h1>
                                </div>
                            </div>

                            <div class="card mt-3 shadow-sm">
                                <div class="card-header bg-info text-white"><i class="bi bi-people-fill"></i> Clientes Mais Frequentes</div>
                                <ul class="list-group list-group-flush">
                                    <?php if (empty($clientesTop)) { ?>
                                        <li class="list-group-item text-muted"><i class="bi bi-person-x"></i> Nenhum cliente frequente encontrado</li>
                                    <?php } else {
                                        foreach ($clientesTop as $c) {
                                            echo "<li class='list-group-item'><i class='bi bi-person-circle'></i> {$c['cliente']} - <b>{$c['quantidade']}</b> agendamentos</li>";
                                        }
                                    } ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Coluna Direita -->
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white"><i class="bi bi-list-stars"></i> Serviços Mais Agendados</div>
                                <ul class="list-group list-group-flush">
                                    <?php if (empty($servicosMais)) { ?>
                                        <li class="list-group-item text-muted"><i class="bi bi-clipboard-x"></i> Nenhum serviço agendado</li>
                                    <?php } else {
                                        foreach ($servicosMais as $s) {
                                            echo "<li class='list-group-item'><i class='bi bi-scissors'></i> {$s['servico']} - <b>{$s['quantidade']}</b> agendamentos</li>";
                                        }
                                    } ?>
                                </ul>
                            </div>

                            <div class="card mt-3 shadow-sm">
                                <div class="card-header bg-secondary text-white"><i class="bi bi-person-workspace"></i> Atendentes com Mais Agendamentos</div>
                                <ul class="list-group list-group-flush">
                                    <?php if (empty($atendentesTop)) { ?>
                                        <li class="list-group-item text-muted"><i class="bi bi-person-x"></i> Nenhum atendente ativo</li>
                                    <?php } else {
                                        foreach ($atendentesTop as $a) {
                                            echo "<li class='list-group-item'><i class='bi bi-person'></i> {$a['atendente']} - <b>{$a['quantidade']}</b> agendamentos</li>";
                                        }
                                    } ?>
                                </ul>
                            </div>

                            <div class="card mt-3 shadow-sm">
                                <div class="card-header bg-warning text-white"><i class="bi bi-calendar-event"></i> Dias Mais Movimentados</div>
                                <ul class="list-group list-group-flush">
                                    <?php if (empty($diasMais)) { ?>
                                        <li class="list-group-item text-muted"><i class="bi bi-calendar-x"></i> Nenhum dia movimentado encontrado</li>
                                    <?php } else {
                                        foreach ($diasMais as $d) {
                                            echo "<li class='list-group-item'><i class='bi bi-calendar-day'></i> {$d['dia']} - <b>{$d['quantidade']}</b> agendamentos</li>";
                                        }
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
        </div>
    </div>

<script src="assets/js/relatorios.js"></script>

<!-- Rodapé -->
<?php include __DIR__ . '/../includes/rodape.php'; ?>

</body>
</html>

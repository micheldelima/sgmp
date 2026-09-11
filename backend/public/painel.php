<?php
require_once '../config/auth_middleware.php';
require_once '../config/database.php';
date_default_timezone_set('America/Sao_Paulo');

// Data atual
$dataAtual = date('Y-m-d');
$dataExibicao = date('d/m/Y');
$diasSemana = [
    'Monday' => 'Segunda-feira', 'Tuesday' => 'Terça-feira',
    'Wednesday' => 'Quarta-feira', 'Thursday' => 'Quinta-feira',
    'Friday' => 'Sexta-feira', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
];
$diaSemanaTraduzido = $diasSemana[date('l')];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Total de agendamentos de hoje
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data = CURDATE()");
    $stmt->execute();
    $agendamentosHoje = $stmt->fetchColumn();

    // Faturamento do mês
    $stmt = $pdo->prepare("
        SELECT SUM(s.valor)
        FROM agendamentos a
        JOIN servicos s ON a.servico = s.id
        WHERE MONTH(a.data) = MONTH(CURDATE()) AND YEAR(a.data) = YEAR(CURDATE())
    ");
    $stmt->execute();
    $faturamentoMensal = $stmt->fetchColumn() ?: 0;
    $faturamentoFormatado = number_format($faturamentoMensal, 2, ',', '.');

    // Total de profissionais disponíveis com profissão "manicure/pedicure"
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM profissionais WHERE profissao = 'manicure/pedicure' AND disponivel = 1");
    $stmt->execute();
    $totalProfissionais = $stmt->fetchColumn();

} catch (PDOException $e) {
    $agendamentosHoje = 0;
    $faturamentoFormatado = '0,00';
    $totalProfissionais = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGMP - Painel</title>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <div class="container content py-4">
        <h1 class="display-6">Bem-vindo (a) ao SGMP</h1>
        <p class="lead">Sistema de Gestão para Manicure e Pedicure</p>
        
        <p class="me-3 text-muted">
            Olá, <strong><?= $_SESSION['admin_nome'] ?? 'Usuário' ?></strong>! Hoje é <strong><?= $diaSemanaTraduzido ?></strong>, <?= $dataExibicao ?>.
        </p>

        <!-- Alertas -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-calendar-event me-2"></i>
            Você tem <strong><?= $agendamentosHoje ?></strong> agendamento(s) para hoje.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            <div class="mt-2">
                <a href="gerenciar_agendamentos.php?filtro=hoje" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye"></i> Ver agendamentos
                </a>
            </div>
        </div>

        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-cash-coin me-2"></i>
            Seu faturamento mensal até o momento está em <strong>R$<?= $faturamentoFormatado ?></strong>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            <div class="mt-2">
                <a href="relatorios.php" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-graph-up"></i> Ver relatórios
                </a>
            </div>
        </div>

        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-person-workspace me-2"></i>
            Você possui <strong><?= $totalProfissionais ?></strong> profissional(is) disponíveis para manicure e pedicure.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            <div class="mt-2">
                <a href="profissionais.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-lines-fill"></i> Ver profissionais
                </a>
            </div>
        </div>

    </div>

    <?php include __DIR__ . '/../includes/rodape.php'; ?>
</body>
</html>

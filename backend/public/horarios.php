<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

// Definir fuso horário para Brasília
date_default_timezone_set('America/Sao_Paulo');
$dataAtual = date('d/m/Y');

// Traduzindo os dias da semana
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

// Buscar horários salvos
$stmt = $pdo->query("SELECT * FROM horarios_atendimento ORDER BY FIELD(dia_inicio, 'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado')");
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Horários</title>
    <!-- head.php -->
    <?php include __DIR__ . '/../includes/head.php'; ?>

</head>

<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <!-- Conteúdo Principal centralizado -->
    <div class="container content py-4">
      <h2><i class="bi bi-clock"></i> Gerenciar Dias e Horários de Atendimento</h2>

<!-- Alerta com botão de ajuda -->
<div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
  <div>
    <i class="bi bi-question-circle-fill fs-4 me-2"></i>
    Precisa de ajuda?
  </div>
  <button 
    class="btn btn-primary btn-sm ms-5 text-write"
    data-bs-toggle="modal" 
    data-bs-target="#modalHelpHorarios"
  >
    <i class="bi bi-question-circle me-1"></i> Ver Ajuda
  </button>
</div>

<!-- Modal de Ajuda -->
<div class="modal fade" id="modalHelpHorarios" tabindex="-1" aria-labelledby="helpHorariosLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="helpHorariosLabel">
          <i class="bi bi-question-circle"></i> Ajuda - Gerenciar Dias e Horários de Atendimento
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="list-unstyled">
          <li>
            <i class="bi bi-calendar-plus-fill me-2 text-success"></i>
            Utilize o formulário para <strong>cadastrar o intervalo de dias e horários</strong> em que o estúdio estará em funcionamento.
          </li>
          <li>
            <i class="bi bi-clock-fill me-2 text-primary"></i>
            Informe o <strong>horário de abertura e fechamento</strong> para o período selecionado.
          </li>
          <li>
            <i class="bi bi-calendar-check-fill me-2 text-warning"></i>
            Selecione se o estúdio <strong>funciona ou não em feriados</strong> antes de salvar.
          </li>
          <li>
            <i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>
            O sistema permite apenas <strong>um cadastro ativo</strong> de horário de funcionamento por vez.  
            Exclua o atual para adicionar um novo.
          </li>
          <li>
            <i class="bi bi-trash-fill me-2 text-danger"></i>
            Clique em <strong>Excluir</strong> para remover o horário cadastrado e liberar novo cadastro.
          </li>
          <li>
            <i class="bi bi-eye-fill me-2 text-secondary"></i>
            Consulte os horários cadastrados na tabela abaixo e confirme se estão corretos.
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<hr>

                <!-- Formulário para adicionar horários -->
                <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-plus-circle"></i> Adicionar Novo Horário
                </div>
                <div class="card-body">
                    <!-- FORM -->
                    <form id="formAdicionarHorario">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Dia de Início</label>
                                <select name="dia_inicio" id="dia_inicio" class="form-select" required>
                                    <option value="" disabled selected>Selecione...</option>
                                    <option>Domingo</option><option>Segunda-feira</option><option>Terça-feira</option>
                                    <option>Quarta-feira</option><option>Quinta-feira</option><option>Sexta-feira</option>
                                    <option>Sábado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Dia de Fim</label>
                                <select name="dia_fim" id="dia_fim" class="form-select" required>
                                    <option value="" disabled selected>Selecione...</option>
                                    <option>Domingo</option><option>Segunda-feira</option><option>Terça-feira</option>
                                    <option>Quarta-feira</option><option>Quinta-feira</option><option>Sexta-feira</option>
                                    <option>Sábado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Abertura</label>
                                <input type="time" name="horario_abertura" id="horario_abertura" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fechamento</label>
                                <input type="time" name="horario_fechamento" id="horario_fechamento" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Feriado</label>
                                <select name="feriado" id="feriado" class="form-select" required>
                                    <option value="Não trabalhamos nos feriados">Não trabalhamos nos feriados</option>
                                    <option value="Trabalhamos no feriado">Trabalhamos no feriado</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3 w-100">
                            <i class="bi bi-save"></i> Salvar Horário
                        </button>
                    </form>
                    <!-- ALERTA -->
                    <div id="alertaHorarios" class="alert alert-warning mt-2 py-2 d-flex align-items-center" 
                        role="alert" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Permitido apenas um horário cadastrado. Exclua para adicionar um novo horário de atendimento.
                    </div>
                </div>
            </div>
        <hr>

                    <small class="d-block mb-2 text-primary fw-bold">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                        Role para o lado para ver mais informações
                    </small>

                <!-- Tabela de Horários -->
                <h4 class="mt-4"><i class="bi bi-calendar-week"></i> Horários Cadastrados</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mt-2">
                        <thead class="table-dark">
                            <tr>
                                <th style="min-width: 130px; white-space: nowrap;">
                                    <i class="bi bi-calendar me-1"></i> Dia Início
                                </th>
                                <th style="min-width: 130px; white-space: nowrap;">
                                    <i class="bi bi-calendar me-1"></i> Dia Fim
                                </th>
                                <th style="min-width: 120px; white-space: nowrap;">
                                    <i class="bi bi-clock me-1"></i> Abertura
                                </th>
                                <th style="min-width: 120px; white-space: nowrap;">
                                    <i class="bi bi-clock me-1"></i> Fechamento
                                </th>
                                <th style="min-width: 100px; white-space: nowrap;">
                                    <i class="bi bi-flag me-1"></i> Feriado
                                </th>
                                <th style="min-width: 120px; white-space: nowrap;">
                                    <i class="bi bi-gear me-1"></i> Ações
                                </th>
                            </tr>
                        </thead>
                    <tbody id="listaHorarios">
                        <!-- Aqui serão carregados os horários via AJAX -->
                    </tbody>
                </table>

            </main>
        </div>
    </div>

<script src="assets/js/horarios.js"></script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>

</body>
</html>

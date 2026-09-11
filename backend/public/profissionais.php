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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profissionais - SGMP</title>
  <?php include __DIR__ . '/../includes/head.php'; ?>
</head>

<body class="bg-light">
  <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

  <div class="container content py-4">
  <?php if (isset($_SESSION['admin_nivel']) && $_SESSION['admin_nivel'] === 'funcionario'): ?>
    <h2><i class="bi bi-person-vcard"></i> Gerenciar Profissionais</h2>
    <p>Cadastre, consulte, edite ou delete um profissional</p>
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

    <h2><i class="bi bi-person-vcard"></i> Gerenciar Profissionais</h2>

    <!-- Alerta com botão de ajuda -->
    <div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
      <div>
        <i class="bi bi-question-circle-fill fs-4 me-2"></i>
        Precisa de ajuda?
      </div>
      <button 
        class="btn btn-primary btn-sm ms-5 text-write"
        data-bs-toggle="modal" 
        data-bs-target="#modalHelpProfissionais"
      >
        <i class="bi bi-question-circle me-1"></i> Ver Ajuda
      </button>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal fade" id="modalHelpProfissionais" tabindex="-1" aria-labelledby="helpProfissionaisLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="helpProfissionaisLabel">
              <i class="bi bi-question-circle"></i> Ajuda - Gerenciar Profissionais
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <ul class="list-unstyled">
              <li>
                <i class="bi bi-person-plus-fill me-2 text-success"></i>
                Clique em <strong>Novo Profissional</strong> para cadastrar um novo funcionário vinculado a um usuário do sistema existente.
              </li>
              <li>
                <i class="bi bi-pencil-square me-2 text-primary"></i>
                Use o botão <strong>Editar</strong> para atualizar informações como nome, telefone, profissão e status.
              </li>
              <li>
                <i class="bi bi-toggle-on me-2 text-warning"></i>
                O campo <strong>Status</strong> indica se o profissional está disponível ou indisponível para atendimentos.
              </li>
              <li>
                <i class="bi bi-trash me-2 text-danger"></i>
                Utilize o botão <strong>Excluir</strong> para remover um profissional do sistema, caso não atue mais no estúdio.
              </li>
              <li>
                <i class="bi bi-search me-2 text-primary"></i>
                Use o campo de <strong>busca</strong> para localizar rapidamente um profissional pelo nome.
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
        <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalProfissional">
          <i class="bi bi-plus-circle me-2"></i> Novo Profissional
        </button>
      </div>

      <!-- Campo de busca -->
      <div class="position-relative w-100 w-md-auto flex-grow-1 search-box">
        <input type="text" id="busca-profissional" class="form-control ps-5" placeholder="Buscar por nome">
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
            <th style="min-width:160px;"><i class="bi bi-person"></i> Nome</th>
            <th style="min-width:140px;"><i class="bi bi-telephone"></i> Telefone</th>
            <th style="min-width:160px;"><i class="bi bi-person-workspace"></i> Profissão</th>
            <th style="min-width:120px;"><i class="bi bi-eye"></i> Status</th>
            <th style="min-width:140px;"><i class="bi bi-calendar"></i> Cadastro</th>
            <th style="min-width:130px;"><i class="bi bi-gear"></i> Ações</th>
          </tr>
        </thead>
        <tbody id="profissionais-table"></tbody>
      </table>
    </div>
    <p id="mensagem-vazia" class="text-center text-muted mt-3" style="display: none;">Nenhum profissional encontrado.</p>
    
    <small class="d-block mb-2 text-primary fw-bold">
        <i class="bi bi-arrow-right-circle me-1"></i>
        Role para o lado para ver mais informações
    </small>
    
    <nav><ul class="pagination justify-content-center" id="paginacao"></ul></nav>

    <?php endif; ?>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="modalProfissional" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modal-title"><i class="bi bi-person-plus"></i> Adicionar Profissional</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="profissional-id">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-person-badge"></i> Selecionar Usuário</label>
            <select id="profissional-usuario" class="form-select" required></select>
            <div class="form-text">Apenas usuários com nível "Administrador" e "Funcionário" aparecem aqui.</div>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-person-workspace"></i> Profissão</label>
            <select id="profissional-profissao" class="form-select">
              <option value="manicure/pedicure">Manicure/Pedicure</option>
              <option value="secretaria">Secretária</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-toggle-on"></i> Status</label>
            <select id="profissional-disponivel" class="form-select">
              <option value="1">Disponível</option>
              <option value="0">Indisponível</option>
            </select>
            <div class="form-text">Para profissionais de férias ou ausentes.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary  btn-lg" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Fechar</button>
          <button class="btn btn-primary  btn-lg" onclick="salvarProfissional()"><i class="bi bi-check-circle"></i> Salvar</button>
        </div>
      </div>
    </div>
  </div>

<script src="assets/js/profissionais.js"></script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
</body>
</html>



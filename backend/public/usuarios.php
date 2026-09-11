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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários do Sistema - SGMP</title>
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
    <h2><i class="bi bi-people-fill"></i> Gerenciar Usuários do Sistema</h2>
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

    <h2><i class="bi bi-people-fill"></i> Gerenciar Usuários do Sistema</h2>

    <!-- Alerta com botão de ajuda -->
    <div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
      <div>
        <i class="bi bi-question-circle-fill fs-4 me-2"></i>
        Precisa de ajuda?
      </div>
      <button 
        class="btn btn-primary btn-sm ms-5 text-write"
        data-bs-toggle="modal" 
        data-bs-target="#modalHelpUsuarios"
      >
        <i class="bi bi-question-circle me-1"></i> Ver Ajuda
      </button>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal fade" id="modalHelpUsuarios" tabindex="-1" aria-labelledby="helpUsuariosLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="helpUsuariosLabel">
              <i class="bi bi-question-circle"></i> Ajuda - Gerenciar Usuários do Sistema
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <ul class="list-unstyled">
              <li>
                <i class="bi bi-person-plus-fill me-2 text-success"></i>
                Clique em <strong>Cadastrar Usuário</strong> para cadastrar um novo usuário no sistema.
              </li>
              <li>
                <i class="bi bi-key-fill me-2 text-primary"></i>
                Cada usuário possui um <strong>nível de acesso</strong> — <em>Administrador</em> ou <em>Funcionário</em>.
              </li>
              <li>
                <i class="bi bi-toggle-on me-2 text-success"></i>
                Use o botão <strong>Ativar/Desativar</strong> para controlar o acesso de um usuário ao sistema. (Apenas para administradores)
              </li>
              <li>
                <i class="bi bi-shield-lock-fill me-2 text-danger"></i>
                Por segurança, <strong>não é permitido Ativar/Desativar administradores</strong>.
              </li>
              <li>
                <i class="bi bi-lightbulb-fill me-2 text-info"></i>
                Dica: mantenha apenas usuários ativos e verificados para garantir a segurança do sistema.
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
                <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                  <i class="bi bi-person-plus me-2"></i> Cadastrar Usuário
                </button>
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
                        <th class="text-truncate" style="min-width: 120px;"><i class="bi bi-person-badge"></i> Nome</th>
                        <th class="text-truncate" style="min-width: 120px;"><i class="bi bi-person-circle"></i> Usuário</th>
                        <th class="text-truncate" style="min-width: 120px;"><i class="bi bi-envelope"></i> Email</th>
                        <th class="text-truncate" style="min-width: 120px;"><i class="bi bi-telephone"></i> Telefone</th>
                        <th class="text-truncate" style="min-width: 120px;"><i class="bi bi-shield-lock"></i> Nível</th>
                        <th class="text-truncate" style="min-width: 120px;"><i class="bi bi-circle-half"></i> Status</th>
                        <th class="text-truncate" style="min-width: 140px;"><i class="bi bi-calendar-plus"></i> Cadastrado</th>
                        <th class="text-truncate" style="min-width: 120px;"><i class="bi bi-gear"></i> Ações</th>
                      </tr>
                    </thead>
                    <tbody id="usuarios-table"></tbody>
                </table>
            </div>

            <p id="mensagem-vazia" class="text-center text-muted mt-3" style="display: none;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Nenhum usuário encontrado.
            </p>

            <small class="d-block mb-2 text-primary fw-bold">
                <i class="bi bi-arrow-right-circle me-1"></i>
                Role para o lado para ver mais informações
            </small>

            <nav><ul class="pagination justify-content-center" id="paginacao"></ul></nav>
        </main>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title"><i class="bi bi-person-plus"></i> Cadastrar Usuário</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="usuario-id">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-card-heading"></i> Nome Completo</label>
                    <input type="text" id="usuario-nome" class="form-control" required maxlength="50" placeholder="Digite o nome completo">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person-circle"></i> Nome de Usuário</label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="text" id="usuario-username" class="form-control" required maxlength="20" placeholder="Digite o nome de usuario">
                    </div>
                    <div class="form-text">4-20 caracteres (letras, números ou _)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                    <input type="email" id="usuario-email" class="form-control" required maxlength="50" placeholder="exemplo@dominio.com">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-telephone"></i> Telefone</label>
                    <input type="text" id="usuario-telefone" class="form-control" maxlength="20" placeholder="(xx) xxxxx-xxxx">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-shield-lock"></i> Nível de Acesso</label>
                    <select id="usuario-nivel" class="form-select" required>
                        <option value="Administrador">Administrador</option>
                        <option value="Funcionario">Funcionário</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-key"></i> Senha</label>
                    <div class="input-group">
                        <input type="password" id="usuario-senha" class="form-control" placeholder="Digite a senha" <?= !isset($usuario) ? 'required' : '' ?>>
                        <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">Mínimo 8 caracteres com letras e números <?= isset($usuario) ? '(opcional para edição)' : '(obrigatório)' ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Fechar
                </button>
                <button class="btn btn-primary" onclick="salvarUsuario()">
                    <i class="bi bi-save"></i> Salvar
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
  const logadoId = <?= json_encode($_SESSION['admin_id'] ?? null) ?>;
</script>
<script src="assets/js/usuarios.js"></script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
</body>
</html>


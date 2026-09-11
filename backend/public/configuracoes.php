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
    <title>Configurações - SGMP</title>
    <!-- head.php -->
    <?php include __DIR__ . '/../includes/head.php'; ?>

</head>

<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

<div class="container content py-4">
  <!-- Bloquear usuários com nível de funcionário -->
  <?php if (isset($_SESSION['admin_nivel']) && $_SESSION['admin_nivel'] === 'funcionario'): ?>
    <h2><i class="bi bi-gear"></i> Configurações do Sistema</h2>
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

    <h2><i class="bi bi-gear"></i> Configurações do Sistema</h2>

    <!-- Alerta com botão de ajuda -->
    <div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
      <div>
        <i class="bi bi-question-circle-fill fs-4 me-2"></i>
        Precisa de ajuda?
      </div>
      <button 
        class="btn btn-primary btn-sm ms-5 text-write"
        data-bs-toggle="modal" 
        data-bs-target="#modalHelpConfiguracoes"
      >
        <i class="bi bi-question-circle me-1"></i> Ver Ajuda
      </button>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal fade" id="modalHelpConfiguracoes" tabindex="-1" aria-labelledby="helpConfiguracoesLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="helpConfiguracoesLabel">
              <i class="bi bi-question-circle"></i> Ajuda - Configurações do Sistema
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <ul class="list-unstyled">
              <li>
                <i class="bi bi-building-fill-gear me-2 text-primary"></i>
                Na seção <strong>Dados da Empresa</strong>, atualize informações como razão social, endereço, CNPJ, telefone, e redes sociais, entre outros.
              </li>
              <li>
                <i class="bi bi-image-fill me-2 text-success"></i>
                Utilize o campo de <strong>Logo</strong> para alterar a imagem exibida no site.
              </li>
              <li>
                <i class="bi bi-clock-history me-2 text-info"></i>
                Acompanhe o <strong>Histórico de Logins</strong> para ver quem acessou o sistema e quando.
              </li>
              <li>
                <i class="bi bi-person-lock me-2 text-danger"></i>
                Somente <strong>administradores</strong> podem alterar dados da empresa.
              </li>
              <li>
                <i class="bi bi-lightbulb-fill me-2 text-warning"></i>
                Dica: mantenha os dados sempre atualizados e faça backups regulares para evitar perda de informações.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <hr>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-buildings"></i> Dados da Empresa
                </div>
                <div class="card-body">
                    <form id="formEmpresa">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="bi bi-building"></i> Razão Social</label>
                                <input type="text" id="empresa-razao" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="bi bi-geo-alt"></i> Endereço</label>
                                <input type="text" id="empresa-endereco" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="bi bi-upc-scan"></i> CNPJ</label>
                                <input type="text" id="empresa-cnpj" class="form-control">
                            </div>
                            </div>
                            <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="bi bi-telephone"></i> Telefone</label>
                                <input type="text" id="empresa-telefone" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                                <input type="email" id="empresa-email" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="bi bi-instagram"></i> Rede Social</label>
                                <input type="text" id="empresa-rede" class="form-control">
                            </div>
                            </div>
                            <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><i class="bi bi-card-text"></i> Descrição</label>
                                <textarea id="empresa-descricao" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-save"></i> Salvar Dados da Empresa
                        </button>
                    </form>
                    </div>

                    <div class="row mb-4">
                      <!-- Bloco da Logo -->
                      <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                          <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-image"></i> Logo da Empresa</span>
                            <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalUploadLogo">
                              <i class="bi bi-upload"></i> Alterar Logo
                            </button>
                          </div>
                          <div class="card-body text-center" id="logoAtual">
                            <p class="text-muted">Carregando logo...</p>
                          </div>
                        </div>
                      </div>

                      <!-- Bloco do botão de Histórico -->
                      <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                          <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-clock-history"></i> Histórico de Logins</span>
                            <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalHistorico">
                              <i class="bi bi-eye"></i> Ver Logins
                            </button>
                          </div>
                          <div class="card-body">
                            <p class="text-muted text-center">Clique no botão acima para visualizar os últimos acessos ao sistema.</p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Modal Upload de Logo -->
                    <div class="modal fade" id="modalUploadLogo" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title"><i class="bi bi-upload"></i> Enviar Nova Logo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <form id="formUploadLogo" method="POST" enctype="multipart/form-data">
                              <div class="mb-3">
                                <label class="form-label"><i class="bi bi-file-image"></i> Selecione uma imagem (PNG, JPG, JPEG, WEBP.)</label>
                                <input type="file" name="logo" accept="image/*" class="form-control" required>
                                <div class="form-text"><i class="bi bi-info-circle"></i> A imagem não pode exceder 2MB.</div>
                              </div>

                              <div id="progressoLogo" class="mt-3" style="display: none;">
                                <div class="progress">
                                  <div id="barraProgressoLogo" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                                </div>
                              </div>
                              <hr>
                              <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-upload"></i> Enviar Logo
                              </button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Modal do Histórico de Logins -->
                    <div class="modal fade" id="modalHistorico" tabindex="-1">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="bi bi-clock-history"></i> Últimos 5 Logins</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="table-responsive">
                              <table class="table table-striped table-hover table-bordered table-sm align-middle">
                                  <thead class="table-dark text-center">
                                    <tr>
                                      <th><i class="bi bi-person-circle"></i> Usuário</th>
                                      <th><i class="bi bi-calendar-event"></i> Data e Hora</th>
                                      <th><i class="bi bi-router"></i> IP</th>
                                      <th><i class="bi bi-laptop"></i> Dispositivo</th>
                                    </tr>
                                  </thead>
                                  <tbody id="historico-logins-table">
                                    <tr><td colspan="4" class="text-center">Carregando...</td></tr>
                                  </tbody>
                                </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

<script src="assets/js/configuracoes.js"></script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>

</body>
</html>


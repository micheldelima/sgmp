<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';

// Buscar dados do usuário logado
$idUsuario = $_SESSION['admin_id'];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->prepare("SELECT id, nome, username, email, telefone, nivel FROM usuario WHERE id = :id");
    $stmt->execute([':id' => $idUsuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        echo "Usuário não encontrado.";
        exit;
    }

} catch (PDOException $e) {
    echo "Erro ao buscar perfil: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Meu Perfil - SGMP</title>
  <!-- head.php -->
  <?php include __DIR__ . '/../includes/head.php'; ?>
</head>

<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <!-- Conteúdo Principal centralizado -->
    <div class="container content py-4">
      <h2><i class="bi bi-person-circle"></i> Meu Perfil</h2>

<!-- Alerta com botão de ajuda -->
<div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
  <div>
    <i class="bi bi-question-circle-fill fs-4 me-2"></i>
    Precisa de ajuda?
  </div>
  <button 
    class="btn btn-primary btn-sm ms-5 text-write"
    data-bs-toggle="modal" 
    data-bs-target="#modalHelpPerfil"
  >
    <i class="bi bi-question-circle me-1"></i> Ver Ajuda
  </button>
</div>

<!-- Modal de Ajuda -->
<div class="modal fade" id="modalHelpPerfil" tabindex="-1" aria-labelledby="helpPerfilLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="helpPerfilLabel">
          <i class="bi bi-question-circle"></i> Ajuda - Meu Perfil
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="list-unstyled">
          <li>
            <i class="bi bi-person-fill me-2 text-primary"></i>
            Nesta página você pode <strong>atualizar seus dados pessoais</strong> como nome, e-mail, telefone e nome de usuário.
          </li>
          <li>
            <i class="bi bi-lock-fill me-2 text-warning"></i>
            Para <strong>alterar sua senha</strong>, informe a senha atual e depois defina uma nova com pelo menos 8 caracteres, incluindo letras e números.
          </li>
          <li>
            <i class="bi bi-envelope-fill me-2 text-success"></i>
            Certifique-se de manter seu <strong>e-mail atualizado</strong> para recuperação de conta e comunicações administrativas.
          </li>
          <li>
            <i class="bi bi-shield-check me-2 text-info"></i>
            As alterações são salvas imediatamente após clicar em <strong>Salvar</strong>.
          </li>
          <li>
            <i class="bi bi-phone-fill me-2 text-success"></i>
            O campo de telefone aceita apenas números válidos no formato brasileiro (DDD + número).
          </li>
          <li>
            <li>
            <i class="bi bi-trash-fill me-2 text-danger"></i>
            Você pode <strong>excluir sua conta permanentemente</strong>.  
            Por segurança, será necessário digitar sua senha atual para confirmar a exclusão.  
            ⚠️ <strong>Atenção:</strong> esta ação é irreversível e removerá todos os seus dados do sistema.
          </li>
            <i class="bi bi-lightbulb-fill me-2 text-warning"></i>
            Dica: troque sua senha periodicamente e evite reutilizar senhas de outros sistemas.
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<hr>

      <form id="formPerfil">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label"><i class="bi bi-person"></i> Nome</label>
            <input type="text" id="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><i class="bi bi-person-badge"></i> Username</label>
            <input type="text" id="username" class="form-control" value="<?= htmlspecialchars($usuario['username']) ?>" required>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label"><i class="bi bi-envelope"></i> E-mail</label>
            <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><i class="bi bi-telephone"></i> Telefone</label>
            <input type="text" id="telefone" class="form-control" value="<?= htmlspecialchars($usuario['telefone']) ?>">
          </div>
        </div>
        <!-- Campo de senha atual -->
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-shield-lock"></i> Senha Atual</label>
          <input type="password" id="senha_atual" class="form-control" placeholder="Digite sua senha atual">
        </div>

        <!-- Campo de nova senha -->
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-lock"></i> Nova Senha</label>
          <input type="password" id="senha" class="form-control" placeholder="Deixe em branco para não alterar">
          <p>A senha deve conter no mínimo 8 caracteres, incluindo letras e números.</p>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-person-gear"></i> Nível de Acesso</label>
          <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($usuario['nivel']) ?>" readonly>
        </div>
        <button type="submit" class="btn btn-primary w-10 mt-2">
          <i class="bi bi-save"></i> Salvar
        </button>

        <button type="button" class="btn btn-outline-danger w-10 mt-2" data-bs-toggle="modal" data-bs-target="#modalExcluirConta">
          <i class="bi bi-trash"></i> Excluir Minha Conta
        </button>
      </form>
  </div>
</div>

<!-- Modal de confirmação para excluir conta -->
<div class="modal fade" id="modalExcluirConta" tabindex="-1" aria-labelledby="modalExcluirContaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalExcluirContaLabel"><i class="bi bi-exclamation-triangle-fill"></i> Confirmar Exclusão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p>Tem certeza que deseja excluir sua conta? Essa ação não poderá ser desfeita.</p>
        <div class="mb-3">
          <label for="senhaConfirmacao" class="form-label">Digite sua senha para confirmar:</label>
          <input type="password" id="senhaConfirmacao" class="form-control" placeholder="Sua senha atual">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" onclick="excluirConta()">Excluir Conta</button>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/meu_perfil.js"></script>
<!-- Rodapé -->
<?php include __DIR__ . '/../includes/rodape.php'; ?>

</body>
</html>

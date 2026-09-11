<?php 
session_start();
require_once '../config/database.php';

// Proteção contra CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Bloqueio de tentativas de login
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

// Bloqueio de 10 segundos
$tentativas_excedidas = false;
if ($_SESSION['login_attempts'] >= 10 && (time() - $_SESSION['last_attempt_time']) < 10) {
   $tentativas_excedidas = true;
    $tempo_restante = 10 - (time() - $_SESSION['last_attempt_time']);
}

// Processa o login
$usuario = null;

/// Processa o login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$tentativas_excedidas) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token inválido");
    }

    $username = trim($_POST['username']);
    $senha = trim($_POST['senha']);

    if (empty($username) || empty($senha)) {
        $erro = "Credenciais inválidas.";
    } elseif (strlen($username) > 50 || strlen($senha) > 50) {
        $erro = "Credenciais inválidas.";
    } else {
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        $stmt = $pdo->prepare("SELECT id, nome, username, senha, status, nivel 
                       FROM usuario 
                       WHERE username = :username 
                       LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);  

    if ($usuario) {
    if ($usuario['status'] !== 'ativo') {
        $erro = "Seu acesso foi desativado. Fale com o administrador.";
    } elseif (password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $usuario['id'];
        $_SESSION['admin_nome'] = $usuario['nome'];
        $_SESSION['admin_username'] = $usuario['username'];
        $_SESSION['admin_nivel'] = strtolower($usuario['nivel']);

        // Captura IP do usuário
        $ipUsuario = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if (!empty($_SESSION['admin_id'])) {
            $stmt = $pdo->prepare("INSERT INTO historico_logins (usuario_id, ip, dispositivo) VALUES (:usuario_id, :ip, :dispositivo)");
            $stmt->bindParam(':usuario_id', $_SESSION['admin_id'], PDO::PARAM_INT);
            $stmt->bindParam(':ip', $ipUsuario, PDO::PARAM_STR);
            $stmt->bindParam(':dispositivo', $userAgent, PDO::PARAM_STR);
            $stmt->execute();
        }

        unset($_SESSION['csrf_token']);
        $_SESSION['login_attempts'] = 0;

        header('Location: painel.php');
        exit;
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $erro = "Credenciais inválidas.";
    }
} else {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    $erro = "Credenciais inválidas.";
}
}
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SGMP - Painel Administrativo</title>

     <!-- Segurança -->
    <meta name="robots" content="noindex, nofollow">

    <!-- Bootstrap e Ícones -->
    <link href="../../public/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../public/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <script src="../../public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- CSS Customizado -->
    <link href="../../public/css/custom.css" rel="stylesheet">

    <!-- Favicons -->
    <link href="../../public/assets/img/favicon.ico" rel="icon">
    
    <!-- Main CSS File -->
    <link href="../../public/assets/css/main.css" rel="stylesheet">
    <link href="../../public/css/custom-login.css" rel="stylesheet">
    <link href="../../public/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="login-container">
            <div class="login-header mb-3">
                <i class="bi bi-shield-lock"></i>
                <h4 class="mt-2">SGMP - Painel Administrativo</h4>
            </div>

            <!-- Exibir mensagens de erro -->
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <!-- Exibir mensagem de logout -->
                <?php if (isset($_COOKIE['mensagem_logout'])): ?>
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2"></i> 
                        <?= htmlspecialchars($_COOKIE['mensagem_logout']) ?>
                    </div>
                    <?php setcookie("mensagem_logout", "", time() - 3600, "/"); // Apaga o cookie ?>
                <?php endif; ?>

                <?php if (isset($_GET['expirado'])): ?>
                  <div class="alert alert-warning text-center mt-3">
                    Sua sessão expirou por inatividade. Por favor, faça login novamente.
                  </div>
                <?php endif; ?>

            <!-- Exibir alerta de tentativas falhas -->
            <?php if ($tentativas_excedidas): ?>
                <div class="alert alert-warning d-flex align-items-center">
                    <i class="bi bi-clock me-2"></i>
                    <span>Muitas tentativas falhas. Atualize a página e tente novamente em <?= $tempo_restante ?> segundos.</span>
                </div>
            <?php else: ?>
                <!-- Formulário de Login Estilizado -->
                <form action="login.php" method="POST" class="p-4 border rounded shadow-sm bg-light">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <!-- Campo Nome de Usuário -->
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-person-fill"></i> Nome de Usuário</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" name="username" class="form-control" maxlength="50" required placeholder="Digite o nome de usuário">
                        </div>
                    </div>

                    <!-- Campo Senha -->
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-lock-fill"></i> Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" name="senha" class="form-control" maxlength="50" required placeholder="Digite sua senha">
                        </div>
                    </div>

                    <!-- Botão de Login -->
                    <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Entrar
                    </button>

                    <!-- Link "Esqueci a Senha" -->
                    <div class="text-center mt-3">
                        <a href="#" class="text-decoration-none text-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#modalEsqueciSenha">
                            <i class="bi bi-question-circle"></i> Esqueci a senha
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <!-- Modal Esqueci a Senha -->
        <div class="modal fade" id="modalEsqueciSenha" tabindex="-1" aria-labelledby="modalEsqueciSenhaLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEsqueciSenhaLabel"><i class="bi bi-lock"></i> Redefinir Senha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p>Função indisponível! Contate os desenvolvedores do sistema.</p>
                <div id="mensagemRedefinicao" class="mt-3"></div>
              </div>
            </div>
          </div>
        </div>
        <script>
        document.getElementById('formEsqueciSenha').addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('emailRedefinir').value;

            fetch('../routes/esqueci_senha.php', {
                method: 'POST',
                body: new URLSearchParams({ email: email }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('mensagemRedefinicao').innerHTML = `
                    <div class="alert alert-${data.status === 'success' ? 'success' : 'danger'}">
                        ${data.mensagem}
                    </div>`;
            })
            .catch(error => console.error("Erro:", error));
        });
        </script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
</body>
</html>
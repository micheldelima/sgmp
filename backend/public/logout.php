<?php
session_start();

// Define um cookie temporário para a mensagem de logout
setcookie("mensagem_logout", "Você saiu com sucesso!", time() + 2, "/");

// Remove todas as variáveis da sessão
$_SESSION = [];

// Remove o cookie de sessão (se existir)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroi a sessão completamente
session_destroy();

// Redireciona para a página de login
header("Location: login.php");
exit;
?>

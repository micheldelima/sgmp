<?php
// Configuração de cookies antes de iniciar a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, // Expira ao fechar o navegador
        'path' => '/',
        'domain' => '', // Domínio atual
        'secure' => isset($_SERVER['HTTPS']), // Apenas em HTTPS
        'httponly' => true, // Impede acesso do JavaScript ao cookie
        'samesite' => 'Strict' // Previne ataques CSRF
    ]);

    session_start();

    // Verificação de inatividade: 2 horas (7200 segundos)
        define('TEMPO_SESSAO', 2 * 60 * 60);

        if (isset($_SESSION['ultimo_acesso'])) {
            $inativo = time() - $_SESSION['ultimo_acesso'];
            if ($inativo > TEMPO_SESSAO) {
                session_unset();
                session_destroy();
                header("Location: login.php?expirado=1");
                exit;
            }
        }
        $_SESSION['ultimo_acesso'] = time();
}

// Regenera o ID da sessão para evitar fixação de sessão
if (!isset($_SESSION['session_regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['session_regenerated'] = true;
}

// Proteção contra acesso não autorizado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Definição de cabeçalhos de segurança (apenas se ainda não enviados)
if (!headers_sent()) {
    header("X-Frame-Options: DENY"); // Bloqueia iframes (proteção contra clickjacking)
    header("X-Content-Type-Options: nosniff"); // Evita que o navegador tente adivinhar o tipo de conteúdo
    header("Referrer-Policy: no-referrer"); // Bloqueia envio de informações do referenciador
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()"); // Bloqueia acesso a câmera, microfone e geolocalização

    // CSP reforçado 
    header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; font-src 'self' data:; img-src 'self' data:; frame-ancestors 'none';");
}
?>

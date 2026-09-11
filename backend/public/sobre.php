<?php
session_start();
require_once '../config/auth_middleware.php';
require_once '../config/database.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre o Sistema - SGMP</title>
    <!-- head.php -->
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>

<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <!-- Conteúdo Principal centralizado -->
    <div class="container content py-4">
        <h2 class="text-primary"><i class="bi bi-info-circle"></i> Sobre o Sistema</h2>
            <hr>
                <p>
                    O <strong>SGMP - Sistema de Gestão para Manicure e Pedicure 1.0</strong> é um projeto acadêmico desenvolvido para auxiliar profissionais da área de manicure e estética na gestão de agendamentos, serviços e portfólio. A aplicação conta com um painel administrativo para gerenciamento e um portal para clientes realizarem seus agendamentos de forma intuitiva e segura.
                </p>

                <h4 class="mt-4"><i class="bi bi-tools"></i> Tecnologias Utilizadas</h4>
                <p>O sistema foi desenvolvido utilizando as seguintes tecnologias:</p>

                <ul>
                    <li><strong>Backend:</strong> PHP (com PDO) e MySQL (MariaDB)</li>
                    <li><strong>Frontend:</strong> React.js, Bootstrap 5</li>
                    <li><strong>Comunicação:</strong> Axios (requisições HTTP), Fetch API (AJAX)</li>
                    <li><strong>Autenticação:</strong> Middleware de segurança, Hash de Senhas (password_hash)</li>
                    <li><strong>Desenvolvimento:</strong> XAMPP (localhost)</li>
                    <li><strong>Outros:</strong> Google Maps API, WhatsApp Web API para envio de confirmação de agendamentos diretamente no aplicativo.</li>
                </ul>

                <h4 class="mt-4"><i class="bi bi-folder"></i> Estrutura do Projeto</h4>
                <p>O sistema segue uma estrutura organizada para facilitar a manutenção e expansão:</p>

                <h5><i class="bi bi-server"></i> Backend (PHP/MySQL)</h5>
                <ul>
                    <li><strong>/backend/public/</strong> → Arquivos públicos do servidor (exemplo: login.php, clientes.php).</li>
                    <li><strong>/backend/routes/</strong> → APIs para comunicação com o frontend.</li>
                    <li><strong>/backend/config/</strong> → Configurações globais (banco de dados, autenticação, segurança).</li>
                    <li><strong>/backend/includes/</strong> → Blocos PHP reutilizáveis (head.php, munu_superior.php, footer.php).</li>
                </ul>

                <h5><i class="bi bi-code-slash"></i> Frontend (React.js)</h5>
                <ul>
                    <li><strong>/src/pages/</strong> → Páginas como Home.js, Contato.js, Servicos.js.</li>
                    <li><strong>/src/components/</strong> → Componentes reutilizáveis (Navbar.js, Footer.js).</li>
                    <li><strong>/public/</strong> → Imagens, CSS, ícones.</li>
                </ul>

                <h5><i class="bi bi-gear-wide-connected"></i> Configuração Global do Frontend</h5>
                <p>
                    O sistema utiliza um arquivo de configuração global no frontend chamado <strong>config.js</strong>. Esse arquivo centraliza URLs da API e caminhos de imagens para facilitar a manutenção do projeto. Com essa abordagem, qualquer mudança na URL do backend pode ser feita apenas uma vez no <code>config.js</code>, evitando a necessidade de atualizar todas as requisições manualmente.
                </p>
                <pre class="bg-dark text-light p-3 rounded">
                <code>
                const API_BASE_URL = "http://localhost/app-uepg/backend/routes/";
                const IMAGE_BASE_URL = "http://localhost/app-uepg/backend/public/assets/img/";

                export { API_BASE_URL, IMAGE_BASE_URL };
                </code>
                </pre>
                <li><strong>/frontend/src/config.js</strong></li>

                <h4 class="mt-5 text-danger"><i class="bi bi-shield-lock-fill"></i> Páginas com Acesso Restrito</h4>
                    <p>
                      Para garantir a <strong>segurança e controle das informações administrativas</strong>, algumas páginas do sistema são restritas exclusivamente a usuários com nível de acesso <strong>Administrador</strong>. Usuários com nível <strong>Funcionário</strong> possuem permissões limitadas, sendo impedidos de acessar áreas críticas da gestão do sistema:
                    </p>

                    <ul class="mb-4">
                      <li><strong><i class="bi bi-gear"></i> configuracoes.php:</strong> permite alterar os dados institucionais da empresa, logotipo e realizar backups manuais.</li>
                      <li><strong><i class="bi bi-bar-chart-line"></i> relatorios.php:</strong> exibe relatórios gerenciais, como faturamento mensal, clientes frequentes e serviços mais agendados.</li>
                      <li><strong><i class="bi bi-person-gear"></i> usuarios.php:</strong> possibilita o gerenciamento de contas de acesso, criação de novos usuários e alteração de permissões.</li>
                      <li><strong><i class="bi bi-people-fill"></i> funcionarios.php:</strong> permite o controle de dados dos profissionais vinculados à empresa, como status e profissão.</li>
                      <li><strong><i class="bi bi-hdd-fill"></i> empresa.php (API):</strong> manipula dados sensíveis da empresa por meio de requisições protegidas.</li>
                      <li><strong><i class="bi bi-lock"></i> Recursos restritos em modais:</strong> como envio de logo e confirmação de senha para gerar backups do sistema.</li>
                    </ul>

                    <div class="alert alert-warning d-flex align-items-start gap-2">
                      <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                      <div>
                        Caso um usuário com nível <strong>Funcionário</strong> tente acessar essas páginas, uma mensagem de <strong>Acesso Restrito</strong> será exibida e o conteúdo da página não será carregado.
                      </div>
                    </div>

                <h4 class="mt-4"><i class="bi bi-person-video3"></i> Desenvolvido por</h4>
                <p>Equipe de alunos (Michel, André, Gustavo) do <strong>3º ano do curso de Engenharia de Software 2025 - UEPG</strong>.</p>
                <p>Disciplina: <strong>Projeto de Software.</strong></p>
            </main>
        </div>
    </div>

    <!-- Rodapé -->
    <?php include __DIR__ . '/../includes/rodape.php'; ?>

</body>
</html>

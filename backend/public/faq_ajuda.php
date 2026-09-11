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
    <title>FAQ e Ajuda - SGMP</title>
    <!-- head.php -->
    <?php include __DIR__ . '/../includes/head.php'; ?>

</head>

<body class="bg-light">
    <!-- Menu superior -->
    <?php include __DIR__ . '/../includes/menu_superior.php'; ?>

    <!-- Conteúdo Principal centralizado -->
    <div class="container content py-4">
                <h2><i class="bi bi-question-circle"></i> FAQ e Ajuda</h2>
                <hr>

                <div class="accordion" id="faqAccordion">
                    <!-- Pergunta 1 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                Como faço para cadastrar um novo serviço?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Para cadastrar um novo serviço, vá até o menu superior "Cadastros" > "Serviços", clique em "Cadastrar Serviço", preencha os campos necessários e clique em "Salvar".
                            </div>
                        </div>
                    </div>

                    <!-- Pergunta 2 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                Como visualizar e gerenciar os agendamentos?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Acesse no menu superior "Agendamentos" para ver todos os horários reservados. Você pode filtrar por atendentes para ver agendamentos de hoje ou ver todos, pesquisar por uma data específica, e também pode pesquisar clientes pelo nome ou telefone. Você pode cancelar um agendamento clicando no botão de ação correspondente.
                            </div>
                        </div>
                    </div>

                    <!-- Pergunta 3 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                Como alterar minha senha?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No menu superior a direita "Meu perfil", você pode alterar sua senha informando a senha atual e a nova senha. A senha precisa ter no mínimo 8 caracteres incluindo letras e números.
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h3 class="mt-4"><i class="bi bi-lightbulb"></i> Dicas Rápidas</h3>
                    <ul class="list-group">
                      <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> Sempre mantenha os serviços (nome, descrição, valores e duração em minutos) atualizados.</li>
                      <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> Utilize descrições detalhadas e fotos no portfólio para atrair mais clientes.</li>
                      <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> Use um e-mail válido e telefone com DDD para notificações e comunicação com os clientes.</li>
                      <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> Acesse a página de agendamentos diariamente e utilize os filtros para visualizar o dia atual.</li>
                      <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> No agendamento público, o cliente deve informar um telefone válido para comunicação.</li>
                      <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> A função de envio de notificações por WhatsApp só está disponível em dispositivos móveis.</li>
                      <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> Apenas usuários com nível <strong>Administrador</strong> podem acessar relatórios, configurações, e usuários do sistema.</li>
                      <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> As sessões expiram após 2 horas de inatividade para garantir mais segurança.</li>
                    </ul>

        </div>
    </div>
    <?php include __DIR__ . '/../includes/rodape.php'; ?>

</body>
</html>

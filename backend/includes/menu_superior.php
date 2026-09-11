    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-stars"></i> SGMP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="painel.php">
                            <i class="bi bi-house-door"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                            <a class="nav-link text-light px-3 py-2" href="gerenciar_agendamentos.php">
                                <i class="bi bi-calendar-check"></i> Agendamentos
                            </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="cadastrosDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-folder-plus"></i> Cadastros
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="clientes.php"><i class="bi bi-people"></i> Clientes</a></li>
                        <li><a class="dropdown-item" href="servicos.php"><i class="bi bi-scissors"></i> Serviços</a></li>
                        <li><a class="dropdown-item" href="profissionais.php"><i class="bi bi-person-vcard"></i> Profissionais</a></li>
                        <li>
                          <a class="dropdown-item" href="bloqueios.php">
                            <i class="bi bi-calendar-x"></i> Horários bloqueados
                          </a>
                        </li>
                        <li><a class="dropdown-item" href="horarios.php"><i class="bi bi-clock-history"></i> Expediente</a></li>
                        <li><a class="dropdown-item" href="portfolio.php"><i class="bi bi-collection-play"></i> Portfólio</a></li>
                    </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorios.php">
                            <i class="bi bi-file-earmark-bar-graph"></i> Relatórios
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="configDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Configurações
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="usuarios.php"><i class="bi bi-people-fill"></i> Usuários do Sistema</a></li>
                            <li><a class="dropdown-item" href="configuracoes.php"><i class="bi bi-sliders"></i> Configurações Gerais</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="faq_ajuda.php"><i class="bi bi-question-circle"></i> FAQ e Ajuda</a></li>
                            <li><a class="dropdown-item" href="sobre.php"><i class="bi bi-info-circle"></i> Sobre o Sistema</a></li>
                        </ul>
                    </li>
                </ul>
                
                <!-- Ícone de Perfil com Dropdown -->
                <ul class="navbar-nav user-dropdown">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-4"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text">Olá, <strong><?= $_SESSION['admin_nome'] ?? 'Usuário' ?></strong>!</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="meu_perfil.php"><i class="bi bi-person"></i> Meu Perfil</a></li>
                            <li>
                              <div class="dropdown-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-moon me-1"></i> Tema Escuro </span>
                                <div class="form-check form-switch ms-3">
                                  <input class="form-check-input" type="checkbox" id="darkModeToggle">
                                </div>
                              </div>
                            </li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
function buscarPortfolio(termo = "", pagina = 1) {
            axios.get(`../routes/admin_portfolio.php?busca=${encodeURIComponent(termo)}&pagina=${pagina}`)
                .then(response => {
                    let tabela = document.getElementById("portfolio-table");
                    let mensagem = document.getElementById("mensagem-vazia");
                    tabela.innerHTML = "";

                    if (!response.data || !response.data.portfolio || response.data.portfolio.length === 0) {
                        mensagem.textContent = "Nenhum item encontrado.";
                        mensagem.style.display = "block";
                        return;
                    }

                    mensagem.style.display = "none";
                    response.data.portfolio.forEach(item => {
                        let linha = `<tr>
                            <td>${item.titulo}</td>
                            <td><img src="${item.imagem}" alt="${item.titulo}" style="width: 100px;"></td>
                            <td class="text-truncate">${item.descricao}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editarPortfolio(${item.id}, '${item.titulo}', '${item.descricao}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="excluirPortfolio(${item.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                        tabela.innerHTML += linha;
                    });

                    atualizarPaginacao(response.data.totalPaginas, pagina, termo);
                })
                .catch(error => {
                    console.error("Erro ao buscar itens do portfólio:", error);
                    alert("Erro ao buscar itens do portfólio. Verifique o console.");
                });
        }

        function atualizarPaginacao(totalPaginas, paginaAtual, termo) {
            let paginacao = document.getElementById("paginacao");
            paginacao.innerHTML = "";

            for (let i = 1; i <= totalPaginas; i++) {
                let classe = i === paginaAtual ? "active" : "";
                paginacao.innerHTML += `<li class="page-item ${classe}">
                    <a class="page-link" href="#" onclick="buscarPortfolio('${termo}', ${i})">${i}</a>
                </li>`;
            }
        }

        // Adiciona o evento de busca ao campo de busca
        document.getElementById("busca-portfolio").addEventListener("keyup", function() {
            buscarPortfolio(this.value);
        });

        // Carrega os itens do portfólio ao abrir a página
        document.addEventListener("DOMContentLoaded", function() {
            buscarPortfolio();
        });

        // Função para editar um item do portfólio
            function editarPortfolio(id, titulo, descricao) {
                document.getElementById("portfolio-id").value = id;
                document.getElementById("portfolio-titulo").value = titulo;
                document.getElementById("portfolio-descricao").value = descricao;
                document.getElementById("modal-title").innerHTML = '<i class="bi bi-pencil"></i> Editar Item do Portfólio';
                
                // Remover o atributo required da imagem para edição
                document.getElementById("portfolio-imagem").removeAttribute("required");
                
                // Abrir o modal
                let modal = new bootstrap.Modal(document.getElementById('modalPortfolio'));
                modal.show();
            }

            // Função para excluir um item do portfólio
           function excluirPortfolio(id) {
            if (confirm('Tem certeza que deseja excluir este item do portfólio?')) {
                axios({
                    method: 'DELETE',
                    url: '../routes/admin_portfolio.php',
                    data: JSON.stringify({ id: id }), // 🔹 Envia o ID como JSON
                    headers: {
                        'Content-Type': 'application/json' // 🔹 CORRETO para DELETE com corpo
                    }
                })
                .then(response => {
                    if (response.data.success) {
                        alert(response.data.success);
                        buscarPortfolio(document.getElementById("busca-portfolio").value);
                    } else {
                        alert(response.data.error || "Erro ao excluir item do portfólio.");
                    }
                })
                .catch(error => {
                    console.error("Erro na requisição:", error);
                    alert("Erro ao excluir item do portfólio. Verifique o console.");
                });
            }
        }

            // Função para limpar o modal ao fechar
            document.getElementById('modalPortfolio').addEventListener('hidden.bs.modal', function () {
                document.getElementById("portfolio-id").value = "";
                document.getElementById("portfolio-titulo").value = "";
                document.getElementById("portfolio-descricao").value = "";
                document.getElementById("portfolio-imagem").value = "";
                document.getElementById("portfolio-imagem").setAttribute("required", "");
                document.getElementById("modal-title").innerHTML = '<i class="bi bi-plus-lg"></i> Adicionar Item ao Portfólio';
            });

            // Função para salvar (criar ou atualizar) um item do portfólio
            function salvarPortfolio() {
                let id = document.getElementById("portfolio-id").value.trim();
                let titulo = document.getElementById("portfolio-titulo").value.trim();
                let imagem = document.getElementById("portfolio-imagem").files[0];
                let descricao = document.getElementById("portfolio-descricao").value.trim();

                if (!titulo || !descricao) {
                    alert("Título e descrição são obrigatórios!");
                    return;
                }

                if (!id && !imagem) {
                    alert("Imagem é obrigatória para novos itens!");
                    return;
                }

                let formData = new FormData();
                formData.append("id", id);
                formData.append("titulo", titulo);
                formData.append("descricao", descricao);
                if (imagem) {
                    formData.append("imagem", imagem);
                }

                const xhr = new XMLHttpRequest();
                const progresso = document.getElementById("progressoPortfolio");
                const barra = document.getElementById("barraProgressoPortfolio");

                progresso.style.display = "block";

                xhr.upload.addEventListener("progress", function (e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        barra.style.width = percent + "%";
                        barra.textContent = percent + "%";
                    }
                });

                xhr.onload = function () {
                    progresso.style.display = "none";
                    barra.style.width = "0%";
                    barra.textContent = "0%";

                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert(response.success);
                            let modal = bootstrap.Modal.getInstance(document.getElementById('modalPortfolio'));
                            modal.hide();
                            buscarPortfolio(document.getElementById("busca-portfolio").value);
                        } else {
                            alert(response.error || "Erro ao salvar item do portfólio.");
                        }
                    } catch (error) {
                        alert("Erro ao processar resposta do servidor.");
                        console.error(error);
                    }
                };

                xhr.onerror = function () {
                    progresso.style.display = "none";
                    barra.style.width = "0%";
                    barra.textContent = "0%";
                    alert("Erro na requisição.");
                };

                xhr.open("POST", "../routes/admin_portfolio.php", true);
                xhr.send(formData);
            }   

            
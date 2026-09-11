function buscarServicos(termo = "", pagina = 1) {
    axios.get(`../routes/admin_servicos.php?busca=${encodeURIComponent(termo)}&pagina=${pagina}`)
        .then(response => {
            let tabela = document.getElementById("servicos-table");
            let mensagem = document.getElementById("mensagem-vazia");
            tabela.innerHTML = "";

            // Verifica se os dados existem antes de acessar
            if (!response.data || !response.data.servicos || response.data.servicos.length === 0) {
                mensagem.textContent = "Nenhum serviço encontrado.";
                mensagem.style.display = "block";
                return;
            }

            mensagem.style.display = "none"; // Oculta mensagem
            response.data.servicos.forEach(servico => {
                let linha = `<tr>
                    <td>${servico.nome}</td>
                    <td class="text-truncate">${servico.descricao}</td>
                    <td>R$ ${parseFloat(servico.valor).toFixed(2).replace(".", ",")}</td>
                    <td>${servico.duracao} min</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editarServico(${servico.id}, '${servico.nome}', '${servico.descricao}', ${servico.valor}, ${servico.duracao})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="excluirServico(${servico.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
                tabela.innerHTML += linha;
            });

            atualizarPaginacao(response.data.totalPaginas, pagina, termo);
        })
        .catch(error => {
            console.error("Erro ao buscar serviços:", error);
            alert("Erro ao buscar serviços. Verifique o console.");
        });
}


        function atualizarPaginacao(totalPaginas, paginaAtual, termo) {
            let paginacao = document.getElementById("paginacao");
            paginacao.innerHTML = "";

            for (let i = 1; i <= totalPaginas; i++) {
                let classe = i === paginaAtual ? "active" : "";
                paginacao.innerHTML += `<li class="page-item ${classe}">
                    <a class="page-link" href="#" onclick="buscarServicos('${termo}', ${i})">${i}</a>
                </li>`;
            }
        }

        // Adiciona o evento de busca ao campo de busca
        document.getElementById("busca-servico").addEventListener("keyup", function() {
            buscarServicos(this.value);
        });

        // Carrega os serviços ao abrir a página
        document.addEventListener("DOMContentLoaded", function() {
            buscarServicos();
        });

        function salvarServico() {
    let id = document.getElementById("servico-id").value.trim();
    let nome = document.getElementById("servico-nome").value.trim();
    let descricao = document.getElementById("servico-descricao").value.trim();
    let valor = document.getElementById("servico-valor").value.trim();
    let duracao = document.getElementById("servico-duracao").value.trim();

    // Validação antes do envio
    if (!nome || !descricao || !valor || !duracao) {
        alert("Todos os campos são obrigatórios!");
        return;
    }

    // Convertendo para os tipos adequados
    valor = parseFloat(valor);
    duracao = parseInt(duracao, 10);

    if (isNaN(valor) || valor <= 0) {
        alert("O valor deve ser um número maior que 0.");
        return;
    }

    if (isNaN(duracao) || duracao <= 0) {
        alert("A duração deve ser um número maior que 0.");
        return;
    }

    let url = "../routes/admin_servicos.php";
    let metodo = id ? "put" : "post";
    let dados = { id, nome, descricao, valor, duracao };

    axios[metodo](url, dados)
        .then(response => {
            if (response.data.success) {
                alert(response.data.success);
                location.reload();
            } else {
                alert(response.data.error || "Erro ao salvar serviço.");
            }
        })
        .catch(() => alert("Erro ao salvar serviço."));
}


        function editarServico(id, nome, descricao, valor, duracao) {
            document.getElementById("modal-title").innerText = "Editar Serviço";
            document.getElementById("servico-id").value = id;
            document.getElementById("servico-nome").value = nome;
            document.getElementById("servico-descricao").value = descricao;
            document.getElementById("servico-valor").value = valor;
            document.getElementById("servico-duracao").value = duracao;
            new bootstrap.Modal(document.getElementById("modalServico")).show();
        }

        function excluirServico(id) {
            if (confirm("Tem certeza que deseja excluir?")) {
                axios.delete("../routes/admin_servicos.php", { data: { id } })
                    .then(() => {
                        alert("Serviço excluído com sucesso!");
                        location.reload();
                    })
                    .catch(() => alert("Erro ao excluir serviço."));
            }
        }
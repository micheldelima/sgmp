let debounceTimer = null;

function buscarClientes(termo = "", pagina = 1) {
    axios.get(`../routes/admin_clientes.php?busca=${encodeURIComponent(termo)}&pagina=${pagina}`)
        .then(response => {
            let tabela = document.getElementById("clientes-table");
            let mensagem = document.getElementById("mensagem-vazia");
            tabela.innerHTML = "";

            if (!response.data || !response.data.clientes || response.data.clientes.length === 0) {
                mensagem.style.display = "block";
                return;
            }

            mensagem.style.display = "none"; // Esconde se achou
            response.data.clientes.forEach(cliente => {
                let linha = `<tr>
                    <td>${cliente.nome}</td>
                    <td>${cliente.telefone}</td>
                    <td>${new Date(cliente.data_cadastro).toLocaleDateString('pt-BR')}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editarCliente(${cliente.id}, '${cliente.nome}', '${cliente.telefone}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="excluirCliente(${cliente.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
                tabela.innerHTML += linha;
            });

            atualizarPaginacao(response.data.totalPaginas, pagina, termo);
        })
        .catch(error => {
            console.error("Erro ao buscar clientes:", error);
            alert("Erro ao buscar clientes.");
        });
}


function atualizarPaginacao(totalPaginas, paginaAtual, termo) {
    let paginacao = document.getElementById("paginacao");
    paginacao.innerHTML = "";

    for (let i = 1; i <= totalPaginas; i++) {
        let classe = i === paginaAtual ? "active" : "";
        paginacao.innerHTML += `<li class="page-item ${classe}">
            <a class="page-link" href="#" onclick="buscarClientes('${termo}', ${i})">${i}</a>
        </li>`;
    }
}

document.getElementById("busca-cliente").addEventListener("keyup", function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        buscarClientes(this.value);
    }, 500);
});

document.addEventListener("DOMContentLoaded", function () {
    buscarClientes();
});

function salvarCliente() {
    let id = document.getElementById("cliente-id").value.trim();
    let nome = document.getElementById("nome").value.trim();
    let telefone = document.getElementById("cliente-telefone").value.trim();

    if (!nome || !telefone) {
        alert("Todos os campos são obrigatórios!");
        return;
    }

    let url = "../routes/admin_clientes.php";
    let metodo = id ? "put" : "post";
    let dados = { id, nome, telefone};

    axios[metodo](url, dados)
        .then(response => {
            if (response.data.success) {
                alert(response.data.success);
                location.reload();
            } else {
                alert(response.data.error || "Erro ao salvar cliente.");
            }
        })
        .catch(() => alert("Erro ao salvar cliente."));
}

function editarCliente(id, nome, telefone, data_nascimento) {
    document.getElementById("modal-title").innerText = "Editar Cliente";
    document.getElementById("cliente-id").value = id;
    document.getElementById("nome").value = nome;  // Corrigido
    document.getElementById("cliente-telefone").value = telefone;

    new bootstrap.Modal(document.getElementById("modalCliente")).show();
}

function excluirCliente(id) {
    if (confirm("Tem certeza que deseja excluir?")) {
        axios.delete("../routes/admin_clientes.php", { data: { id } })
            .then(() => {
                alert("Cliente excluído com sucesso!");
                location.reload();
            })
            .catch(() => alert("Erro ao excluir cliente."));
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const telefoneInput = document.getElementById('cliente-telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '');
            if (x.length > 11) x = x.slice(0, 11);
            if (x.length > 0) x = '(' + x;
            if (x.length > 2) x = x.slice(0, 3) + ') ' + x.slice(3);
            if (x.length > 8) x = x.slice(0, 10) + '-' + x.slice(10);
            else if (x.length > 7) x = x.slice(0, 9) + '-' + x.slice(9);
            e.target.value = x;
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const nomeInput = document.getElementById('nome');

    if (nomeInput) {
        nomeInput.addEventListener('input', function (e) {
            // Remove números e caracteres inválidos
            let valor = e.target.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');

            // Evita múltiplos espaços seguidos
            valor = valor.replace(/\s{2,}/g, ' ');

            // Capitaliza as palavras automaticamente (opcional)
            valor = valor.replace(/\b\w/g, l => l.toUpperCase());

            e.target.value = valor;
        });
    }
});

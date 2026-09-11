let debounceTimer = null;

function buscarBloqueios(termo = "", pagina = 1) {
    axios.get(`../routes/admin_bloqueios.php?busca=${encodeURIComponent(termo)}&pagina=${pagina}`)
        .then(response => {
            const tabela = document.getElementById("bloqueios-table");
            const mensagem = document.getElementById("mensagem-vazia");
            tabela.innerHTML = "";

            if (!response.data || !response.data.bloqueios || response.data.bloqueios.length === 0) {
                mensagem.style.display = "block";
                return;
            }

            mensagem.style.display = "none";
            response.data.bloqueios.forEach(b => {
                let linha = `<tr>
                    <td>${b.nome_profissional}</td>
                    <td>${b.data.split('-').reverse().join('/')}</td>
                    <td>${b.hora_inicio.substring(0,5)}</td>
                    <td>${b.hora_fim.substring(0,5)}</td>
                    <td>${b.motivo}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editarBloqueio(${b.id}, ${b.profissional_id}, '${b.data}', '${b.hora_inicio}', '${b.hora_fim}', '${b.motivo}')"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-danger btn-sm" onclick="excluirBloqueio(${b.id})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
                tabela.innerHTML += linha;
            });

            atualizarPaginacao(response.data.totalPaginas, pagina, termo);
        })
        .catch(error => {
            console.error("Erro ao buscar bloqueios:", error);
            alert("Erro ao buscar bloqueios.");
        });
}

function atualizarPaginacao(totalPaginas, paginaAtual, termo) {
    const paginacao = document.getElementById("paginacao");
    paginacao.innerHTML = "";

    for (let i = 1; i <= totalPaginas; i++) {
        let classe = i === paginaAtual ? "active" : "";
        paginacao.innerHTML += `<li class="page-item ${classe}">
            <a class="page-link" href="#" onclick="buscarBloqueios('${termo}', ${i})">${i}</a>
        </li>`;
    }
}

document.getElementById("busca-bloqueio").addEventListener("keyup", function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        buscarBloqueios(this.value);
    }, 500);
});

document.addEventListener("DOMContentLoaded", function () {
    buscarBloqueios();
});

function salvarBloqueio() {
    const id = document.getElementById("bloqueio-id").value.trim();
    const profissional_id = document.getElementById("bloqueio-profissional").value.trim();
    const data = document.getElementById("bloqueio-data").value.trim();
    const hora_inicio = document.getElementById("bloqueio-hora-inicio").value.trim();
    const hora_fim = document.getElementById("bloqueio-hora-fim").value.trim();
    const motivo = document.getElementById("bloqueio-motivo").value.trim();

    if (!profissional_id || !data || !hora_inicio || !hora_fim || !motivo) {
        alert("Todos os campos são obrigatórios.");
        return;
    }

    const url = "../routes/admin_bloqueios.php";
    const metodo = id ? "put" : "post";
    const dados = { id, profissional_id, data, hora_inicio, hora_fim, motivo };

    axios[metodo](url, dados)
        .then(response => {
            if (response.data.success) {
                alert(response.data.success);
                location.reload();
            } else {
                alert(response.data.error || "Erro ao salvar bloqueio.");
            }
        })
        .catch(() => alert("Erro ao salvar bloqueio."));
}

function editarBloqueio(id, profissional_id, data, hora_inicio, hora_fim, motivo) {
    document.getElementById("modal-title").innerText = "Editar Bloqueio";
    document.getElementById("bloqueio-id").value = id;
    document.getElementById("bloqueio-profissional").value = profissional_id;
    document.getElementById("bloqueio-data").value = data;
    document.getElementById("bloqueio-hora-inicio").value = hora_inicio;
    document.getElementById("bloqueio-hora-fim").value = hora_fim;
    document.getElementById("bloqueio-motivo").value = motivo;

    new bootstrap.Modal(document.getElementById("modalBloqueio")).show();
}

function excluirBloqueio(id) {
    if (confirm("Tem certeza que deseja excluir este bloqueio?")) {
        axios.delete("../routes/admin_bloqueios.php", { data: { id } })
            .then(response => {
                alert("Bloqueio excluído com sucesso!");
                location.reload();
            })
            .catch(() => alert("Erro ao excluir bloqueio."));
    }
}


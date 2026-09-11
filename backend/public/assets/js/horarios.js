 document.addEventListener("DOMContentLoaded", function () {
    carregarHorarios();

    document.getElementById("formAdicionarHorario").addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = {
            dia_inicio: document.getElementById("dia_inicio").value,
            dia_fim: document.getElementById("dia_fim").value,
            horario_abertura: document.getElementById("horario_abertura").value,
            horario_fechamento: document.getElementById("horario_fechamento").value,
            feriado: document.getElementById("feriado").value
        };

        fetch("../routes/adicionar_horario.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.mensagem);
            carregarHorarios();
            document.getElementById("formAdicionarHorario").reset();
        })
        .catch(error => console.error("Erro ao adicionar horário:", error));
    });
});

function carregarHorarios() {
    fetch("../routes/adicionar_horario.php")
        .then(response => response.json())
        .then(horarios => {
            const tabela = document.getElementById("listaHorarios");
            const form = document.getElementById("formAdicionarHorario");
            const alerta = document.getElementById("alertaHorarios");

            tabela.innerHTML = "";

            if (horarios && horarios.length > 0) {
                // esconde o formulário e mostra alerta
                form.style.display = "none";
                alerta.style.display = "block";
            } else {
                // mostra formulário e esconde alerta
                form.style.display = "block";
                alerta.style.display = "none";
            }

            if (horarios && horarios.length > 0) {
                horarios.forEach(horario => {
                    tabela.innerHTML += `
                        <tr>
                            <td>${horario.dia_inicio}</td>
                            <td>${horario.dia_fim}</td>
                            <td>${horario.horario_abertura.substring(0, 5)}</td>
                            <td>${horario.horario_fechamento.substring(0, 5)}</td>
                            <td>${horario.feriado}</td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="removerHorario(${horario.id})">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
        })
        .catch(error => console.error("Erro ao carregar horários:", error));
}

function removerHorario(id) {
    if (!confirm("Tem certeza que deseja remover este horário?")) return;

    fetch("../routes/adicionar_horario.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.mensagem);
        carregarHorarios();
    })
    .catch(error => console.error("Erro ao remover horário:", error));
}

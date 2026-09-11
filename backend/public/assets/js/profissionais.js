function buscarProfissionais(termo = '', pagina = 1) {
  axios.get(`../routes/admin_funcionarios.php?busca=${encodeURIComponent(termo)}&pagina=${pagina}`)
    .then(res => {
      const tabela = document.getElementById("profissionais-table");
      const mensagem = document.getElementById("mensagem-vazia");
      const paginacao = document.getElementById("paginacao");

      tabela.innerHTML = "";
      paginacao.innerHTML = "";

      if (!res.data.profissionais.length) {
        mensagem.style.display = "block";
        return;
      }

      mensagem.style.display = "none";

      res.data.profissionais.forEach(p => {
        const profissaoBadge = `<span class="badge bg-secondary">${p.profissao}</span>`;
        const statusBadge = p.disponivel == 1
          ? `<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Disponível</span>`
          : `<span class="badge bg-danger"><i class="bi bi-slash-circle-fill me-1"></i> Indisponível</span>`;

        tabela.innerHTML += `
          <tr>
            <td>${p.nome}</td>
            <td>${p.telefone || '-'}</td>
            <td>${profissaoBadge}</td>
            <td>${statusBadge}</td>
            <td>${new Date(p.data_criacao).toLocaleDateString('pt-BR')}</td>
            <td>
              <button class="btn btn-warning btn-sm" onclick="editarProfissional(${p.id}, ${p.usuario_id}, '${p.profissao}', ${p.disponivel})">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-danger btn-sm" onclick="excluirProfissional(${p.id})">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>`;
      });

      for (let i = 1; i <= res.data.totalPaginas; i++) {
        paginacao.innerHTML += `<li class="page-item ${i === pagina ? 'active' : ''}">
          <a class="page-link" href="#" onclick="buscarProfissionais('${termo}', ${i})">${i}</a>
        </li>`;
      }
    });
}

function carregarUsuarios() {
  axios.get("../routes/admin_funcionarios.php?usuarios=1")
    .then(res => {
      const select = document.getElementById("profissional-usuario");
      select.innerHTML = '<option value="">Selecione um usuário...</option>';
      res.data.usuarios.forEach(u => {
        select.innerHTML += `<option value="${u.id}">${u.nome} (${u.nivel})</option>`;
      });
    });
}

function salvarProfissional() {
  const id = document.getElementById("profissional-id").value;
  const usuario_id = document.getElementById("profissional-usuario").value;
  const profissao = document.getElementById("profissional-profissao").value;
  const disponivel = parseInt(document.getElementById("profissional-disponivel").value); 

  if (!usuario_id || !profissao) {
    alert("Usuário e profissão são obrigatórios");
    return;
  }

  axios({
    method: id ? 'put' : 'post',
    url: '../routes/admin_funcionarios.php',
    data: { id, usuario_id, profissao, status: disponivel },
    headers: { 'Content-Type': 'application/json' }
  }).then(res => {
    if (res.data.success) {
      alert(res.data.success);
      bootstrap.Modal.getInstance(document.getElementById('modalProfissional')).hide();
      buscarProfissionais();
    } else {
      alert(res.data.error || "Erro ao salvar profissional");
    }
  });
}

function editarProfissional(id, usuario_id, profissao, disponivel) {
  document.getElementById("modal-title").innerText = "Editar Profissional";
  document.getElementById("profissional-id").value = id;
  document.getElementById("profissional-usuario").value = usuario_id;
  document.getElementById("profissional-profissao").value = profissao;
  document.getElementById("profissional-disponivel").value = String(disponivel);
  new bootstrap.Modal(document.getElementById("modalProfissional")).show();
}

function excluirProfissional(id) {
  if (confirm("Deseja realmente excluir este profissional?")) {
    axios.delete('../routes/admin_funcionarios.php', {
      data: { id: id }
    })
    .then(res => {
      if (res.data.success) {
        alert(res.data.success);
        buscarProfissionais();
      } else {
        alert(res.data.error || "Erro ao excluir profissional.");
      }
    })
    .catch(err => {
      console.error("Erro ao excluir profissional:", err);
      alert("Erro ao excluir profissional.");
    });
  }
}

document.getElementById("busca-profissional").addEventListener("keyup", function () {
  buscarProfissionais(this.value);
});

document.addEventListener("DOMContentLoaded", () => {
  buscarProfissionais();
  carregarUsuarios();
});


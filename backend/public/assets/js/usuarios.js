// Toggle para mostrar/esconder senha
document.getElementById("toggleSenha").addEventListener("click", function() {
    const senhaInput = document.getElementById("usuario-senha");
    const icon = this.querySelector("i");
    if (senhaInput.type === "password") {
        senhaInput.type = "text";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    } else {
        senhaInput.type = "password";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    }
});

document.addEventListener("DOMContentLoaded", () => buscarUsuarios());

let paginaAtual = 1;

function buscarUsuarios(pagina = 1) {
    axios.get(`../routes/admin_usuarios.php?pagina=${pagina}`)
        .then(res => {
            const { usuarios, total, limite } = res.data;
            const tabela = document.getElementById("usuarios-table");
            const mensagem = document.getElementById("mensagem-vazia");
            const paginacao = document.getElementById("paginacao");

            tabela.innerHTML = "";
            paginacao.innerHTML = "";

            if (!usuarios.length) {
                mensagem.style.display = "block";
                return;
            }

            mensagem.style.display = "none";

            usuarios.forEach(u => {
                const nivelIcon = u.nivel?.toLowerCase() === "administrador" ? "bi-shield-fill-check" : "bi-person-fill-gear";
                const nivelText = u.nivel?.toLowerCase() === "administrador" ? "Administrador" : "Funcionário";
                const telefoneFormatado = u.telefone ? u.telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3') : '-';
                const statusBadge = u.status === 'ativo'
                    ? `<span class="badge bg-success">Ativo</span>`
                    : `<span class="badge bg-secondary">Inativo</span>`;
                const botaoStatus = u.nivel.toLowerCase() !== 'administrador' ? `
                    <button class="btn btn-sm btn-outline-${u.status === 'ativo' ? 'warning' : 'success'}" 
                            onclick="toggleStatus(${u.id}, '${u.status}')">
                        ${u.status === 'ativo' ? 'Desativar' : 'Ativar'}
                    </button>
                ` : '<span class="text-muted">-</span>';

                tabela.innerHTML += `
                    <tr>
                        <td>${u.nome}</td>
                        <td><span class="badge bg-secondary">@${u.username}</span></td>
                        <td>${u.email}</td>
                        <td>${telefoneFormatado}</td>
                        <td><i class="bi ${nivelIcon}"></i> ${nivelText}</td>
                        <td>${statusBadge}</td>
                        <td>${u.data_criacao ? new Date(u.data_criacao).toLocaleDateString('pt-BR') : '-'}</td>
                        <td>${botaoStatus}</td>
                    </tr>`;
            });

            // Paginação
            const totalPaginas = Math.ceil(total / limite);
            for (let i = 1; i <= totalPaginas; i++) {
                paginacao.innerHTML += `
                    <li class="page-item ${i === pagina ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="buscarUsuarios(${i})">${i}</a>
                    </li>
                `;
            }

            paginaAtual = pagina;
        })
        .catch(err => {
            console.error("Erro ao carregar usuários:", err);
            alert("Erro ao carregar usuários. " + (err.response?.data?.error || ""));
        });
}

function salvarUsuario() {
    // Obter todos os valores necessários
    const id = document.getElementById("usuario-id").value;
    const nome = document.getElementById("usuario-nome").value.trim();
    const username = document.getElementById("usuario-username").value.trim();
    const email = document.getElementById("usuario-email").value.trim();
    const telefone = document.getElementById("usuario-telefone").value.trim();
    const nivel = document.getElementById("usuario-nivel").value;
    const senha = document.getElementById("usuario-senha").value;

    // Elementos para validação visual
    const elementos = {
        nome: document.getElementById("usuario-nome"),
        username: document.getElementById("usuario-username"),
        email: document.getElementById("usuario-email"),
        nivel: document.getElementById("usuario-nivel"),
        senha: document.getElementById("usuario-senha")
    };

    // Resetar estilos de erro
    Object.values(elementos).forEach(el => el.classList.remove('is-invalid'));

    // Validação dos campos obrigatórios
    const camposObrigatorios = [
        { nome: 'nome', valor: nome },
        { nome: 'username', valor: username },
        { nome: 'email', valor: email },
        { nome: 'nivel', valor: nivel }
    ];

    const camposFaltantes = camposObrigatorios.filter(campo => !campo.valor);

    if (camposFaltantes.length > 0) {
        camposFaltantes.forEach(campo => {
            elementos[campo.nome].classList.add('is-invalid');
            elementos[campo.nome].focus();
        });
        alert(`Preencha os campos obrigatórios: ${camposFaltantes.map(c => c.nome).join(', ')}`);
        return;
    }

    // Validação específica para novo cadastro
    if (!id && !senha) {
        elementos.senha.classList.add('is-invalid');
        alert("Para novo cadastro, a senha é obrigatória.");
        return;
    }

    // Validação do formato do username
    if (!/^[a-zA-Z0-9_]{4,20}$/.test(username)) {
        elementos.username.classList.add('is-invalid');
        alert("Nome de usuário deve ter 4-20 caracteres (letras, números ou _)");
        return;
    }

    // Preparar dados para envio - incluindo telefone e nível
    const dados = { 
        id, 
        nome, 
        username, 
        email, 
        telefone,   
        nivel,      
        ...(senha && { senha }) // Envia senha apenas se existir
    };

    const metodo = id ? 'put' : 'post';

    // Enviar para a API
    axios({
        method: metodo,
        url: '../routes/admin_usuarios.php',
        data: dados,
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(res => {
        if (res.data.success) {
            alert(res.data.success);
            bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
            buscarUsuarios();
        } else {
            alert(res.data.error || "Erro ao salvar usuário.");
        }
    })
    .catch(err => {
        console.error("Erro detalhado:", err.response);
        const errorMsg = err.response?.data?.error || "Erro ao processar a requisição.";
        
        // Tratamento específico para erro de username/email duplicado
        if (err.response?.data?.error?.includes('já está em uso')) {
            if (err.response.data.error.includes('e-mail')) {
                elementos.email.classList.add('is-invalid');
                elementos.email.focus();
            } else {
                elementos.username.classList.add('is-invalid');
                elementos.username.focus();
            }
        }
        
        alert(errorMsg);
    });
}

function toggleStatus(id, statusAtual) {
  const acao = statusAtual === 'ativo' ? 'desativar' : 'ativar';
  if (!confirm(`Tem certeza que deseja ${acao} este usuário?`)) return;

  fetch('../routes/toggle_status_usuario.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, status: statusAtual })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.success);
      location.reload();
    } else {
      alert(data.error || 'Erro ao alterar status.');
    }
  })
  .catch(err => {
    console.error('Erro:', err);
    alert('Erro inesperado.');
  });
}

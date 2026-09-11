document.addEventListener("DOMContentLoaded", () => {
  carregarEmpresa();
  carregarLogoEmpresa();

  // Envio dos dados da empresa
  document.getElementById("formEmpresa").addEventListener("submit", e => {
    e.preventDefault();

    const dados = {
      razao_social: document.getElementById("empresa-razao").value,
      endereco: document.getElementById("empresa-endereco").value,
      cnpj: document.getElementById("empresa-cnpj").value,
      telefone: document.getElementById("empresa-telefone").value,
      email: document.getElementById("empresa-email").value,
      rede_social: document.getElementById("empresa-rede").value,
      descricao: document.getElementById("empresa-descricao").value
    };

    axios.put("../routes/admin_empresa.php", dados)
      .then(res => {
        alert(res.data.success || "Dados atualizados com sucesso!");
      })
      .catch(err => {
        alert("Erro ao salvar: " + (err.response?.data?.error || "Erro interno."));
      });
  });

  // Envio da nova logo
 document.getElementById("formUploadLogo").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  const xhr = new XMLHttpRequest();

  const progresso = document.getElementById("progressoLogo");
  const barra = document.getElementById("barraProgressoLogo");

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
      const data = JSON.parse(xhr.responseText);
      if (data.success) {
        alert(data.success);
        carregarLogoEmpresa();
        bootstrap.Modal.getInstance(document.getElementById("modalUploadLogo")).hide();
      } else {
        alert(data.error || "Erro ao enviar a logo.");
      }
    } catch (err) {
      console.error("Erro ao processar resposta:", err);
      alert("Erro ao processar resposta do servidor.");
    }
  };

  xhr.onerror = function () {
    progresso.style.display = "none";
    barra.style.width = "0%";
    barra.textContent = "0%";
    alert("Erro ao enviar a logo.");
  };

  xhr.open("POST", "../routes/admin_empresa.php", true);
  xhr.send(formData);
});


  // Evento para exibir o modal de histórico de logins
  document.getElementById("modalHistorico").addEventListener("show.bs.modal", carregarHistorico);
});

// Função para carregar dados da empresa
function carregarEmpresa() {
  axios.get("../routes/admin_empresa.php")
    .then(res => {
      const e = res.data;
      document.getElementById("empresa-razao").value = e.razao_social || "";
      document.getElementById("empresa-endereco").value = e.endereco || "";
      document.getElementById("empresa-cnpj").value = e.cnpj || "";
      document.getElementById("empresa-telefone").value = e.telefone || "";
      document.getElementById("empresa-email").value = e.email || "";
      document.getElementById("empresa-rede").value = e.rede_social || "";
      document.getElementById("empresa-descricao").value = e.descricao || "";
    });
}

// Função para carregar a logo atual da empresa
function carregarLogoEmpresa() {
  axios.get("../routes/admin_empresa.php")
    .then(res => {
      const logo = res.data.logo;
      const divLogo = document.getElementById("logoAtual");

      const basePath = window.location.pathname.split("/")[1];
      const LOGO_PATH = `${window.location.origin}/${basePath}/backend/public/assets/img/logo/`;


      if (logo) {
        divLogo.innerHTML = `
          <img 
            src="${LOGO_PATH + logo}" 
            alt="Logo da Empresa" 
            class="img-fluid shadow-sm rounded" 
            style="max-height: 100px;"
          >
        `;
      } else {
        divLogo.innerHTML = `
          <p class="text-muted">
            <i class="bi bi-image"></i> Nenhuma logo cadastrada.
          </p>
        `;
      }
    })
    .catch(error => {
      console.error("Erro ao carregar a logo:", error);
      document.getElementById("logoAtual").innerHTML = `
        <p class="text-danger">
          <i class="bi bi-exclamation-triangle"></i> Erro ao carregar a logo.
        </p>
      `;
    });
}

// Função para carregar histórico de logins no modal
function carregarHistorico() {
  axios.get("../routes/get_historico_logins.php")
    .then(res => {
      const table = document.getElementById("historico-logins-table");
      table.innerHTML = "";

      if (!res.data.length) {
        table.innerHTML = "<tr><td colspan='4' class='text-center'>Nenhum histórico encontrado.</td></tr>";
        return;
      }

      res.data.forEach(log => {
        const data = new Date(log.data_hora);
        const dataFormatada = `${data.toLocaleDateString('pt-BR')} às ${data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`;

        const row = document.createElement("tr");
        row.innerHTML = `
          <td class="text-nowrap">${log.nome}</td>
          <td class="text-nowrap">${dataFormatada}</td>
          <td class="text-nowrap">${log.ip}</td>
          <td class="text-truncate">${log.dispositivo}</td>
        `;
        table.appendChild(row);
      });
    })
    .catch(error => {
      console.error("Erro ao carregar histórico:", error);
      document.getElementById("historico-logins-table").innerHTML =
        "<tr><td colspan='4' class='text-center text-danger'>Erro ao carregar dados.</td></tr>";
    });
}

// Geração de backup com senha
function gerarBackup() {
  const senha = document.getElementById("senhaBackup").value.trim();

  if (!senha) {
    alert("Digite sua senha para continuar.");
    return;
  }

  fetch("../routes/backup_database.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ senha })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === "success") {
      window.location.href = data.link; // Redireciona para o download
    } else {
      alert(data.mensagem || "Erro ao gerar backup.");
    }
  })
  .catch(err => {
    console.error("Erro ao gerar backup:", err);
    alert("Erro ao gerar backup.");
  });
}


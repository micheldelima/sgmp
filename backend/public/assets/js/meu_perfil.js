document.getElementById("formPerfil").addEventListener("submit", function(e) {
  e.preventDefault();

  const dados = {
  nome: document.getElementById("nome").value,
  username: document.getElementById("username").value,
  email: document.getElementById("email").value,
  telefone: document.getElementById("telefone").value,
  senha: document.getElementById("senha").value,
  senha_atual: document.getElementById("senha_atual").value // novo campo incluído
};


  fetch("../routes/update_perfil.php", {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(dados)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.success);
      location.reload();
    } else {
      alert(data.error || "Erro ao atualizar perfil.");
    }
  })
  .catch(err => {
    console.error("Erro ao atualizar perfil:", err);
    alert("Erro inesperado.");
  });
});

function excluirConta() {
  const senha = document.getElementById("senhaConfirmacao").value.trim();

  if (!senha) {
    alert("Por favor, digite sua senha para confirmar.");
    return;
  }

  if (!confirm("Tem certeza? Essa ação é irreversível.")) {
    return;
  }

  fetch("../routes/excluir_conta.php", {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ senha })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Conta excluída com sucesso.");
      window.location.href = "logout.php";
    } else {
      alert(data.error || "Erro ao excluir conta.");
    }
  })
  .catch(err => {
    console.error("Erro ao excluir conta:", err);
    alert("Erro inesperado.");
  });
}


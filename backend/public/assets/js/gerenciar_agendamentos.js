function excluirAgendamento(id) {
                if (confirm('Tem certeza que deseja excluir este agendamento?')) {
                    fetch(`?acao=excluir&id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert(data.mensagem); // Exibe a mensagem de sucesso
                                window.location.reload(); // Recarrega a página para atualizar a lista de agendamentos
                            }
                        })
                        .catch(error => console.error('Erro:', error));
                }
            }

            // Função para buscar agendamentos dinamicamente
                function buscarAgendamentos(termo) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('busca', termo);
                    url.searchParams.delete('pagina'); // Ignorar paginação na busca dinâmica

                    fetch(url.toString())
                        .then(response => response.json())
                        .then(data => {
                            // Atualizar a tabela com os resultados
                            const tabela = document.querySelector('#agendamentos-table');
                            tabela.innerHTML = ''; // Limpar a tabela

                            if (data.length === 0) {
                                // Exibir mensagem se nenhum agendamento for encontrado
                                tabela.innerHTML = `
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <i class="bi bi-exclamation-circle me-2"></i> Nenhum agendamento encontrado.
                                        </td>
                                    </tr>
                                `;
                            } else {
                                // Preencher a tabela com os resultados
                                data.forEach(agendamento => {
                                    const linha = `
                                        <tr>
                                            <td>${agendamento.data} - ${agendamento.horario}</td>
                                            <td>${agendamento.nome}<br><small class="text-muted">${agendamento.telefone}</small></td>
                                                <td>${agendamento.servico} <span class="text-muted small">(R$ ${parseFloat(agendamento.valor).toFixed(2).replace('.', ',')})</span></td>
                                            <td>${agendamento.atendente || '---'}</td>
                                            <td class="text-break">${agendamento.observacao || '---'}</td>
                                            <td class="text-center">
                                                <a class="btn btn-success btn-sm" target="_blank" href="https://wa.me/55${agendamento.telefone.replace(/\D/g, '')}?text=Olá!%20Seu%20agendamento%20está%20confirmado%20para%20${agendamento.data}%20às%20${agendamento.horario}.">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                ${agendamento.confirmado == 1
                                                    ? '<i class="bi bi-check-circle text-success fs-5"></i>'
                                                    : '<i class="bi bi-x-circle text-danger fs-5"></i>'
                                                }
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm" onclick="excluirAgendamento(${agendamento.id})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                    tabela.innerHTML += linha;
                                });

                            }
                        })
                        .catch(error => console.error('Erro:', error));
                }

                // Debounce para evitar múltiplas requisições
                let timeout;
                document.getElementById('busca-agendamento').addEventListener('input', function () {
                    const termo = this.value.trim(); // Remove espaços em branco

                    // Limpar o timeout anterior
                    clearTimeout(timeout);

                    // Aguardar 300ms antes de enviar a requisição
                    timeout = setTimeout(() => {
                        buscarAgendamentos(termo);
                    }, 300);
                });


        function filtrar(filtro) {
            const url = new URL(window.location.href);
            url.searchParams.set('filtro', filtro);
            url.searchParams.delete('data'); // Remove o filtro de data ao usar "Hoje" ou "Todos"
            url.searchParams.set('pagina', 1); // Redireciona para a primeira página
            window.location.href = url.toString();
        }

        document.getElementById('busca-data').addEventListener('change', function () {
            const data = this.value;
            if (data) {
                const url = new URL(window.location.href);
                url.searchParams.set('data', data);
                url.searchParams.delete('filtro'); // Remove o filtro ao usar a busca por data
                url.searchParams.set('pagina', 1); // Redireciona para a primeira página
                window.location.href = url.toString();
            }
        });

        document.getElementById('busca-data').addEventListener('change', function () {
            const data = this.value;
            if (data) {
                const url = new URL(window.location.href);
                url.searchParams.set('data', data);
                url.searchParams.set('pagina', 1); // Resetar para a primeira página
                url.searchParams.delete('filtro'); // Remover o filtro "Hoje" ou "Todos"
                window.location.href = url.toString();
            }
        });


      document.addEventListener("DOMContentLoaded", () => {
      carregarAtendentes();
        });

        function carregarAtendentes() {
          const selectAtendente = document.querySelector("#formNovoAgendamento select[name='atendente']");

          // Limpa e adiciona o option padrão
          selectAtendente.innerHTML = `<option value="">Selecione um atendente</option>`;

          fetch("../routes/admin_agendamentos.php?action=listar_atendentes")
            .then(response => response.json())
            .then(data => {
              if (data.error) {
                console.warn("Erro:", data.error);
                return;
              }

              data.forEach(atendente => {
                const option = document.createElement("option");
                option.value = atendente.usuario_id;
                option.textContent = atendente.nome_atendente;
                selectAtendente.appendChild(option);
              });
            })
            .catch(err => {
              console.error("Erro ao buscar atendentes:", err);
            });
        }

// Filtro por atendente
document.getElementById('filtro-atendente').addEventListener('change', function () {
  const atendenteId = this.value;
  const url = new URL(window.location.href);

  // Atualiza ou remove o parâmetro
  if (atendenteId) {
    url.searchParams.set('atendente_id', atendenteId);
  } else {
    url.searchParams.delete('atendente_id');
  }

  // Reinicia na primeira página
  url.searchParams.set('pagina', 1);

  // Redireciona
  window.location.href = url.toString();
});

function abrirModalEditarAgendamento(id) {
  fetch(`../routes/admin_agendamentos.php?action=buscar&id=${id}`)
    .then(res => res.json())
    .then(async dados => {
      if (dados.error) {
        alert(dados.error);
        return;
      }

      // Preencher campos básicos
      document.getElementById('edit-id').value = dados.id;
      document.getElementById('edit-nome').value = dados.nome || '';
      document.getElementById('edit-telefone').value = dados.telefone || '';
      document.getElementById('edit-data').value = dados.data;
      document.getElementById('edit-observacao').value = dados.observacao || '';

      // Buscar serviços
      const resServ = await fetch('../routes/admin_agendamentos.php?action=listar_servicos');
      const servicos = await resServ.json();
      const servicoSelect = document.getElementById('edit-servico');
      servicoSelect.innerHTML = '';
      servicos.forEach(serv => {
        const option = document.createElement('option');
        option.value = serv.id;
        option.textContent = `${serv.nome} - R$${serv.valor}`;
        servicoSelect.appendChild(option);
      });

      // Buscar atendentes
      const resAt = await fetch('../routes/admin_agendamentos.php?action=listar_atendentes');
      const atendentes = await resAt.json();
      const atendenteSelect = document.getElementById('edit-atendente');
      atendenteSelect.innerHTML = '';
      atendentes.forEach(at => {
        const option = document.createElement('option');
        option.value = at.usuario_id;
        option.textContent = at.nome_atendente;
        atendenteSelect.appendChild(option);
      });

      // Setar valor selecionado
      servicoSelect.value = dados.servico ?? servicoSelect.options[0]?.value;
      atendenteSelect.value = dados.atendente_id;

      // Buscar horários disponíveis com base na data, atendente e serviço
      const resHora = await fetch(`../routes/admin_agendamentos.php?action=listar_horarios&data=${dados.data}&atendente=${dados.atendente_id}&servico=${servicoSelect.value}`);
      const horariosData = await resHora.json();
      const horarioSelect = document.getElementById('edit-horario');
      horarioSelect.innerHTML = '';

      if (horariosData.horarios_disponiveis?.length) {
        horariosData.horarios_disponiveis.forEach(h => {
          const option = document.createElement('option');
          option.value = h;
          option.textContent = h;
          horarioSelect.appendChild(option);
        });

        // Selecionar o horário atual do agendamento, se ainda estiver disponível
        horarioSelect.value = dados.horario;
      } else {
        const opt = document.createElement('option');
        opt.textContent = 'Nenhum horário disponível';
        opt.disabled = true;
        horarioSelect.appendChild(opt);
      }

      // Mostrar modal
      const modal = new bootstrap.Modal(document.getElementById('modalEditarAgendamento'));
      modal.show();
    })
    .catch(err => {
      console.error('Erro ao carregar agendamento:', err);
      alert('Erro ao buscar dados para edição.');
    });
}

function carregarHorariosDisponiveis() {
  const data = document.getElementById('edit-data').value;
  const servico = document.getElementById('edit-servico').value;
  const atendente = document.getElementById('edit-atendente').value;

  if (data && servico && atendente) {
    fetch(`../routes/admin_agendamentos.php?action=listar_horarios&data=${data}&servico_id=${servico}&atendente=${atendente}`)
      .then(res => res.json())
      .then(resp => {
        const selectHor = document.getElementById('edit-horario');
        selectHor.innerHTML = '';
        if (Array.isArray(resp.horarios_disponiveis)) {
          resp.horarios_disponiveis.forEach(h => {
            const opt = document.createElement('option');
            opt.value = h;
            opt.textContent = h;
            selectHor.appendChild(opt);
          });
        } else {
          const opt = document.createElement('option');
          opt.textContent = 'Nenhum horário disponível';
          selectHor.appendChild(opt);
        }
      });
  }
}

// Atualizar horários quando alterar campos
['edit-data', 'edit-servico', 'edit-atendente'].forEach(id => {
  document.getElementById(id).addEventListener('change', carregarHorariosDisponiveis);
});


function confirmarAgendamento(id, confirmar) {
  const msg = confirmar
    ? "Deseja marcar este agendamento como confirmado?"
    : "Deseja remover a confirmação deste agendamento?";

  if (confirm(msg)) {
    fetch(`../routes/admin_agendamentos.php?acao=confirmar&id=${id}&valor=${confirmar ? 1 : 0}`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          location.reload(); 
        }
      })
      .catch(err => console.error('Erro ao confirmar agendamento:', err));
  }
}

document.getElementById('formEditarAgendamento').addEventListener('submit', function(e) {
  e.preventDefault();

  const dados = {
    id: document.getElementById('edit-id').value,
    data: document.getElementById('edit-data').value,
    horario: document.getElementById('edit-horario').value,
    servico: document.getElementById('edit-servico').value,
    atendente: document.getElementById('edit-atendente').value,
    observacao: document.getElementById('edit-observacao').value
  };

  fetch('../routes/admin_agendamentos.php?action=editar', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(dados)
  })
  .then(res => res.json())
  .then(resposta => {
    if (resposta.status === 'success') {
      alert('Agendamento atualizado com sucesso!');
      location.reload();
    } else {
      alert(resposta.mensagem || 'Erro ao atualizar agendamento.');
    }
  })
  .catch(err => {
    console.error('Erro no fetch:', err);
    alert('Erro ao enviar dados. Verifique a conexão.');
  });
});

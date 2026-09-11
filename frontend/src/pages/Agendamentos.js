import React, { useEffect, useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import API_BASE_URL from "../config";

const Agendamentos = () => {
  const navigate = useNavigate();
  const [servicos, setServicos] = useState([]);
  const [horarios, setHorarios] = useState([]);
  const [mensagem, setMensagem] = useState(null);
  const [horarioAtendimento, setHorarioAtendimento] = useState(null);
  const [atendentes, setAtendentes] = useState([]);
  const [diasPermitidos, setDiasPermitidos] = useState([]);
  const [etapa, setEtapa] = useState(1);
  const [clienteEncontrado, setClienteEncontrado] = useState(null);
  const [mostrarModal, setMostrarModal] = useState(false);
  const [mostrarCadastro, setMostrarCadastro] = useState(false);
  const [modoCadastroManual, setModoCadastroManual] = useState(false);
  const [modalMensagem, setModalMensagem] = useState(null);
  const [resumoDados, setResumoDados] = useState({});
  const [carregandoAgds, setCarregandoAgds] = useState(false);
  const [meusAgds, setMeusAgds] = useState([]);
  const [mostrarListaAgds, setMostrarListaAgds] = useState(false);
  const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
  const [pergunta, setPergunta] = useState("");
  const [respostaCorreta, setRespostaCorreta] = useState(null);
  const [respostaUsuario, setRespostaUsuario] = useState("");


  const [formData, setFormData] = useState({
    nome: "",
    telefone: "",
    servico: "",
    data: "",
    horario: "",
    atendente: "",
    observacao: "",
  });

  useEffect(() => {
  if (etapa === 3) {
    const n1 = Math.floor(Math.random() * 10) + 1;
    const n2 = Math.floor(Math.random() * 10) + 1;
    setPergunta(`Quanto é ${n1} + ${n2}?`);
    setRespostaCorreta(n1 + n2);
    setRespostaUsuario("");
  }
}, [etapa]);

useEffect(() => {
  setMensagem(null); 
}, [etapa]);

useEffect(() => {
  if (formData.servico && formData.data && formData.atendente) {
    buscarHorarios(formData.servico, formData.data, formData.atendente);
  }
}, [formData.servico, formData.data, formData.atendente]);


const formatarData = (dataString) => {
  if (!dataString) return '';
  const [ano, mes, dia] = dataString.split('-');
  return `${dia}/${mes}/${ano}`;
};

  useEffect(() => {
    if (!isMobile) {
      setMensagem({ texto: "Agendamentos só podem ser feitos via celular.", tipo: "warning" });
    }

    axios.get(`${API_BASE_URL}admin_agendamentos.php?action=listar_servicos`).then(response => {
      if (Array.isArray(response.data)) setServicos(response.data);
    });

    axios.get(`${API_BASE_URL}get_horarios_atendimento.php`).then(response => {
      if (response.data && !response.data.error) setHorarioAtendimento(response.data);
    });

    axios.get(`${API_BASE_URL}admin_agendamentos.php?action=listar_atendentes`).then(response => {
      if (Array.isArray(response.data)) setAtendentes(response.data);
    });
  }, []);

  useEffect(() => {
    axios.get(`${API_BASE_URL}admin_agendamentos.php?action=dias_permitidos`).then(response => {
      if (Array.isArray(response.data)) {
        setDiasPermitidos(response.data.map(Number));
      }
    });
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;

    if (name === "data" && diasPermitidos.length > 0) {
      const diaSemana = new Date(value).getDay();
      if (!diasPermitidos.includes(diaSemana)) {
        alert("O estúdio não abre neste dia da semana.");
        return setFormData(prev => ({ ...prev, data: "" }));
      }
    }

    const formatTelefone = (input) => {
      return input.replace(/\D/g, "").replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
    };

    const novoForm = {
      ...formData,
      [name]: name === "telefone" ? formatTelefone(value) : value
    };

    setFormData(novoForm);

    if ((name === "servico" || name === "data") && novoForm.servico && novoForm.data) {
      buscarHorarios(novoForm.servico, novoForm.data);
    }
  };

  const buscarHorarios = (servico, data, atendente) => {
  if (!servico || !data || !atendente) return;
  
  axios.get(`${API_BASE_URL}admin_agendamentos.php?action=listar_horarios&servico_id=${servico}&data=${data}&atendente=${atendente}`)
    .then(response => {
      setHorarios(response.data.horarios_disponiveis || []);
    })
    .catch(err => {
      console.error("Erro ao buscar horários:", err);
    });
};

  const buscarCliente = () => {
    const telefoneLimpo = formData.telefone.replace(/\D/g, '');
    if (telefoneLimpo.length < 10) return;

    axios.get(`${API_BASE_URL}buscar_cliente.php?telefone=${telefoneLimpo}`)
      .then(response => {
        if (response.data && response.data.id) {
          setClienteEncontrado(response.data);
          setMostrarModal(true);
        } else {
          setClienteEncontrado(null);
          setMostrarCadastro(false);
          setModalMensagem("Cliente não encontrado! Faça um cadastro rápido para agendar. Clique no botão abaixo.");
        }
      })
      .catch(() => {
        setClienteEncontrado(null);
        setMostrarCadastro(false);
        setModalMensagem("Erro ao buscar telefone.");
      });
  };

  const confirmarCliente = () => {
    setFormData(prev => ({
      ...prev,
      nome: clienteEncontrado.nome,
    }));
    setEtapa(2);
  };

  const avancarEtapa = () => setEtapa(prev => prev + 1);

  const handleSubmit = async (e) => {
  e.preventDefault();

  axios.post(`${API_BASE_URL}admin_agendamentos.php`, {
    ...formData,
    cliente_id: clienteEncontrado?.id || null
  })
    .then(response => {
      if (response.data.error) {
        setMensagem({ texto: response.data.error, tipo: "danger" });
        return;
      }

      axios.get(`${API_BASE_URL}get_empresa.php`)
        .then(res => {
          const telefoneEmpresaria = res.data?.telefone?.replace(/\D/g, '');

          const servicoSelecionado = servicos.find(s => s.id == formData.servico);
          const atendenteSelecionado = atendentes.find(a => a.usuario_id == formData.atendente);
          const valor = servicoSelecionado ? parseFloat(servicoSelecionado.valor).toFixed(2) : '';

          navigate("/agendamento-sucesso", {
            state: {
              nome: formData.nome,
              telefone: formData.telefone,
              servico: servicoSelecionado ? servicoSelecionado.nome : '',
              data: formData.data,
              horario: formData.horario,
              atendente: atendenteSelecionado ? atendenteSelecionado.nome_atendente : '',
              valor: ` ${valor}`,
              observacao: formData.observacao || "",
              telefoneEmpresa: telefoneEmpresaria,
              abrirWhatsApp: true
            }
          });
        });
    })
    .catch(() => setMensagem({ texto: "Erro ao realizar agendamento.", tipo: "danger" }));
};



async function carregarMeusAgendamentos() {
  if (!clienteEncontrado) return;

  setCarregandoAgds(true);
  setMostrarListaAgds(true);
  setMeusAgds([]);

  try {
    const params = {};
    if (clienteEncontrado.id) params.cliente_id = clienteEncontrado.id;
    if (clienteEncontrado.telefone) params.telefone = clienteEncontrado.telefone;

    console.log("Buscando meus agendamentos com params:", params);

        const { data } = await axios.get(`${API_BASE_URL}agendamentos_do_cliente.php`, {
      params: {
        cliente_id: clienteEncontrado?.id,
        telefone: clienteEncontrado?.telefone
      }
    });


    console.log("Resposta meus agendamentos:", data);

    setMeusAgds(Array.isArray(data.agendamentos) ? data.agendamentos : []);
  } catch (e) {
    console.error("Erro ao carregar meus agendamentos:", e);
    setMeusAgds([]);
  } finally {
    setCarregandoAgds(false);
  }
}

  return (
    <>
      {modalMensagem && (
        <div className="modal fade show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h3 className="modal-title">
                  <i className="bi bi-exclamation-triangle-fill  me-3"></i>
                  Aviso
                </h3>
                <button type="button" className="btn-close" onClick={() => setModalMensagem(null)}></button>
              </div>
              <div className="modal-body">
                <p>
                  <i className="bi bi-info-circle me-2 text-primary"></i>
                  {modalMensagem}
                </p>
              </div>
              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-primary d-flex justify-content-center align-items-center gap-2 mx-auto"
                  onClick={() => {
                    setModalMensagem(null);
                    setMostrarCadastro(true);
                    setModoCadastroManual(true);
                  }}
                >
                  <i className="bi bi-arrow-right-circle"></i>
                  Cadastre-se aqui
                </button>
              </div>
            </div>
          </div>
        </div>
      )}


      {mostrarModal && (
      <div className="modal fade show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
        <div className="modal-dialog">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title">
                <i className="bi bi-person-badge me-2"></i> Confirmação de Identidade
              </h5>
              <button type="button" className="btn-close" onClick={() => setMostrarModal(false)}></button>
            </div>
            <div className="modal-body">
              

              <div className="modal-body">
                <p className="mb-2">
                  <i className="bi bi-question-circle me-2"></i> É você? Verifique seus dados.
                </p>
                <p style={{ wordBreak: 'break-word', overflowWrap: 'break-word' }}>
                  <i className="bi bi-person me-2"></i>
                  <strong>Nome:</strong> {clienteEncontrado.nome}
                </p>
                <p>
                  <i className="bi bi-whatsapp me-2"></i>
                  <strong>WhatsApp:</strong> {clienteEncontrado.telefone}
                </p>
                
                {/* Linha de Separação */}
                 <hr className="my-4"/>

                {/* Lista de agendamentos futuros */}
                {mostrarListaAgds && (
                  <div className="mt-3">
                    <h6 className="d-flex align-items-center mb-2">
                      <i className="bi bi-calendar2-week me-2"></i> Meus agendamentos (futuros)
                    </h6>

                    {carregandoAgds && (
                      <div className="text-center py-2">
                        <div className="spinner-border" role="status" aria-hidden="true"></div>
                      </div>
                    )}

                    {!carregandoAgds && meusAgds.length === 0 && (
                      <div className="alert alert-warning py-2 mb-0">
                        Nenhum agendamento futuro encontrado.
                      </div>
                    )}

                    {!carregandoAgds && meusAgds.length > 0 && (
                      <ul className="list-group">
                        {meusAgds.map(a => {
                          const d = new Date(a.data + 'T' + (a.horario || '00:00:00'));
                          const dataFmt = d.toLocaleDateString('pt-BR');
                          const horaFmt = (a.horario || '').slice(0,5);
                          return (
                            <li key={a.id} className="list-group-item d-flex justify-content-between align-items-center">
                              <div>
                                <div className="fw-semibold">
                                  <i className="bi bi-calendar-event me-1"></i>
                                  {dataFmt} <span className="text-muted">às</span> {horaFmt}
                                </div>
                                {a.servico_nome && (
                                  <small className="text-muted d-block mt-1">
                                    <i className="bi bi-scissors me-1"></i>
                                    {a.servico_nome}
                                  </small>
                                )}
                              </div>
                              <span className="badge bg-primary rounded-pill">Futuro</span>
                            </li>
                          );
                        })}
                      </ul>
                    )}
                  </div>
                )}
              </div>


            </div>
            <div className="modal-footer d-flex flex-column gap-2">

                {/* Botão de confirmação */}
                <button
                  type="button"
                  className="btn btn-success btn-lg w-100 d-flex justify-content-center align-items-center gap-2 shadow-sm"
                  style={{ borderRadius: "8px" }}
                  onClick={() => {
                    confirmarCliente();
                    setMostrarModal(false);
                  }}
                >
                  <i className="bi bi-check2-circle fs-5"></i>
                  Confirmar identidade
                </button>

                {/* Botão de ver agendamentos */}
                <button
                  type="button"
                  className="btn btn-danger w-100 d-flex justify-content-center align-items-center gap-2 shadow-sm"
                  style={{ borderRadius: "8px" }}
                  onClick={carregarMeusAgendamentos}
                >
                  <i className="bi bi-calendar-week fs-5"></i>
                  Meus agendamentos
                </button>

                {/* Botão de cadastrar novo número */}
                <button
                  type="button"
                  className="btn btn-primary w-100 d-flex justify-content-center align-items-center gap-2 shadow-sm"
                  style={{ borderRadius: "8px" }}
                  onClick={() => {
                    setMostrarModal(false);
                    setMostrarCadastro(true);
                    setModoCadastroManual(true);
                    setFormData({
                      nome: "",
                      telefone: "",
                      servico: "",
                      data: "",
                      horario: "",
                      atendente: "",
                      observacao: "",
                      captcha: ""
                    });
                  }}
                >
                  <i className="bi bi-phone fs-5"></i>
                  Cadastrar novo número
                </button>

              </div>

          </div>
        </div>
      </div>
    )}


      <div className="container mt-5 d-flex justify-content-center">
        <div className="card p-4 shadow-lg w-100" style={{ maxWidth: '650px' }}>
          <h1 className="text-center mb-4 text-primary">
            <i className="bi bi-calendar-check me-2"></i> Agendamento de Serviços
          </h1>

          <div className="text-center mb-4">
            <p className="text-muted">
              Preencha o formulário abaixo<br />
              Etapa {etapa} de 4
            </p>
          </div>

          {mensagem && <div className={`alert alert-${mensagem.tipo}`}>{mensagem.texto}</div>}

          {!isMobile ? (
            <div className="alert alert-warning">
              <i className="bi bi-phone"></i> Verifique seu dispositivo! Agendamentos são feitos via mobile exclusivamente.
            </div>
          ) : (
            <form
                onSubmit={(e) => {
                  e.preventDefault();
                  if (etapa === 4) {
                    handleSubmit(e);
                  }
                }}
              >
              {etapa === 1 && (
              <div className="bg-light p-4 rounded border border-primary-subtle">
                <h5 className="text-primary mb-3">
                  {modoCadastroManual ? "Cadastro de Cliente" : "Faça seu login"}
                </h5>
                <label className="form-label">
                      <i className="bi bi-whatsapp me-2"></i>Informe seu número de WhatsApp
                    </label>
                <input
                  type="tel"
                  name="telefone"
                  className="form-control mb-3"
                  placeholder="(xx) xxxxx-xxxx"
                  value={formData.telefone}
                  onChange={handleChange}
                  onBlur={!modoCadastroManual ? buscarCliente : undefined}
                  required
                />

                {!modoCadastroManual && (
                  <div className="d-flex justify-content-center mb-3">
                    <button
                      type="button"
                      className="btn btn-primary"
                      onClick={buscarCliente}
                      disabled={formData.telefone.replace(/\D/g, '').length < 10}
                    >
                      <i className="bi bi-box-arrow-in-right me-2"></i>Fazer login
                    </button>
                  </div>
                )}

                {mostrarCadastro && modoCadastroManual && (
                  <>
                    <label className="form-label">
                      <i className="bi bi-person me-2"></i>Nome completo
                    </label>
                    <input
                    type="text"
                    name="nome"
                    className="form-control mb-3"
                    placeholder="Digite seu nome completo"
                    value={formData.nome}
                    maxLength={100}
                    onChange={(e) => {
                      const valor = e.target.value;
                      if (/^[a-zA-ZÀ-ÿ\s]*$/.test(valor)) {
                        setFormData({ ...formData, nome: valor });
                      }
                    }}
                    required
                  />

                    <div className="d-flex justify-content-center">
                      <button
                        type="button"
                        className="btn btn-primary"
                        onClick={async () => {
                          try {
                            const res = await axios.post(`${API_BASE_URL}cadastrar_cliente.php`, {
                              nome: formData.nome,
                              telefone: formData.telefone,
                            });

                            if (res.data && res.data.id) {
                              setMensagem(null);
                              setClienteEncontrado(res.data);
                              setEtapa(2);
                              setModoCadastroManual(false);
                              setMostrarCadastro(false);
                            } else {
                              setMensagem({
                                texto: "Erro ao cadastrar cliente.",
                                tipo: "danger"
                              });
                            }

                          } catch (error) {
                            if (error.response?.status === 409) {
                              // Telefone duplicado
                              setMensagem({
                                texto: "Este número de telefone já está cadastrado. Por favor, escolha outro número.",
                                tipo: "warning"
                              });
                              setModoCadastroManual(true);
                              setMostrarCadastro(true);

                            } else if (error.response?.status === 422) {
                              // Nome inválido ou telefone faltando
                              setMensagem({
                                texto: error.response.data?.error || "Dados inválidos.",
                                tipo: "warning"
                              });

                            } else {
                              // Outro erro
                              setMensagem({ texto: "Erro ao cadastrar cliente.", tipo: "danger" });
                            }
                          }
                        }}
                        disabled={!formData.nome || !formData.telefone}
                      >
                        <i className="bi bi-person-plus me-2"></i>Avançar
                        <i className="bi bi-arrow-right ms-2"></i>
                      </button>
                    </div>

                  </>
                )}
              </div>
            )}

              {etapa === 2 && (
                <div className="bg-light p-4 rounded border border-success-subtle">
                  <h5 className="text-success mb-3">Escolha o atendente, serviço, data e horário</h5>
                  <div className="form-text text-danger">* Atendente</div>
                  <select name="atendente" className="form-select mb-3" value={formData.atendente} onChange={handleChange} required>
                    <option value="">Selecione um atendente</option>
                    {atendentes.map(a => (
                      <option key={a.usuario_id} value={a.usuario_id}>{a.nome_atendente}</option>
                    ))}
                  </select>

                  <div className="form-text text-danger">* Serviço</div>
                  <select name="servico" className="form-select mb-3" value={formData.servico} onChange={handleChange} required>
                    <option value="">Selecione um serviço</option>
                    {servicos.map(s => (
                      <option key={s.id} value={s.id}>{s.nome} - R$ {parseFloat(s.valor).toFixed(2)}</option>
                    ))}
                  </select>

                  <div className="form-text text-danger">* Data</div>
                  <input type="date" name="data" className="form-control mb-3" value={formData.data} onChange={handleChange} required />

                  <div className="form-text text-danger">* Horário</div>
                  <select name="horario" className="form-select mb-3" value={formData.horario} onChange={handleChange} required>
                    <option value="">Selecione um horário</option>
                    {horarios.map(h => (
                      <option key={h} value={h}>{h}</option>
                    ))}
                  </select>

                  <div className="d-flex justify-content-center">
                    <button type="button" className="btn btn-success" disabled={!formData.servico || !formData.data || !formData.horario || !formData.atendente} onClick={avancarEtapa}>
                      Avançar <i className="bi bi-arrow-right"></i>
                    </button>
                  </div>
                </div>
              )}

              {etapa === 3 && (
                <div className="bg-light p-4 rounded border border-info-subtle">
                  <h5 className="text-success mb-3">Observações e Verificação antispam</h5>
                  <textarea
                    name="observacao"
                    className="form-control mb-3"
                    placeholder="Alergias, preferências de cores de esmaltes, etc ..."
                    rows="3"
                    value={formData.observacao}
                    onChange={handleChange}
                  />

                  <div className="mb-3">
                    <div className="form-text text-danger">* Verificação antispam</div>
                    <label className="form-label fw-bold">{pergunta}</label>
                    <input
                      type="number"
                      className="form-control"
                      value={respostaUsuario}
                      onChange={(e) => setRespostaUsuario(e.target.value)}
                      placeholder="Digite a resposta"
                    />
                  </div>

                  <div className="d-flex justify-content-center">
                    <button
                      type="button"
                      className="btn btn-success d-flex align-items-center gap-2"
                      onClick={() => {
                        if (parseInt(respostaUsuario, 10) === respostaCorreta) {
                        setMensagem(null);
                        avancarEtapa();
                      } else {
                        setMensagem({ texto: "Resposta incorreta. Tente novamente.", tipo: "danger" });
                      }
                      }}
                    >
                      Avançar <i className="bi bi-arrow-right"></i>
                    </button>
                  </div>
                </div>
              )}

              {etapa === 4 && (
                <div className="bg-light p-4 rounded border border-warning-subtle">
                  <h5 className="text-primary mb-3">
                    <i className="bi bi-eye-fill me-2"></i>
                    Revisão dos dados
                  </h5>

                  <ul className="list-group mb-3">
                    <li className="list-group-item">
                      <strong>Nome:</strong> {formData.nome}
                    </li>
                    <li className="list-group-item">
                      <strong>Telefone:</strong> {formData.telefone}
                    </li>
                    <li className="list-group-item">
                      <strong>Serviço:</strong> {servicos.find(s => s.id == formData.servico)?.nome}
                    </li>
                    <li className="list-group-item">
                      <strong>Valor:</strong> R$ {
                        parseFloat(servicos.find(s => s.id == formData.servico)?.valor || 0).toFixed(2)
                      }
                    </li>
                    <li className="list-group-item">
                      <strong>Atendente:</strong> {atendentes.find(a => a.usuario_id == formData.atendente)?.nome_atendente}
                    </li>
                    <li className="list-group-item">
                      <strong>Data:</strong> {formatarData(formData.data)}
                    </li>
                    <li className="list-group-item">
                      <strong>Horário:</strong> {formData.horario}
                    </li>
                    <li className="list-group-item">
                      <strong>Observações:</strong> {formData.observacao || "Nenhuma"}
                    </li>
                  </ul>

                  {/* Botão principal destacado em cima */}
                  <div className="text-center mb-4">
                    <button
                      type="submit"
                      className="btn btn-success fw-bold d-flex align-items-center gap-2 mx-auto"
                    >
                      <i className="bi bi-whatsapp fs-5"></i> Confirmar e Agendar
                    </button>
                  </div>
                </div>
              )}
          </form>
          )}

        </div>
      </div>
    </>
  );
};

export default Agendamentos;

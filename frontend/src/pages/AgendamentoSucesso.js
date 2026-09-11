import React, { useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";

const formatarData = (dataStr) => {
  if (!dataStr) return '';
  const partes = dataStr.split('-');  // ['2025', '05', '29']
  return `${partes[2]}/${partes[1]}/${partes[0]}`;  // '29-05-2025'
};


const AgendamentoSucesso = () => {
  const { state } = useLocation();
  const navigate = useNavigate();

  useEffect(() => {
  if (state?.abrirWhatsApp && state?.telefoneEmpresa) {
    const observacaoFormatada = state.observacao?.trim() ? `- Observações: ${state.observacao}` : '- Observações: Nenhuma';

    const mensagem = `*Olá! Segue abaixo meu Novo Agendamento:*\n
- Cliente: ${state.nome}
- Telefone: ${state.telefone}
- Serviço: ${state.servico}
- Data: ${formatarData(state.data)}
- Horário: ${state.horario}
- Atendente: ${state.atendente}
- Valor: R$ ${state.valor}
${observacaoFormatada}`;

    const url = `https://wa.me/55${state.telefoneEmpresa}?text=${encodeURIComponent(mensagem)}`;
    window.open(url, "_blank");
  }
}, [state]);


  if (!state) {
    return (
      <div className="container mt-5">
        <div className="alert alert-warning">
          Nenhum dado de agendamento encontrado. Retorne para a página inicial.
        </div>
        <button className="btn btn-primary" onClick={() => navigate("/")}>Voltar</button>
      </div>
    );
  }

  return (
    <div className="container mt-5">
      <div className="card shadow p-4">
        <h5 className="text-success mb-4">
          <i className="bi bi-check-circle me-2"></i>
          Agendamento realizado com sucesso!
        </h5>
        <ul className="list-group mb-3">
        <li className="list-group-item"><strong>Nome:</strong> {state.nome}</li>
        <li className="list-group-item"><strong>Telefone:</strong> {state.telefone}</li>
        <li className="list-group-item"><strong>Serviço:</strong> {state.servico}</li>
        <li className="list-group-item"><strong>Valor:</strong> {state.valor}</li>
        <li className="list-group-item"><strong>Data:</strong> {formatarData(state.data)}</li>
        <li className="list-group-item"><strong>Horário:</strong> {state.horario}</li>
        <li className="list-group-item"><strong>Atendente:</strong> {state.atendente}</li>
        <li className="list-group-item">
      <strong>Observações:</strong> {state.observacao?.trim() ? state.observacao : "Nenhuma"}
      </li>

      </ul>
        <button className="btn btn-primary" onClick={() => navigate("/agendamentos")}>
          <i className="bi bi-plus-circle me-2"></i> Criar novo agendamento
        </button>
      </div>
    </div>
  );
};

export default AgendamentoSucesso;

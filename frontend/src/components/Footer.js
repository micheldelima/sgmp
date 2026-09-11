import React, { useEffect, useState } from "react";
import axios from "axios";
import API_BASE_URL from "../config";

const Footer = () => {
  const [empresa, setEmpresa] = useState({});
  const [horarios, setHorarios] = useState([]);
  const [servicos, setServicos] = useState([]);

  const anoAtual = new Date().getFullYear();

  const formatarTelefone = (numero) => {
    let numeroLimpo = numero.replace(/\D/g, ''); // Remove tudo que não for número

    // Se o número já começar com 55 (Brasil), retorna ele
    if (numeroLimpo.startsWith("55")) {
        return numeroLimpo;
    }

    // Caso contrário, adiciona +55 no início
    return "55" + numeroLimpo;
};

  const linksRapidos = [
    { nome: "Início", url: "/" },
    { nome: "Serviços", url: "/servicos" },
    { nome: "Agendamentos", url: "/agendamentos" },
    { nome: "Portfólio", url: "/portfolio" },
    { nome: "Contato", url: "/contato" }
    ];

  // Buscar informações da empresa, horários de atendimento e serviços ao carregar a página
  useEffect(() => {
    axios.get(`${API_BASE_URL}get_empresa_info.php`)
      .then(response => {
        if (response.data.empresa) {
          setEmpresa(response.data.empresa);
        }
        if (response.data.horarios) {
          setHorarios(response.data.horarios);
        }
        if (response.data.servicos) {
          setServicos(response.data.servicos.slice(0, 5)); // Exibir até 5 serviços
        }
      })
      .catch(error => {
        console.error("Erro ao carregar informações:", error);
      });
  }, []);

  return (
    <footer className="text-white py-4 mt-5" style={{ backgroundColor: "#d63384" }}>
      <div className="container">
        <div className="row gy-4">

          {/* Sobre a empresa */}
          <div className="col-lg-4">
            <h4 className="fw-bold">{empresa.razao_social || "Nome da Empresa"}</h4>
            <p>{empresa.descricao || "Carregando informações..."}</p>
            
            {/* Horários de Atendimento */}
            <p>
              <strong>Horário de Atendimento:</strong> <br />
              {horarios.length > 0 ? (
                horarios.map((horario, index) => (
                  <span key={index}>
                    {horario.dia_inicio} a {horario.dia_fim}:{" "}
                    {horario.horario_abertura.substring(0, 5)} - {horario.horario_fechamento.substring(0, 5)}
                    <br />
                    {horario.feriado && (
                      <small className="d-block mt-1">
                        <i className="bi bi-exclamation-triangle-fill"></i> {horario.feriado}
                      </small>
                    )}
                  </span>
                ))
              ) : (
                "Horários não cadastrados."
              )}
            </p>

            {/* Redes Sociais */}
            <div className="d-flex gap-3 mt-3">
              {empresa.rede_social && (
                <a href={empresa.rede_social} target="_blank" rel="noopener noreferrer" className="text-white fs-4">
                  <i className="bi bi-instagram"></i>
                </a>
              )}
              {empresa.telefone && (
                <a href={`https://wa.me/${formatarTelefone(empresa.telefone)}`} 
              className="text-white text-decoration-none"
              target="_blank" 
              rel="noopener noreferrer" className="text-white fs-4">
                  <i className="bi bi-whatsapp"></i>
                </a>
              )}
            </div>
          </div>

          {/* Links Rápidos */}
          <div className="col-lg-2 col-6">
            <h4 className="fw-bold">Links Rápidos</h4>
            <ul className="list-unstyled">
              {linksRapidos.map((link, index) => (
                <li key={index}>
                  <a href={link.url} className="text-white text-decoration-underline">
                    {link.nome}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Serviços Dinâmicos */}
          <div className="col-lg-2 col-6">
            <h4 className="fw-bold">Nossos Serviços</h4>
            <ul className="list-unstyled">
              {servicos.length > 0 ? (
                servicos.map(servico => (
                  <li key={servico.id}>{servico.nome}</li>
                ))
              ) : (
                <li>Carregando...</li>
              )}
              <li><strong>E muito mais...</strong></li>
            </ul>
          </div>

          {/*  Informações de Contato */}
          <div className="col-lg-4">
            <h4 className="fw-bold">Contato</h4>
            <p><i className="bi bi-geo-alt-fill"></i> {empresa.endereco || "Endereço não informado"}</p>
            {empresa.telefone && (
            <p>
             <i className="bi bi-whatsapp"></i> <a 
              href={`https://wa.me/${formatarTelefone(empresa.telefone)}`} 
              className="text-white text-decoration-none"
              target="_blank" 
              rel="noopener noreferrer"
            >
               {empresa.telefone}
            </a>
          </p>
            )}
            {empresa.email && (
              <p><i className="bi bi-envelope"></i> <a href={`mailto:${empresa.email}`} className="text-white text-decoration-none">{empresa.email}</a></p>
            )}
            <p><i className="bi bi-briefcase-fill"></i> CNPJ: {empresa.cnpj || "Não informado"}</p>
          </div>
        </div>
      </div>

      {/* Direitos autorais */}
      <div className="container text-center mt-4">
        <p className="mb-1">© {anoAtual} <strong>{empresa.razao_social || "Taty Cardoso Studio Nails"}</strong> - Todos os direitos reservados.</p>
      </div>
    </footer>
  );
};

export default Footer;

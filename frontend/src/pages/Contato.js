import React, { useEffect, useState } from "react";
import { Helmet } from "react-helmet-async";
import AOS from "aos";
import axios from "axios";
import "aos/dist/aos.css";
import API_BASE_URL from "../config";

const Contato = () => {
  const [empresa, setEmpresa] = useState({});
  const [horarios, setHorarios] = useState([]);

  // carregar dados da empresa e horários
  useEffect(() => {
    AOS.init({ duration: 1000 });

    axios.get(`${API_BASE_URL}get_empresa_info.php`)
      .then(response => {
        if (response.data.empresa) {
          setEmpresa(response.data.empresa);
        }
        if (response.data.horarios) {
          setHorarios(response.data.horarios);
        }
      })
      .catch(error => {
        console.error("Erro ao carregar informações da empresa:", error);
      });
  }, []);

  return (
    <>
      {/* SEO - Metatags Dinâmicas */}
      <Helmet>
        <title>Contato | {empresa.razao_social || "Taty Cardoso Studio Nails"}</title>
        <meta name="description" content="Entre em contato para dúvidas ou informações. Atendimento pelo WhatsApp e e-mail. Agendamentos apenas pela página de Agendamentos." />
        <meta name="keywords" content="contato, unhas, manicure, nail design, WhatsApp, email, localização" />
        <meta name="author" content={empresa.razao_social || "Taty Cardoso Studio Nails"} />
        <meta name="robots" content="index, follow" />
      </Helmet>

      <div className="container mt-5">
        <h1 className="text-center fw-bold" data-aos="fade-up">
          <i className="bi bi-envelope-fill text-danger"></i> Contato
        </h1>

        {/* Horários de atendimento */}
        <div className="text-center mt-3" data-aos="fade-up">
          <i className="bi bi-clock-history text-warning fs-3"></i>
          <p className="mt-2 mb-0 fw-bold">Horários de Atendimento</p>
          <small className="text-muted">
            {horarios && horarios.length > 0 ? (
              horarios.map((h, idx) => {
                const abertura = h.horario_abertura ? h.horario_abertura.substring(0, 2) + "h" : "";
                const fechamento = h.horario_fechamento ? h.horario_fechamento.substring(0, 2) + "h" : "";

                return (
                  <span key={idx} className="d-block">
                    <strong>{h.dia_inicio} a {h.dia_fim}:</strong> {abertura} às {fechamento}  
                    <br />
                    <em className="text-secondary">
                      {h.feriado === "Trabalhamos no feriado"
                        ? "Atendemos também em feriados"
                        : "Não atendemos em feriados"}
                    </em>
                  </span>
                );
              })
            ) : (
              "Horários não cadastrados"
            )}
          </small>
        </div>

        {/*  Linha de Separação */}
        <hr className="my-4" />

        <p className="text-center lead text-muted" data-aos="fade-up">
          Para dúvidas ou informações, entre em contato pelos canais abaixo. <br />
        </p>

        {/*  Linha de Separação */}
        <hr className="my-4" />

        <div className="row text-center">
          {/* WhatsApp */}
          <div className="col-md-4" data-aos="fade-up">
            <i className="bi bi-whatsapp text-success display-3"></i>
            <h4 className="fw-bold mt-2">WhatsApp</h4>
            <p>
              Envie uma mensagem para nós! <br />
              <a
                href={`https://wa.me/55${empresa.telefone?.replace(/\D/g, "")}`}
                target="_blank"
                rel="noreferrer"
                className="btn btn-success mt-2"
              >
                <i className="bi bi-whatsapp"></i> Fale Conosco
              </a>
            </p>
          </div>

          {/*  E-mail */}
          <div className="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <i className="bi bi-envelope-fill text-primary display-3"></i>
            <h4 className="fw-bold mt-2">E-mail</h4>
            <p>
              Preferência por e-mail? <br />
              <a href={`mailto:${empresa.email}`} className="btn btn-primary mt-2">
                <i className="bi bi-envelope"></i> Enviar E-mail
              </a>
            </p>
          </div>

          {/* Localização */}
          <div className="col-md-4" data-aos="fade-up" data-aos-delay="400">
            <i className="bi bi-geo-alt-fill text-danger display-3"></i>
            <h4 className="fw-bold mt-2">Localização</h4>
            <p>
              {empresa.endereco || "Endereço não disponível"}
            </p>
          </div>
        </div>

        {/* Linha de Separação */}
        <hr className="my-4" />

        {/*  Mapa do Google */}
        <div className="text-center" data-aos="fade-up">
          <h4 className="fw-bold">Nosso Estúdio</h4>
          <p className="text-muted">Clique no mapa para abrir no Google Maps.</p>

          {empresa.endereco ? (
            <iframe
              title="Localização no Google Maps"
              width="100%"
              height="350"
              className="rounded shadow-lg"
              loading="lazy"
              allowFullScreen
              referrerPolicy="no-referrer-when-downgrade"
              src={`https://www.google.com/maps?q=${encodeURIComponent(empresa.endereco)}&output=embed`}
            />
          ) : (
            <p className="text-danger">Endereço não disponível no momento.</p>
          )}
        </div>
      </div>
    </>
  );
};

export default Contato;

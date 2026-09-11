import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet-async";
import AOS from "aos";
import "aos/dist/aos.css";
import axios from "axios";
import API_BASE_URL from "../config";

const Home = () => {
  const [empresa, setEmpresa] = useState({});
  const [logo, setLogo] = useState(null);
  const [horarios, setHorarios] = useState([]);

  // carregar dados da empresa e logo
  useEffect(() => {
  AOS.init({ duration: 1000, once: true });

  // Buscar informações da empresa
  axios
    .get(`${API_BASE_URL}get_empresa_info.php`)
    .then((response) => {
      if (response.data.empresa) {
        setEmpresa(response.data.empresa);
      }
      if (response.data.horarios) {
        setHorarios(response.data.horarios);  
      }
    })
    .catch((error) =>
      console.error("Erro ao carregar informações da empresa:", error)
    );

  // Buscar logo dinamicamente
  axios
    .get(`${API_BASE_URL}get_logo.php`)
    .then((response) => {
      if (response.data.logo) {
        setLogo(response.data.logo);
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar logo:", error);
    });
}, []);

  return (
    <>
      {/* SEO - Metatags Dinâmicas */}
      <Helmet>
        <title>Taty Cardoso Studio Nails | Alongamento de Unhas e Nail Design</title>
        <meta
          name="description"
          content="Transforme suas unhas com Alongamento de Gel, Fibra de Vidro, Blindagem, Esmaltação em Gel e muito mais! Agende seu horário no Taty Cardoso Studio Nails."
        />
        <meta
          name="keywords"
          content="manicure ponta grossa pr, unhas, alongamento de gel, manicure, nail design, esmaltação, blindagem, manutenção de unhas, spa dos pés, unhas decoradas"
        />
        <meta name="author" content="Taty Cardoso Studio Nails" />
        <meta name="robots" content="index, follow" />
      </Helmet>

      <div className="container mt-5">
        {/* Seção de Boas-Vindas */}
        <div className="container mt-5">
      {/* Seção de Boas-Vindas */}
      <section className="row align-items-center" data-aos="fade-up">
        
        {/* Logo à esquerda */}
        <div className="col-md-4 text-center">
          {logo ? (
            <img src={logo} alt="Logo do Estúdio" className="rounded-circle img-fluid shadow-sm" style={{ maxWidth: "300px" }} />
          ) : (
            <p className="text-muted">Nenhuma logo cadastrada</p>
          )}
        </div>

    {/* Texto à direita */}
    <div className="col-md-8">
      
      {/* HERO / CHAMADA PRINCIPAL */}
    <section className="py-5 text-center text-white" style={{ background: "linear-gradient(135deg,#d63384,#9b1d5a)" }}>
      <div className="container py-4">
        <h1 className="fw-bold mb-2"><i className="bi bi-stars me-2"></i>Taty Cardoso Studio Nails</h1>
        <p className="lead mb-4">Unhas impecáveis com técnicas modernas e muito carinho ✨</p>

        {/* Botões responsivos */}
        <div className="row g-2 g-sm-3 justify-content-center">
          <div className="col-12 col-sm-6 col-md-3">
            <a href="/agendamentos" className="btn btn-light btn-lg w-100 btn-cta shadow-sm">
              <i className="bi bi-calendar-check me-2"></i> Agendar agora
            </a>
          </div>
          <div className="col-12 col-sm-6 col-md-3">
            <a href="/servicos" className="btn btn-outline-light btn-lg w-100 btn-cta">
              <i className="bi bi-scissors me-2"></i> Ver serviços
            </a>
          </div>
        </div>

        {/* Selo/mini-info dinâmico */}
        <div className="d-inline-flex align-items-center gap-2 bg-dark bg-opacity-25 rounded-pill px-3 py-2 mt-4 flex-wrap">
          <small className="fw-semibold">
            Atendimento com hora marcada • {" "}
            {/* Horários de Atendimento */}
              {horarios.length > 0 ? (
                horarios.map((horario, index) => (
                  <span key={index}>
                    {horario.dia_inicio} a {horario.dia_fim}:{" "}
                    {horario.horario_abertura.substring(0, 5)} - {horario.horario_fechamento.substring(0, 5)}
                    <br />
                    {horario.feriado && (
                      <p>
                        {horario.feriado}
                      </p>
                    )}
                  </span>
                ))
              ) : (
                "Horários não cadastrados."
              )}
          </small>
        </div>
        </div>
    </section>

      <blockquote className="blockquote fst-italic text-muted">
        "No nosso estúdio, prezamos pela qualidade, segurança e higiene, utilizando produtos de alto padrão e técnicas inovadoras para garantir resultados incríveis."
      </blockquote>
      <blockquote className="blockquote fst-italic text-muted">
        "Trabalhamos com as <strong>melhores marcas</strong> do mercado e seguimos <strong>rigorosos padrões de higiene</strong>, 
        garantindo um atendimento profissional e seguro. Cada detalhe do nosso serviço é pensado para 
        realçar sua <strong>beleza natural</strong> e elevar sua autoestima."
      </blockquote>
      <blockquote className="blockquote fst-italic text-muted">
        "Além de unhas impecáveis, queremos que você tenha um <strong>momento de autocuidado</strong>, onde possa relaxar 
        e se sentir especial. Agende seu horário e permita-se viver essa experiência única!"
      </blockquote>

    </div>

  </section>
</div>

        {/* Linha de Separação */}
        <hr className="my-4"/>

        {/* Seção Sobre */}
        <section className="py-5">
          <h1 className="text-center fw-bold mb-4">Sobre Nós</h1>
          <div className="row align-items-center">
            <div className="col-xl-5 text-center text-xl-start" data-aos="fade-up">
              <h3 className="fw-bold">Conheça um pouco de mim</h3>
              <p>{empresa.descricao || "Carregando descrição..."}</p>
              <Link to="/portfolio" className="btn btn-outline-primary btn-lg">
                <i className="bi bi-chevron-right"></i> Conheça meus trabalhos
              </Link>
            </div>

            <div className="col-xl-7 mt-4 mt-xl-0">
              <div className="row text-center">
                {[
                  { icon: "bi-heart-fill text-danger", title: "Paixão pelo que faço", text: "Dedicação e amor pela profissão, proporcionando cuidado e beleza para cada cliente." },
                  { icon: "bi-stars text-warning", title: "Autoestima e Bem-estar", text: "Transformo unhas e momentos, ajudando mulheres a se sentirem mais confiantes e poderosas!" },
                  { icon: "bi-mortarboard-fill text-info", title: "Aprimoramento Profissional", text: "Sempre buscando aprendizado contínuo e novas técnicas para oferecer um serviço atualizado e de qualidade." },
                  { icon: "bi-award-fill text-success", title: "Compromisso com a Excelência", text: "Sempre em busca da melhor qualidade, utilizando técnicas modernas e produtos de alto padrão." }
                ].map((item, index) => (
                  <div key={index} className="col-md-6 mt-4" data-aos="fade-up" data-aos-delay={index * 200}>
                    <i className={`bi ${item.icon} display-3`}></i>
                    <h4 className="fw-bold mt-2">{item.title}</h4>
                    <p>{item.text}</p>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </section>

        {/* Linha de Separação */}
        <hr className="my-4"/>

        {/* Perguntas Frequentes */}
        <section className="text-center py-5" data-aos="fade-down">
          <h1 className="fw-bold">FAQ - Perguntas Frequentes</h1>
          <div className="accordion mt-4" id="faqAccordion">
            {[
              { pergunta: "Quais as formas de pagamento?", resposta: "Dinheiro, Pix, Cartão." },
              { pergunta: "Qual o horário de funcionamento?", resposta: "O Horário de atendimento está no rodapé do site ou na seção de agendamentos." },
              { 
                pergunta: "Quanto tempo dura cada procedimento?", 
                resposta: "A duração dos procedimentos estão na seção de serviços."
              },
              { pergunta: "Qual o endereço do estúdio?", resposta: "O endereço esta no rodapé do site e na seção contatos" },
              { pergunta: "Quais os preços dos procedimentos?", resposta: "Os preços estão disponíveis na seção de serviços." }
            ].map((item, index) => (
              <div key={index} className="accordion-item" data-aos="fade-up" data-aos-delay={index * 200}>
                <h2 className="accordion-header">
                  <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target={`#collapse${index}`}>
                    {item.pergunta}
                  </button>
                </h2>
                <div id={`collapse${index}`} className="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div className="accordion-body">{item.resposta}</div>
                </div>
              </div>
            ))}
          </div>
        </section>
      </div>
    </>
  );
};

export default Home;

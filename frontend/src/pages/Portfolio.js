import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import axios from "axios";
import "aos/dist/aos.css";
import AOS from "aos";
import "../App.css";
import API_BASE_URL from "../config"; 
import { IMAGE_BASE_URL } from "../config";

const Portfolio = () => {
  const [portfolio, setPortfolio] = useState([]); 
  const [erro, setErro] = useState("");
  const [imagemModal, setImagemModal] = useState(null);
  const [tituloModal, setTituloModal] = useState("");

  useEffect(() => {
    AOS.init({ duration: 1000 });

    const fetchPortfolio = async () => {
      try {
        const response = await axios.get(`${API_BASE_URL}admin_portfolio_all.php`);
        if (Array.isArray(response.data.portfolio)) {
          setPortfolio(response.data.portfolio);
        } else {
          setErro("Erro ao carregar o portfólio. Resposta inválida da API.");
        }
      } catch (error) {
        console.error("Erro ao buscar o portfólio:", error);
        setErro("Erro ao conectar à API.");
      }
    };

    fetchPortfolio();
  }, []);

  return (
    <>
      <Helmet>
        <title>Portfólio | Taty Cardoso Studio Nails</title>
        <meta name="description" content="Confira nosso portfólio de trabalhos realizados. Veja exemplos de Alongamento de Unhas, Blindagem, Esmaltação em Gel e muito mais!" />
        <meta name="keywords" content="portfólio, alongamento de unhas, blindagem, esmaltação em gel, nail design, manicure" />
        <meta name="author" content="Taty Cardoso Studio Nails" />
        <meta name="robots" content="index, follow" />
      </Helmet>

      <div className="container mt-5">
        <h2 className="text-center fw-bold" data-aos="fade-down">
          <i className="bi bi-images text-danger"></i> Nosso Portfólio
        </h2>
        <p className="text-center text-muted" data-aos="fade-up">
          Confira alguns dos nossos trabalhos realizados com técnicas modernas e produtos de alta qualidade.
        </p>
        <hr />

        {erro && <div className="alert alert-danger text-center">{erro}</div>}

        {portfolio.length === 0 && !erro ? (
          <div className="alert alert-warning text-center" data-aos="fade-up">
            Nenhum item cadastrado no portfólio.
          </div>
        ) : (
          <div className="row">
            {portfolio.map((item, index) => (
              <div key={item.id} className="col-12 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay={index * 150}>
                <div className="card shadow-sm border-0 h-100">
                  <div className="portfolio-img-container">
                    <img
                      src={`${IMAGE_BASE_URL}${item.imagem}`}
                      alt={item.titulo}
                      className="card-img-top portfolio-img"
                      style={{ height: "200px", objectFit: "cover", cursor: "pointer" }}
                      onClick={() => {
                        setImagemModal(`${IMAGE_BASE_URL}${item.imagem}`);
                        setTituloModal(item.titulo);
                      }}
                    />
                  </div>
                  <div className="card-body text-center">
                    <h5 className="card-title mt-2 fw-bold">{item.titulo}</h5>
                    <p className="card-text text-muted">{item.descricao}</p>
                    <p className="text-muted small">
                      <i className="bi bi-calendar-check"></i> Publicado em: {item.data_criacao}
                    </p>
                    <Link to="/agendamentos" className="btn btn-primary mt-3">
                      <i className="bi bi-calendar-check"></i> Agendar agora!
                    </Link>
                  </div>
                </div>
              </div>
            ))}
          </div>

        )}

        {imagemModal && (
          <div className="modal-overlay" onClick={() => setImagemModal(null)}>
            <div className="modal-content" onClick={e => e.stopPropagation()}>
              <button className="btn-fechar" onClick={() => setImagemModal(null)}>
                &times;
              </button>
              <img src={imagemModal} alt={tituloModal} className="modal-img" />
              <p className="modal-title">{tituloModal}</p>
            </div>
          </div>
        )}
      </div>
    </>
  );
};

export default Portfolio;

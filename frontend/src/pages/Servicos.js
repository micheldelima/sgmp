import React, { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import axios from "axios";
import "aos/dist/aos.css";
import AOS from "aos";
import API_BASE_URL from "../config";
import { Modal } from "bootstrap";

const Servicos = () => {
  const [servicos, setServicos] = useState([]);
  const [erro, setErro] = useState("");
  const [busca, setBusca] = useState("");
  const [descModal, setDescModal] = useState({ titulo: "", texto: "" });
  const [ordenacao, setOrdenacao] = useState(""); // '', 'valor', 'duracao', 'agendados', 'lancamentos'
  const [filtroLabel, setFiltroLabel] = useState("Ordenar/Filtrar");
  // Conjuntos p/ badges
  const [topAgendadosIds, setTopAgendadosIds] = useState(new Set());
  const [lancamentosIds, setLancamentosIds] = useState(new Set());

  const [openFiltros, setOpenFiltros] = useState(false);
const closeFiltros = () => setOpenFiltros(false);

useEffect(() => {
  const onDocClick = (e) => {
    const menu = document.getElementById('menu-filtros');
    const btn  = document.getElementById('btn-filtros');
    if (!menu || !btn) return;
    if (!menu.contains(e.target) && !btn.contains(e.target)) {
      setOpenFiltros(false);
    }
  };
  const onKey = (e) => { if (e.key === 'Escape') setOpenFiltros(false); };

  document.addEventListener('click', onDocClick);
  document.addEventListener('keydown', onKey);
  return () => {
    document.removeEventListener('click', onDocClick);
    document.removeEventListener('keydown', onKey);
  };
}, []);


  useEffect(() => {
    AOS.init({ duration: 1000 });
    (async () => {
      await carregarServicos();           // padrão A–Z
      await carregarBadgesAuxiliares();   // popula badges top3 e lançamentos do mês
    })();
  }, []);

  // debounce da busca
  const [buscaDebounced, setBuscaDebounced] = useState("");
  useEffect(() => {
    const t = setTimeout(() => setBuscaDebounced(busca.trim().toLowerCase()), 250);
    return () => clearTimeout(t);
  }, [busca]);

  // Carregar serviços (com filtro opcional)
const carregarServicos = async (filtro = "", periodo = "") => {
  try {
    const qs = new URLSearchParams();
    if (filtro) qs.set("filtro", filtro);
    if (periodo) qs.set("periodo", periodo);

    const url = `${API_BASE_URL}admin_servicos_all.php${qs.toString() ? "?" + qs.toString() : ""}`;
    const { data } = await axios.get(url);

    if (Array.isArray(data.servicos)) {
      setServicos(data.servicos);
      setErro("");

      if (filtro === "mais_agendados") {
        // Marca todos que têm agendamento > 0
        const ids = data.servicos
          .filter(s => Number(s.total_agendamentos || 0) > 0)
          .map(s => Number(s.id));
        setTopAgendadosIds(new Set(ids));
      } else {
        // No carregamento normal: só os Top 3
        const temAgCol = data.servicos.some(s => typeof s.total_agendamentos !== "undefined");
        if (temAgCol) {
          const top3 = [...data.servicos]
            .sort((a, b) => (Number(b.total_agendamentos || 0) - Number(a.total_agendamentos || 0)))
            .slice(0, 3)
            .map(s => Number(s.id));
          setTopAgendadosIds(new Set(top3));
        }
  }

    } else {
      setErro("Erro ao carregar serviços.");
    }
  } catch (e) {
    console.error(e);
    setErro("Erro ao conectar à API.");
  }
};


  // Busca auxiliar p/ badges (Top 3 do mês + Lançamentos do mês)
  const carregarBadgesAuxiliares = async () => {
    try {
      // Top agendados (mês-calendário)
      const topRes = await axios.get(`${API_BASE_URL}admin_servicos_all.php?filtro=mais_agendados&periodo=mensal`);
      if (Array.isArray(topRes.data.servicos)) {
        const top3 = topRes.data.servicos.slice(0, 3).map(s => Number(s.id)).filter(Boolean);
        setTopAgendadosIds(new Set(top3));
      }

      // Lançamentos (mês)
      const lancRes = await axios.get(`${API_BASE_URL}admin_servicos_all.php?filtro=lancamentos&periodo=mensal`);
      if (Array.isArray(lancRes.data.servicos)) {
        const ids = lancRes.data.servicos.map(s => Number(s.id)).filter(Boolean);
        setLancamentosIds(new Set(ids));
      }
    } catch (e) {
      console.warn("Não foi possível carregar badges auxiliares:", e?.message || e);
    }
  };

  // Filtra por nome e ordena localmente (valor/duração)
 const servicosFiltrados = useMemo(() => {
  let base = buscaDebounced
    ? servicos.filter(s => (s.nome || "").toLowerCase().includes(buscaDebounced))
    : [...servicos];

  if (ordenacao === "valor") {
    base.sort((a, b) => Number(a.valor) - Number(b.valor));
  } else if (ordenacao === "duracao") {
    base.sort((a, b) => Number(a.duracao) - Number(b.duracao));
  } else if (ordenacao === "agendados") {
    base.sort((a, b) => (Number(b.total_agendamentos || 0) - Number(a.total_agendamentos || 0)));
  } else if (ordenacao === "az") {
    base.sort((a, b) => (a.nome || "").localeCompare(b.nome || "", "pt", { sensitivity: "base" }));
  }
  return base;
}, [servicos, buscaDebounced, ordenacao]);

  // Select de ordenação/filtros
  // sempre MENSAL
const onChangeOrdenacao = (opt) => {
  setOrdenacao(opt);
  setTopAgendadosIds(new Set());
  setLancamentosIds(new Set());

  const mesAtual = new Date().toISOString().slice(0,7);

  if (opt === "agendados") {
    return carregarServicos("mais_agendados", mesAtual);
  }
  if (opt === "lancamentos") {
    return carregarServicos("lancamentos", mesAtual);
  }
  return carregarServicos();
};

  function abrirDescricao(s) {
    setDescModal({ titulo: s.nome, texto: s.descricao || "Sem descrição." });
    const modalEl = document.getElementById("modalDescricaoServico");
    if (modalEl) {
      const modal = Modal.getOrCreateInstance(modalEl);
      modal.show();
    }
  }

  // Helpers de badges
  const isLancamento = (s) => {
    if (lancamentosIds.has(Number(s.id))) return true;
    if (s.created_at) {
      const d = new Date(s.created_at);
      const diff = (Date.now() - d.getTime()) / (1000 * 60 * 60 * 24);
      return diff <= 31;
    }
    return false;
  };
  const isTopAgendado = (s) => topAgendadosIds.has(Number(s.id));

  return (
    <>
      <Helmet>
        <title>Nossos Serviços | Taty Cardoso Studio Nails</title>
        <meta name="description" content="Conheça nossos serviços e agende seu horário agora mesmo!" />
      </Helmet>

      <div className="container mt-5">
        <h2 className="text-center fw-bold" data-aos="fade-down">
          <i className="bi bi-scissors text-danger"></i> Nossos Serviços
        </h2>
        <p className="text-center text-muted" data-aos="fade-up">
          Trabalhamos com técnicas modernas e produtos de alta qualidade.
        </p>
        <hr />

       {/* Barra: busca à esquerda, filtros à direita */}
        <div className="row align-items-center g-2 mb-3">
          {/* Busca (esquerda) */}
          <div className="col">
            <div className="position-relative">
              <input
                type="text"
                className="form-control ps-5"
                placeholder="Buscar serviço"
                value={busca}
                onChange={(e) => setBusca(e.target.value)}
              />
              <i
                className="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-dark"
                style={{ zIndex: 5, pointerEvents: "none" }}
              />
            </div>
          </div>

          {/* Filtros (direita) */}
          <div className="col-auto d-flex justify-content-md-end">
          <div className={`dropdown ${openFiltros ? 'show' : ''}`}>
            <button
              id="btn-filtros"
              type="button"
              className="btn btn-outline-secondary dropdown-toggle"
              aria-expanded={openFiltros}
              onClick={() => setOpenFiltros(o => !o)}
            >
              <i className="bi bi-funnel me-2"></i>{filtroLabel}
            </button>

            <ul
              id="menu-filtros"
              className={`dropdown-menu dropdown-menu-end ${openFiltros ? 'show' : ''}`}
              style={{ zIndex: 2000 }}
            >
            <li>
              <button className="dropdown-item" onClick={async () => { 
                setFiltroLabel("A–Z"); 
                setOrdenacao("az"); 
                await carregarServicos(); // lista padrão pra ordenar local
              }}>
                <i className="bi bi-sort-alpha-down me-2"></i>A–Z
              </button>
            </li>
            
            <li><hr className="dropdown-divider" /></li>

              <li>
                <button className="dropdown-item" onClick={() => { setFiltroLabel("Menor valor"); onChangeOrdenacao("valor"); closeFiltros(); }}>
                  <i className="bi bi-currency-dollar me-2"></i>Menor valor
                </button>
              </li>
              <li>
                <button className="dropdown-item" onClick={() => { setFiltroLabel("Menor duração"); onChangeOrdenacao("duracao"); closeFiltros(); }}>
                  <i className="bi bi-clock me-2"></i>Menor duração
                </button>
              </li>
              <li><hr className="dropdown-divider" /></li>
              <li>
                <button className="dropdown-item" onClick={() => { setFiltroLabel("Mais agendados (mês)"); onChangeOrdenacao("agendados"); closeFiltros(); }}>
                  <i className="bi bi-graph-up-arrow me-2"></i>Mais agendados (mês)
                </button>
              </li>
              <li>
                <button className="dropdown-item" onClick={() => { setFiltroLabel("Lançamentos (mês)"); onChangeOrdenacao("lancamentos"); closeFiltros(); }}>
                  <i className="bi bi-fire me-2"></i>Lançamentos (mês)
                </button>
              </li>
            </ul>
          </div>
        </div>
        </div>

        {erro && <div className="alert alert-danger text-center">{erro}</div>}

        {servicosFiltrados.length === 0 && !erro ? (
          <div className="alert alert-warning text-center" data-aos="fade-up">
            {buscaDebounced ? "Nenhum serviço encontrado para sua busca." : "Nenhum serviço cadastrado."}
          </div>
        ) : (
          <div className="row">
            {servicosFiltrados.map((servico, index) => (
              <div key={servico.id} className="col-md-4" data-aos="zoom-in" data-aos-delay={index * 120}>
                <div className="card mb-4 shadow-lg border-0 rounded h-100">
                  <div className="card-body d-flex flex-column text-center">

                    {/* Badges */}
                    <div className="d-flex justify-content-center gap-2 mb-2">
                      {isTopAgendado(servico) && (
                        <span className="badge bg-warning text-dark d-inline-flex align-items-center shadow-sm rounded-pill px-2 py-1">
                          <i className="bi bi-fire me-1"></i>
                          Mais agendado
                        </span>
                      )}

                      {isLancamento(servico) && (
                        <span className="badge bg-danger d-inline-flex align-items-center shadow-sm rounded-pill px-2 py-1">
                          <i className="bi bi-star-fill me-1"></i>
                          Lançamento
                        </span>
                      )}
                    </div>

                    <h4 className="card-title mt-2 fw-bold">{servico.nome}</h4>

                    {/* Preço / Duração */}
                    <div className="d-flex justify-content-center gap-3 mt-2">
                      <h3 className="text-danger fw-bold fs-5">
                        R$ {parseFloat(servico.valor).toFixed(2)}
                      </h3>
                      <h6 className="text-primary fw-bold fs-6">
                        <i className="bi bi-clock"></i> {servico.duracao} min
                      </h6>
                    </div>

                    {/* Ações */}
                    <div className="mt-auto d-flex justify-content-center gap-2">
                      <button
                        type="button"
                        className="btn btn-outline-secondary"
                        title="Ver descrição do serviço"
                        onClick={() => abrirDescricao(servico)}
                      >
                        <i className="bi bi-question-circle"></i>
                      </button>

                      <Link to="/agendamentos" className="btn btn-primary">
                        <i className="bi bi-calendar-check"></i> Agendar agora!
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>

        )}
      </div>

      {/* Modal de descrição */}
      <div className="modal fade" id="modalDescricaoServico" tabIndex="-1" aria-hidden="true">
        <div className="modal-dialog">
          <div className="modal-content">
            <div className="modal-header" style={{ backgroundColor: "#b1124a", color: "#fff" }}>
              <h5 className="modal-title">
                <i className="bi bi-question-circle me-2"></i> {descModal.titulo}
              </h5>
              <button type="button" className="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
            </div>
            <div className="modal-body">
              <p className="mb-0">{descModal.texto}</p>
            </div>
            <div className="modal-footer">
              <button className="btn btn-secondary" data-bs-dismiss="modal">
                <i className="bi bi-x-circle"></i> Fechar
              </button>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default Servicos;

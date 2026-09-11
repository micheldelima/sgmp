import React, { useEffect, useRef, useState } from "react";
import { NavLink } from "react-router-dom";

const Navbar = () => {
  const [isOpen, setIsOpen] = useState(false);
  const navRef = useRef(null);

  useEffect(() => {
    const applyNavHeight = () => {
      const h = Math.ceil(navRef.current?.getBoundingClientRect().height || 64);
      // pequeno “acolchoamento” para não cortar bordas arredondadas do primeiro card
      const safe = h;
      document.documentElement.style.setProperty("--nav-h", `${safe}px`);
    };
    applyNavHeight();
    window.addEventListener("resize", applyNavHeight);
    return () => window.removeEventListener("resize", applyNavHeight);
  }, []);

  const toggleNav = () => setIsOpen(v => !v);
  const closeNav  = () => setIsOpen(false);

  return (
    <nav
      ref={navRef}
      className="navbar navbar-expand-lg navbar-dark fixed-top"
      style={{ backgroundColor: "#d63384" }}
    >
      <div className="container">
        <NavLink className="navbar-brand" to="/" onClick={closeNav}>
          <i className="bi bi-stars"></i> Taty Cardoso Studio Nails
        </NavLink>

        <button
          className="navbar-toggler"
          type="button"
          aria-controls="navbarNav"
          aria-expanded={isOpen ? "true" : "false"}
          aria-label="Alternar navegação"
          onClick={toggleNav}
        >
          <i className="bi bi-list"></i> MENU
        </button>

        <div
          id="navbarNav"
          className={`collapse navbar-collapse mobile-overlay ${isOpen ? "show" : ""}`}
        >
          <ul className="navbar-nav ms-auto">
            <li className="nav-item">
              <NavLink className="nav-link" to="/" onClick={closeNav}>
                <i className="bi bi-house-door me-2 fs-5"></i> Início
              </NavLink>
            </li>
            <li className="nav-item">
              <NavLink className="nav-link" to="/servicos" onClick={closeNav}>
                <i className="bi bi-scissors me-2 fs-5"></i> Serviços
              </NavLink>
            </li>
            <li className="nav-item">
              <NavLink className="nav-link" to="/agendamentos" onClick={closeNav}>
                <i className="bi bi-calendar-check me-2 fs-5"></i> Agendamentos
              </NavLink>
            </li>
            <li className="nav-item">
              <NavLink to="/portfolio" className="nav-link" onClick={closeNav}>
                <i className="bi bi-images me-2 fs-5"></i> Portfólio
              </NavLink>
            </li>
            <li className="nav-item">
              <NavLink to="/contato" className="nav-link" onClick={closeNav}>
                <i className="bi bi-envelope me-2 fs-5"></i> Contato
              </NavLink>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;

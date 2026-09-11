import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Navbar from "./components/Navbar";
import Footer from "./components/Footer";
import Home from "./pages/Home";
import Servicos from "./pages/Servicos";
import Agendamentos from "./pages/Agendamentos";
import Contato from "./pages/Contato";
import Portfolio from "./pages/Portfolio";
import AgendamentoSucesso from './pages/AgendamentoSucesso';
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min";
import "bootstrap-icons/font/bootstrap-icons.css";


function App() {
  return (
    <Router>
      <Navbar />
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/servicos" element={<Servicos />} />
        <Route path="/agendamentos" element={<Agendamentos />} />
        <Route path="/agendamento-sucesso" element={<AgendamentoSucesso />} /> 
        <Route path="/portfolio" element={<Portfolio />} />
        <Route path="/contato" element={<Contato />} />
      </Routes>
      <Footer />
    </Router>
  );
}

export default App;

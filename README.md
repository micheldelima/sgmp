# 💅 SGMP — Sistema de Gestão para Salão/Studio de Beleza

Projeto desenvolvido para a disciplina de **Projeto de Software** do 3º ano — **UEPG**.

O **SGMP** é um sistema web completo para gestão de agendamentos em salões de beleza e estúdios de estética, oferecendo controle de serviços, profissionais, clientes, portfólio e horários de atendimento, com uma interface administrativa moderna.

---

## ✨ Funcionalidades

- 📅 **Agendamentos** — criação, confirmação e gerenciamento de horários, com notificações via WhatsApp
- 💼 **Serviços** — cadastro com nome, descrição, valor e duração
- 👥 **Clientes** — cadastro via telefone (com identificação automática)
- 💇 **Profissionais** — vinculados a usuários, com controle de disponibilidade
- 🚫 **Bloqueios de agenda** — impede agendamentos em datas/horários específicos
- 🕒 **Horários de atendimento** — configuração por dia da semana e feriados
- 🖼️ **Portfólio** — exibição de trabalhos com imagem e descrição
- 🏢 **Empresa** — dados institucionais (razão social, CNPJ, logo, redes sociais...)
- 🔐 **Autenticação** — níveis de acesso (Administrador / Funcionário)
- 📊 **Histórico de logins** — rastreio de IP e dispositivo

---

## 🛠️ Tecnologias Utilizadas

### Frontend
- **React** — biblioteca para interfaces SPA
- **JavaScript (ES6+)**
- **CSS3** — estilização customizada
- **HTML5**

### Backend
- **PHP 8** — API e regras de negócio
- **Apache** (via XAMPP) — servidor web
- **MariaDB / MySQL** — banco de dados relacional

### Ferramentas
- **VS Code** — ambiente de desenvolvimento
- **phpMyAdmin** — administração do banco
- **Git / GitHub** — versionamento

---

## 🗄️ Banco de Dados

### Estrutura das Tabelas

| Tabela | Descrição |
|--------|-----------|
| `usuario` | Usuários do sistema (Admin/Funcionário) |
| `clientes` | Clientes cadastrados por telefone |
| `profissionais` | Profissionais vinculados a usuários |
| `servicos` | Serviços oferecidos (nome, valor, duração) |
| `agendamentos` | Agendamentos com data, horário e status |
| `bloqueios_profissionais` | Bloqueios pontuais de agenda |
| `horarios_atendimento` | Horário de funcionamento por dia da semana |
| `portfolio` | Trabalhos exibidos no site |
| `empresa` | Dados institucionais do salão |
| `historico_logins` | Registro de acessos (IP + dispositivo + Data/hora) |

### 🔧 Instalação do Banco

**Pré-requisitos:** XAMPP instalado e rodando (Apache + MySQL).

**1. Inicie o XAMPP** — ative os serviços **Apache** e **MySQL**.

**2. Acesse o phpMyAdmin:**
```
http://localhost/phpmyadmin
```

**3. Crie o banco de dados:**
- Clique em **Novo** (New) na barra lateral
- Nome: `app-uepg`
- Collation: `utf8mb4_general_ci`
- Clique em **Criar**

**4. Importe o schema:**
- Selecione o banco `app-uepg`
- Vá na aba **Importar**
- Selecione o arquivo `database/schema.sql`
- Clique em **Executar**

**5. Verifique** se as 10 tabelas foram criadas corretamente.

### ⚙️ Configuração da Conexão

Crie/edite o arquivo de conexão no backend (`backend/config.php` ou equivalente) com:

```php
<?php
$host     = "localhost";
$usuario  = "root";
$senha    = "";
$banco    = "app-uepg";
?>
```

> ⚠️ **Atenção:** em ambiente de produção, nunca use `root` sem senha. Crie um usuário dedicado no MySQL.

---

## ▶️ Como Executar o Projeto

### Backend (PHP + Apache)

1. Coloque a pasta do projeto em `C:\xampp\htdocs\` (já está em `app-uepg`)
2. Inicie o XAMPP (Apache + MySQL)
3. Acesse: `http://localhost/app-uepg`

### Frontend (React)

```bash
cd frontend
npm install
npm start
```

O frontend roda em `http://localhost:3000` (padrão do Create React App / Vite).

---

## 📂 Estrutura do Projeto

```
app-uepg/
├── backend/                     # API em PHP
│   ├── config/                  # Configurações
│   ├── includes/                # Funções auxiliares
│   ├── public/                  # Endpoints públicos
│   └── routes/                  # Rotas da API
├── frontend/                    # SPA em React
│   ├── public/
│   └── src/
│       ├── components/          # Componentes reutilizáveis
│       └── pages/               # Páginas (Agendamentos, Serviços, etc.)
├── database/
│   └── schema.sql               # Estrutura do banco
├── index.php                    # Entry point
├── .htaccess                    # Regras Apache (segurança + rotas)
├── package.json
└── README.md
```

---

## 🔒 Segurança

O projeto já inclui cabeçalhos de segurança configurados no `.htaccess`:

- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection`
- `Referrer-Policy: no-referrer`
- `Content-Security-Policy`
- `ServerSignature Off`

---

## 👤 Autor

**Michel de Lima**
Disciplina: Projeto de Software — 3º ano — UEPG
Ano: 2025

---

## 📄 Licença

Projeto acadêmico sem fins comerciais. Uso livre para fins educacionais.

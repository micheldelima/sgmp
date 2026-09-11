import React from 'react';
import { HelmetProvider } from 'react-helmet-async';
import { createRoot } from 'react-dom/client';
import App from './App';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'bootstrap-icons/font/bootstrap-icons.css';

const container = document.getElementById('root');
const root = createRoot(container);

root.render(
  <HelmetProvider>
    <App />
  </HelmetProvider>
);
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { BaseStyle } from '@fzed51/green-terminal';
import { App } from './App.tsx';
import './index.css';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BaseStyle />
    <BrowserRouter>
      <App />
    </BrowserRouter>
  </StrictMode>,
);

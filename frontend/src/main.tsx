/**
 * Gazelle Frontend Entry Point
 *
 * Main application entry with Splunk UI Theme Provider
 */

import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import SplunkThemeProvider from '@splunk/themes/SplunkThemeProvider';

import App from './App';
import { DEFAULT_FAMILY, DEFAULT_COLOR_SCHEME, DEFAULT_DENSITY } from '@themes';

// Global styles reset
import './styles/global.css';

// Render application
const root = ReactDOM.createRoot(document.getElementById('root') as HTMLElement);

root.render(
  <React.StrictMode>
    <BrowserRouter>
      <SplunkThemeProvider
        family={DEFAULT_FAMILY}
        colorScheme={DEFAULT_COLOR_SCHEME}
        density={DEFAULT_DENSITY}
      >
        <App />
      </SplunkThemeProvider>
    </BrowserRouter>
  </React.StrictMode>
);

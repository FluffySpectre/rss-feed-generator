import React from 'react';
import { AppProvider } from './context/AppContext';
import HomePage from './pages/HomePage';
import Alert from './components/common/Alert';
import './styles/main.css';
import './styles/responsive.css';
import './styles/icons.css';

const App = () => {
  return (
    <AppProvider>
      <div>
        <header className="header">
          <div className="container header-content">
            <h1 className="app-title">AI-Powered RSS Feed Generator</h1>
          </div>
        </header>
        
        <main className="main-content">
          <HomePage />
        </main>
        
        <footer className="footer">
          <div className="container">
            <p className="footer-text">
              &copy; {new Date().getFullYear()} AI-Powered RSS Feed Generator
            </p>
          </div>
        </footer>
        
        <Alert />
      </div>
    </AppProvider>
  );
};

export default App;

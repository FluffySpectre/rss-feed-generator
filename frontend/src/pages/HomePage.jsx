import React from 'react';
import WebsiteForm from '../components/website/WebsiteForm';
import WebsiteList from '../components/website/WebsiteList';

const HomePage = () => {
  return (
    <div>
      <div className="intro-section">
        <p className="intro-text">
          This tool automatically generates RSS feeds for news websites using AI. Simply add a website URL, and the system will create a custom parser to extract today's news articles.
        </p>
        
        <p className="intro-text">
          Each registered website gets its own RSS feed that you can subscribe to in your favorite RSS reader.
        </p>
        
        <div className="success-panel">
          <div className="success-panel-content">
            <svg className="success-icon" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
            </svg>
            <div className="success-text">
              <span className="success-text-bold">Fully Automated:</span> The system automatically processes all registered websites on a scheduled basis to keep your RSS feeds up-to-date with fresh content.
            </div>
          </div>
        </div>
      </div>
      
      <WebsiteForm />
      <WebsiteList />
    </div>
  );
};

export default HomePage;

import React from 'react';
import { useAppContext } from '../../context/AppContext';
import WebsiteCard from './WebsiteCard';

const WebsiteList = () => {
  const { websites, isLoading } = useAppContext();

  // Render loading state
  if (isLoading && websites.length === 0) {
    return (
      <div className="card">
        <div className="loading-container">
          <div className="spinner"></div>
          <span className="loading-text">Loading websites...</span>
        </div>
      </div>
    );
  }

  // Render empty state
  if (websites.length === 0) {
    return (
      <div className="card empty-state">
        <p className="empty-text">No websites registered yet.</p>
        <p className="empty-subtext">Add your first website using the form above.</p>
      </div>
    );
  }

  return (
    <div>
      <div className="website-list-header">
        <h2 className="website-list-title">Registered Websites</h2>
        
        <div className="website-list-info">
          <i className="far fa-clock"></i> RSS feeds update automatically
        </div>
      </div>

      <div className="info-panel">
        <div className="info-panel-content">
          <svg className="info-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
          </svg>
          <div className="info-text">
            All websites are automatically processed on a regular schedule. New content will appear in the RSS feeds without manual intervention.
          </div>
        </div>
      </div>

      <div className="website-list">
        {websites.map((website) => (
          <WebsiteCard key={website.domain} website={website} />
        ))}
      </div>
    </div>
  );
};

export default WebsiteList;

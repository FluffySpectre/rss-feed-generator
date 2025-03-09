import React, { useState } from 'react';
import { useAppContext } from '../../context/AppContext';

const WebsiteForm = () => {
  const { registerWebsite, isLoading } = useAppContext();
  const [url, setUrl] = useState('');
  const [urlError, setUrlError] = useState('');

  // Validate URL format
  const validateUrl = (value) => {
    try {
      new URL(value);
      setUrlError('');
      return true;
    } catch (err) {
      setUrlError('Please enter a valid URL (including http:// or https://)');
      return false;
    }
  };

  // Handle input change
  const handleUrlChange = (e) => {
    const value = e.target.value;
    setUrl(value);
    if (value) {
      validateUrl(value);
    } else {
      setUrlError('');
    }
  };

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!url) {
      setUrlError('URL is required');
      return;
    }
    
    if (!validateUrl(url)) {
      return;
    }
    
    await registerWebsite(url);
    setUrl('');
  };

  return (
    <div className="card">
      <h2 className="card-title">Add News Website</h2>
      
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="url" className="form-label">
            Website URL
          </label>
          <input
            type="text"
            id="url"
            value={url}
            onChange={handleUrlChange}
            placeholder="https://example.com"
            className={`form-control ${urlError ? 'is-invalid' : ''}`}
            disabled={isLoading}
          />
          {urlError && (
            <p className="form-error">{urlError}</p>
          )}
        </div>
        
        <button
          type="submit"
          className="btn btn-primary"
          disabled={isLoading}
        >
          {isLoading ? 'Adding...' : 'Add Website'}
        </button>
      </form>
    </div>
  );
};

export default WebsiteForm;

import React from 'react';
import ApiService from '../../api/api';

const WebsiteCard = ({ website }) => {
  // Status labels
  const statusLabels = {
    success: 'Success',
    error_download: 'Failed to download',
    error_parser_generation: 'Failed to generate parser',
    error_parsing: 'Failed to parse content',
    error_parsing_final: 'Failed to parse after regeneration',
    error_rss_generation: 'Failed to generate RSS',
    error_exception: 'An error occurred',
    default: 'Not processed',
  };

  // Get status class
  const getStatusClass = (status) => {
    if (status === 'success') {
      return 'status-success';
    } else if (status && status.startsWith('error')) {
      return 'status-error';
    }
    return 'status-default';
  };

  // Get status label
  const getStatusLabel = (status) => {
    return statusLabels[status] || statusLabels.default;
  };

  // RSS feed URL
  const rssFeedUrl = ApiService.getRssFeedUrl(website.domain);

  return (
    <div className="website-card">
      <div className="website-card-header">
        <div className="website-info">
          <h3 className="website-domain">{website.domain}</h3>
          <p className="website-url">{website.url}</p>
          
          {website.lastParseDate && (
            <p className="website-date">
              Last processed: {new Date(website.lastParseDate).toLocaleString()}
            </p>
          )}
          
          {website.lastParseStatus && (
            <div className={`status-badge ${getStatusClass(website.lastParseStatus)}`}>
              {getStatusLabel(website.lastParseStatus)}
            </div>
          )}
        </div>
        
        <div className="website-actions">
          {website.lastParseStatus === 'success' && (
            <a
              href={rssFeedUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="btn btn-success btn-sm"
            >
              View RSS
            </a>
          )}
        </div>
      </div>
      
      <div className="website-footer">
        <p className="website-status">
          <i className="far fa-clock"></i> Updates automatically via scheduled job
        </p>
      </div>
    </div>
  );
};

export default WebsiteCard;

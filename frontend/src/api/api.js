const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000';

/**
 * API Service for interacting with the backend
 */
class ApiService {
  /**
   * Register a new website
   * 
   * @param {string} url The website URL
   * @returns {Promise} Promise with the response data
   */
  static async registerWebsite(url) {
    try {
      const response = await fetch(`${API_BASE_URL}/api/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ url }),
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('Failed to register website:', error);
      throw error;
    }
  }
  
  /**
   * Get all registered websites
   * 
   * @returns {Promise} Promise with the response data
   */
  static async getWebsites() {
    try {
      const response = await fetch(`${API_BASE_URL}/api/websites`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('Failed to get websites:', error);
      throw error;
    }
  }
  
  /**
   * Process a website to generate RSS feed
   * 
   * @param {string} domain The website domain
   * @param {boolean} forceRegeneration Whether to force parser regeneration
   * @returns {Promise} Promise with the response data
   */
  static async processWebsite(domain, forceRegeneration = false) {
    try {
      const response = await fetch(`${API_BASE_URL}/api/process`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ domain, forceRegeneration }),
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('Failed to process website:', error);
      throw error;
    }
  }
  
  /**
   * Process all registered websites
   * 
   * @returns {Promise} Promise with the response data
   */
  static async processAllWebsites() {
    try {
      const response = await fetch(`${API_BASE_URL}/api/process-all`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('Failed to process all websites:', error);
      throw error;
    }
  }
  
  /**
   * Get the RSS feed URL for a domain
   * 
   * @param {string} domain The website domain
   * @returns {string} The RSS feed URL
   */
  static getRssFeedUrl(domain) {
    return `${API_BASE_URL}/sites/${domain}/rss.xml`;
  }
}

export default ApiService;

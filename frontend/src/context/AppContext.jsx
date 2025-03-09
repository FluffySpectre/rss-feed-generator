import React, { createContext, useContext, useState, useEffect } from 'react';
import ApiService from '../api/api';

// Create context
const AppContext = createContext();

// Custom hook to use the app context
export const useAppContext = () => useContext(AppContext);

// Context provider component
export const AppProvider = ({ children }) => {
  const [websites, setWebsites] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  // Fetch websites on initial load
  useEffect(() => {
    fetchWebsites();
    
    // Set up automatic refresh every 5 minutes to show latest status
    const refreshInterval = setInterval(() => {
      fetchWebsites();
    }, 5 * 60 * 1000);
    
    return () => clearInterval(refreshInterval);
  }, []);

  // Fetch all websites
  const fetchWebsites = async () => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await ApiService.getWebsites();
      
      if (response.success) {
        setWebsites(response.data || []);
      } else {
        setError(response.message || 'Failed to fetch websites');
      }
    } catch (err) {
      setError('Network error while fetching websites');
    } finally {
      setIsLoading(false);
    }
  };

  // Register a new website
  const registerWebsite = async (url) => {
    try {
      setIsLoading(true);
      setError(null);
      setSuccessMessage(null);
      
      const response = await ApiService.registerWebsite(url);
      
      if (response.success) {
        // Add the new website to the list or update it if it already exists
        const exists = websites.some((site) => site.domain === response.data.domain);
        
        if (!exists) {
          setWebsites([...websites, response.data]);
        }
        
        setSuccessMessage('Website registered successfully. It will be processed automatically by the system.');
        return response.data;
      } else {
        setError(response.message || 'Failed to register website');
        return null;
      }
    } catch (err) {
      setError('Network error while registering website');
      return null;
    } finally {
      setIsLoading(false);
    }
  };

  // Clear messages
  const clearMessages = () => {
    setError(null);
    setSuccessMessage(null);
  };

  // Context value
  const contextValue = {
    websites,
    isLoading,
    error,
    successMessage,
    clearMessages,
    fetchWebsites,
    registerWebsite
  };

  return (
    <AppContext.Provider value={contextValue}>
      {children}
    </AppContext.Provider>
  );
};

export default AppContext;

import React, { useEffect } from 'react';
import { useAppContext } from '../../context/AppContext';

const Alert = () => {
  const { error, successMessage, clearMessages } = useAppContext();
  
  // Auto-dismiss messages after 5 seconds
  useEffect(() => {
    if (error || successMessage) {
      const timer = setTimeout(() => {
        clearMessages();
      }, 5000);
      
      return () => clearTimeout(timer);
    }
  }, [error, successMessage, clearMessages]);
  
  // Handle close button click
  const handleClose = () => {
    clearMessages();
  };
  
  // If no messages, don't render anything
  if (!error && !successMessage) {
    return null;
  }
  
  const isError = !!error;
  const alertType = isError ? 'danger' : 'success';
  
  return (
    <div className={`alert alert-${alertType}`}>
      <div className={`alert-icon ${alertType}`}>
        {isError ? (
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
          </svg>
        ) : (
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
          </svg>
        )}
      </div>
      
      <div className="alert-content">
        <p className={`alert-text ${alertType}`}>
          {error || successMessage}
        </p>
      </div>
      
      <button 
        onClick={handleClose} 
        className={`alert-close ${alertType}`}
      >
        <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
        </svg>
      </button>
    </div>
  );
};

export default Alert;

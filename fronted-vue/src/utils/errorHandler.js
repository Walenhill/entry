/**
 * Generic API error handler to reduce code duplication
 * @param {Error} error - The error object from axios or other sources
 * @param {string} logMessage - Message to log to console
 * @param {string} alertPrefix - Prefix for the user-facing alert
 */
export const handleApiError = (error, logMessage, alertPrefix) => {
  console.error(logMessage, error);
  const errorMessage = error.response?.data?.message || error.message || 'Unknown error';
  alert(`${alertPrefix}: ${errorMessage}`);
};

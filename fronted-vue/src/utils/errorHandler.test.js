import { test, describe, beforeEach, afterEach, expect } from 'vitest';
import { handleApiError } from './errorHandler.js';

describe('handleApiError', () => {
  let originalConsoleError;
  let originalAlert;
  let consoleErrorCalls = [];
  let alertCalls = [];

  beforeEach(() => {
    consoleErrorCalls = [];
    alertCalls = [];

    originalConsoleError = console.error;
    console.error = (...args) => {
      consoleErrorCalls.push(args);
    };

    originalAlert = global.alert;
    global.alert = (msg) => {
      alertCalls.push(msg);
    };
  });

  afterEach(() => {
    console.error = originalConsoleError;
    global.alert = originalAlert;
  });

  test('uses error.response.data.error when available', () => {
    const error = {
      response: {
        data: {
          error: 'Backend specific error'
        }
      }
    };

    handleApiError(error, 'Log message', 'Alert Prefix');

    expect(consoleErrorCalls.length).toBe(1);
    expect(consoleErrorCalls[0]).toEqual(['Log message', error]);

    expect(alertCalls.length).toBe(1);
    expect(alertCalls[0]).toBe('Alert Prefix: Backend specific error');
  });

  test('uses error.response.data.message when error field is unavailable', () => {
    const error = {
      response: {
        data: {
          message: 'Server validation error'
        }
      }
    };

    handleApiError(error, 'Log message', 'Alert Prefix');

    expect(consoleErrorCalls.length).toBe(1);
    expect(consoleErrorCalls[0]).toEqual(['Log message', error]);

    expect(alertCalls.length).toBe(1);
    expect(alertCalls[0]).toBe('Alert Prefix: Server validation error');
  });

  test('falls back to error.message when response.data.message is unavailable', () => {
    const error = new Error('Network error');

    handleApiError(error, 'Network log', 'Network Alert');

    expect(consoleErrorCalls.length).toBe(1);
    expect(consoleErrorCalls[0]).toEqual(['Network log', error]);

    expect(alertCalls.length).toBe(1);
    expect(alertCalls[0]).toBe('Network Alert: Network error');
  });

  test('falls back to Unknown error when no message is available', () => {
    const error = {};

    handleApiError(error, 'Unknown log', 'Unknown Alert');

    expect(consoleErrorCalls.length).toBe(1);
    expect(consoleErrorCalls[0]).toEqual(['Unknown log', error]);

    expect(alertCalls.length).toBe(1);
    expect(alertCalls[0]).toBe('Unknown Alert: Unknown error');
  });
});

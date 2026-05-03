import { test, describe, beforeEach, afterEach } from 'node:test';
import assert from 'node:assert';
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

  test('uses error.response.data.message when available', () => {
    const error = {
      response: {
        data: {
          message: 'Server validation error'
        }
      }
    };

    handleApiError(error, 'Log message', 'Alert Prefix');

    assert.strictEqual(consoleErrorCalls.length, 1);
    assert.deepStrictEqual(consoleErrorCalls[0], ['Log message', error]);

    assert.strictEqual(alertCalls.length, 1);
    assert.strictEqual(alertCalls[0], 'Alert Prefix: Server validation error');
  });

  test('falls back to error.message when response.data.message is unavailable', () => {
    const error = new Error('Network error');

    handleApiError(error, 'Network log', 'Network Alert');

    assert.strictEqual(consoleErrorCalls.length, 1);
    assert.deepStrictEqual(consoleErrorCalls[0], ['Network log', error]);

    assert.strictEqual(alertCalls.length, 1);
    assert.strictEqual(alertCalls[0], 'Network Alert: Network error');
  });

  test('falls back to Unknown error when no message is available', () => {
    const error = {};

    handleApiError(error, 'Unknown log', 'Unknown Alert');

    assert.strictEqual(consoleErrorCalls.length, 1);
    assert.deepStrictEqual(consoleErrorCalls[0], ['Unknown log', error]);

    assert.strictEqual(alertCalls.length, 1);
    assert.strictEqual(alertCalls[0], 'Unknown Alert: Unknown error');
  });
});

import { test, describe, beforeEach, afterEach } from 'node:test';
import assert from 'node:assert';
import apiClient, { slotsApi } from './index.js';

describe('slotsApi', () => {
  let originalGet;
  let originalPost;
  let originalDelete;

  let getCalls = [];
  let postCalls = [];
  let deleteCalls = [];

  beforeEach(() => {
    getCalls = [];
    postCalls = [];
    deleteCalls = [];

    originalGet = apiClient.get;
    originalPost = apiClient.post;
    originalDelete = apiClient.delete;

    apiClient.get = (url, config) => {
      getCalls.push({ url, config });
      return Promise.resolve({ data: 'mocked get response' });
    };

    apiClient.post = (url, data, config) => {
      postCalls.push({ url, data, config });
      return Promise.resolve({ data: 'mocked post response' });
    };

    apiClient.delete = (url, config) => {
      deleteCalls.push({ url, config });
      return Promise.resolve({ data: 'mocked delete response' });
    };
  });

  afterEach(() => {
    apiClient.get = originalGet;
    apiClient.post = originalPost;
    apiClient.delete = originalDelete;
  });

  test('getAllSlots calls GET /slots with correct default parameters', async () => {
    await slotsApi.getAllSlots();
    assert.strictEqual(getCalls.length, 1);
    assert.strictEqual(getCalls[0].url, '/slots');
    assert.deepStrictEqual(getCalls[0].config, { params: { role: 'client' } });
  });

  test('getAllSlots calls GET /slots with provided date and role', async () => {
    await slotsApi.getAllSlots('2023-10-10', 'admin');
    assert.strictEqual(getCalls.length, 1);
    assert.strictEqual(getCalls[0].url, '/slots');
    assert.deepStrictEqual(getCalls[0].config, { params: { date: '2023-10-10', role: 'admin' } });
  });

  test('createSlot calls POST /slots with correct data', async () => {
    const slotData = { time: '10:00' };
    await slotsApi.createSlot(slotData);
    assert.strictEqual(postCalls.length, 1);
    assert.strictEqual(postCalls[0].url, '/slots');
    assert.deepStrictEqual(postCalls[0].data, slotData);
  });

  test('generateSlots calls POST /slots/generate with correct template data', async () => {
    const templateData = { templateId: 1 };
    await slotsApi.generateSlots(templateData);
    assert.strictEqual(postCalls.length, 1);
    assert.strictEqual(postCalls[0].url, '/slots/generate');
    assert.deepStrictEqual(postCalls[0].data, templateData);
  });

  test('bookSlot calls POST /slots/{id}/book with correct id and booking data', async () => {
    const bookingData = { name: 'John Doe' };
    await slotsApi.bookSlot(123, bookingData);
    assert.strictEqual(postCalls.length, 1);
    assert.strictEqual(postCalls[0].url, '/slots/123/book');
    assert.deepStrictEqual(postCalls[0].data, bookingData);
  });

  test('cancelBooking calls POST /slots/{id}/cancel with correct id', async () => {
    await slotsApi.cancelBooking(123);
    assert.strictEqual(postCalls.length, 1);
    assert.strictEqual(postCalls[0].url, '/slots/123/cancel');
    assert.strictEqual(postCalls[0].data, undefined);
  });

  test('deleteSlot calls DELETE /slots/{id} with correct id', async () => {
    await slotsApi.deleteSlot(123);
    assert.strictEqual(deleteCalls.length, 1);
    assert.strictEqual(deleteCalls[0].url, '/slots/123');
  });

  test('getStats calls GET /stats', async () => {
    await slotsApi.getStats();
    assert.strictEqual(getCalls.length, 1);
    assert.strictEqual(getCalls[0].url, '/stats');
    assert.strictEqual(getCalls[0].config, undefined);
  });
});

import { test, describe, beforeEach, afterEach, expect } from 'vitest';
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
    expect(getCalls.length).toBe(1);
    expect(getCalls[0].url).toBe('/slots');
    expect(getCalls[0].config).toEqual({ params: { role: 'client' } });
  });

  test('getAllSlots calls GET /slots with provided date and role', async () => {
    await slotsApi.getAllSlots('2023-10-10', 'admin');
    expect(getCalls.length).toBe(1);
    expect(getCalls[0].url).toBe('/slots');
    expect(getCalls[0].config).toEqual({ params: { date: '2023-10-10', role: 'admin' } });
  });

  test('createSlot calls POST /slots with correct data', async () => {
    const slotData = { time: '10:00' };
    await slotsApi.createSlot(slotData);
    expect(postCalls.length).toBe(1);
    expect(postCalls[0].url).toBe('/slots');
    expect(postCalls[0].data).toEqual(slotData);
  });

  test('generateSlots calls POST /slots/generate with correct template data', async () => {
    const templateData = { templateId: 1 };
    await slotsApi.generateSlots(templateData);
    expect(postCalls.length).toBe(1);
    expect(postCalls[0].url).toBe('/slots/generate');
    expect(postCalls[0].data).toEqual(templateData);
  });

  test('bookSlot calls POST /slots/{id}/book with correct id and booking data', async () => {
    const bookingData = { name: 'John Doe' };
    await slotsApi.bookSlot(123, bookingData);
    expect(postCalls.length).toBe(1);
    expect(postCalls[0].url).toBe('/slots/123/book');
    expect(postCalls[0].data).toEqual(bookingData);
  });

  test('cancelBooking calls POST /slots/{id}/cancel with correct id', async () => {
    await slotsApi.cancelBooking(123);
    expect(postCalls.length).toBe(1);
    expect(postCalls[0].url).toBe('/slots/123/cancel');
    expect(postCalls[0].data).toBe(undefined);
  });

  test('deleteSlot calls DELETE /slots/{id} with correct id', async () => {
    await slotsApi.deleteSlot(123);
    expect(deleteCalls.length).toBe(1);
    expect(deleteCalls[0].url).toBe('/slots/123');
  });

  test('getStats calls GET /stats', async () => {
    await slotsApi.getStats();
    expect(getCalls.length).toBe(1);
    expect(getCalls[0].url).toBe('/stats');
    expect(getCalls[0].config).toBe(undefined);
  });
});

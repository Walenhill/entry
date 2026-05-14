import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useSlotsStore } from './slots.js';
import { slotsApi } from '../api/index.js';

vi.mock('../api/index.js', () => ({
  slotsApi: {
    getAllSlots: vi.fn(),
    createSlot: vi.fn(),
    bookSlot: vi.fn(),
    cancelBooking: vi.fn(),
    deleteSlot: vi.fn(),
    getStats: vi.fn(),
  }
}));

describe('Slots Store - Error Handling', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    global.localStorage = {
      getItem: vi.fn(() => 'true')
    };
  });

  afterEach(() => {
    vi.restoreAllMocks();
    delete global.localStorage;
  });

  describe('fetchSlots', () => {
    it('handles API errors gracefully in catch block', async () => {
      const store = useSlotsStore();
      const mockError = new Error('API Error');

      slotsApi.getAllSlots.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      await expect(store.fetchSlots()).rejects.toThrow('API Error');

      expect(consoleSpy).toHaveBeenCalledWith('Error loading slots:', mockError);
      expect(store.isLoading).toBe(false);
    });
  });

  describe('createSlot', () => {
    it('handles API errors gracefully in catch block', async () => {
      const store = useSlotsStore();
      const mockError = new Error('API Error');

      slotsApi.createSlot.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      await expect(store.createSlot({})).rejects.toThrow('API Error');

      expect(consoleSpy).toHaveBeenCalledWith('Error creating slot:', mockError);
    });
  });

  describe('bookSlot', () => {
    it('handles API errors gracefully in catch block', async () => {
      const store = useSlotsStore();
      const mockError = new Error('API Error');

      slotsApi.bookSlot.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      await expect(store.bookSlot(1, {})).rejects.toThrow('API Error');

      expect(consoleSpy).toHaveBeenCalledWith('Error booking slot:', mockError);
    });
  });

  describe('cancelBooking', () => {
    it('handles API errors gracefully in catch block', async () => {
      const store = useSlotsStore();
      const mockError = new Error('API Error');

      slotsApi.cancelBooking.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      await expect(store.cancelBooking(1)).rejects.toThrow('API Error');

      expect(consoleSpy).toHaveBeenCalledWith('Error canceling booking:', mockError);
    });
  });

  describe('deleteSlot', () => {
    it('handles API errors gracefully in catch block', async () => {
      const store = useSlotsStore();
      const mockError = new Error('API Error');

      slotsApi.deleteSlot.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      await expect(store.deleteSlot(1)).rejects.toThrow('API Error');

      expect(consoleSpy).toHaveBeenCalledWith('Error deleting slot:', mockError);
    });
  });

  describe('fetchStats', () => {
    it('handles API errors gracefully in catch block', async () => {
      const store = useSlotsStore();
      const mockError = new Error('API Error');

      slotsApi.getStats.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      // fetchStats does NOT throw the error, it catches and sets statsError
      await store.fetchStats();

      expect(consoleSpy).toHaveBeenCalledWith('Error fetching stats:', mockError);
      expect(store.statsError).toBe('Ошибка при получении данных');
      expect(store.statsLoading).toBe(false);
    });
  });
});

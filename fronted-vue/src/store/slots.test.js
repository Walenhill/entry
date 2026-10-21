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

  describe('formatSlot', () => {
    it('formats an available slot correctly', () => {
      const store = useSlotsStore();
      const slotData = {
        id: 1,
        start_time: '2023-11-01 10:00:00',
        end_time: '2023-11-01 11:00:00',
        description: 'Test description',
        status: 'available',
        client_name: null,
        client_phone: null
      };

      const formatted = store.formatSlot(slotData);

      expect(formatted.id).toBe(1);
      expect(formatted.raw_start_time).toBe('2023-11-01 10:00:00');
      expect(formatted.date).toBe('2023-11-01');
      expect(formatted.start_time).toBe('10:00');
      expect(formatted.end_time).toBe('11:00');
      expect(formatted.description).toBe('Test description');
      expect(formatted.is_booked).toBe(false);
      expect(formatted.booked_by).toBeNull();
      expect(formatted.booking_comment).toBeNull();
      expect(formatted.statusClass).toBe('available');
      expect(formatted.statusText).toBe('Свободно');
      expect(formatted.formattedDate).toBeDefined(); // Formatted by Intl.DateTimeFormat
    });

    it('formats a booked slot correctly', () => {
      const store = useSlotsStore();
      const slotData = {
        id: 2,
        start_time: '2023-11-02 14:30:00',
        end_time: '2023-11-02 15:30:00',
        description: '',
        status: 'booked',
        client_name: 'John Doe',
        client_phone: '+1234567890'
      };

      const formatted = store.formatSlot(slotData);

      expect(formatted.id).toBe(2);
      expect(formatted.is_booked).toBe(true);
      expect(formatted.booked_by).toBe('John Doe');
      expect(formatted.booking_comment).toBe('+1234567890');
      expect(formatted.statusClass).toBe('booked');
      expect(formatted.statusText).toBe('Забронировано');
    });
  });

  describe('fetchSlots', () => {
    it('successfully fetches and formats slots', async () => {
      const store = useSlotsStore();
      const mockSlots = [
        { id: 1, start_time: '2023-10-27 10:00:00', end_time: '2023-10-27 11:00:00', status: 'available' },
        { id: 2, start_time: '2023-10-28 12:00:00', end_time: '2023-10-28 13:00:00', status: 'booked', client_name: 'Alice' }
      ];

      slotsApi.getAllSlots.mockResolvedValue({ data: { success: true, data: mockSlots } });

      await store.fetchSlots();

      expect(slotsApi.getAllSlots).toHaveBeenCalled();
      expect(store.slots.length).toBe(2);
      expect(store.slots[0].id).toBe(1);
      expect(store.slots[0].is_booked).toBe(false);
      expect(store.slots[1].id).toBe(2);
      expect(store.slots[1].is_booked).toBe(true);
      expect(store.slots[1].booked_by).toBe('Alice');
      expect(store.isLoading).toBe(false);
      expect(store.error).toBeNull();
    });

    it('handles API errors gracefully in catch block', async () => {
      const store = useSlotsStore();
      const mockError = new Error('API Error');

      slotsApi.getAllSlots.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      await store.fetchSlots();

      expect(consoleSpy).toHaveBeenCalledWith('Error loading slots:', mockError);
      expect(store.error).toBe('API Error');
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
      expect(store.statsError).toBe('API Error');
      expect(store.statsLoading).toBe(false);
    });
  });
});

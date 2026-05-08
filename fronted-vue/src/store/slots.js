import { defineStore } from 'pinia';
import { slotsApi } from '../api';

export const useSlotsStore = defineStore('slots', {
  state: () => ({
    slots: [],
    isLoading: false,
    stats: null,
    statsLoading: false,
    statsError: null
  }),
  actions: {
    async fetchSlots(date = null) {
      this.isLoading = true;
      try {
        const role = localStorage.getItem('is_logged_in') === 'true' ? 'admin' : 'client';
        const response = await slotsApi.getAllSlots(date, role);

        if (response.data.success) {
          this.slots = response.data.data.map(slot => {
            // Performance optimization: Using string slicing instead of Date instantiation
            // reduces processing time significantly, as the DB returns strict YYYY-MM-DD HH:MM:SS format
            return {
              id: slot.id,
              date: slot.start_time.substring(0, 10),
              start_time: slot.start_time.substring(11, 16),
              end_time: slot.end_time.substring(11, 16),
              description: slot.description,
              is_booked: slot.status === 'booked',
              booked_by: slot.client_name,
              booking_comment: slot.client_phone // Use client_phone for the comment field in UI
            };
          });
        }
      } catch (error) {
        console.error('Error loading slots:', error);
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    async createSlot(slotData) {
      try {
        const response = await slotsApi.createSlot(slotData);
        if (response.data.success && response.data.slot) {
          const slot = response.data.slot;
          this.slots.push({
            id: slot.id,
            date: slot.start_time.substring(0, 10),
            start_time: slot.start_time.substring(11, 16),
            end_time: slot.end_time.substring(11, 16),
            description: slot.description,
            is_booked: slot.status === 'booked',
            booked_by: slot.client_name,
            booking_comment: slot.client_phone
          });
          // Sort slots by start_time ascending as backend does
          this.slots.sort((a, b) => {
             const datetimeA = a.date + ' ' + a.start_time;
             const datetimeB = b.date + ' ' + b.start_time;
             return datetimeA.localeCompare(datetimeB);
          });
        }
      } catch (error) {
        console.error('Error creating slot:', error);
        throw error;
      }
    },

    async bookSlot(id, bookingData) {
      try {
        const response = await slotsApi.bookSlot(id, bookingData);
        if (response.data.success && response.data.slot) {
          const slotIndex = this.slots.findIndex(s => s.id === id);
          if (slotIndex !== -1) {
            this.slots[slotIndex].is_booked = true;
            this.slots[slotIndex].booked_by = response.data.slot.client_name;
            this.slots[slotIndex].booking_comment = response.data.slot.client_phone;
          }
        }
      } catch (error) {
        console.error('Error booking slot:', error);
        throw error;
      }
    },

    async cancelBooking(id) {
      try {
        const response = await slotsApi.cancelBooking(id);
        if (response.data.success && response.data.slot) {
          const slotIndex = this.slots.findIndex(s => s.id === id);
          if (slotIndex !== -1) {
            this.slots[slotIndex].is_booked = false;
            this.slots[slotIndex].booked_by = null;
            this.slots[slotIndex].booking_comment = null;
          }
        }
      } catch (error) {
        console.error('Error canceling booking:', error);
        throw error;
      }
    },

    async deleteSlot(id) {
      try {
        await slotsApi.deleteSlot(id);
        this.slots = this.slots.filter(s => s.id !== id);
      } catch (error) {
        console.error('Error deleting slot:', error);
        throw error;
      }
    },

    async fetchStats() {
      this.statsLoading = true;
      this.statsError = null;
      try {
        const response = await slotsApi.getStats();
        if (response.data.success) {
          this.stats = response.data.data;
        } else {
          this.statsError = 'Не удалось загрузить статистику';
        }
      } catch (error) {
        console.error('Error fetching stats:', error);
        this.statsError = 'Ошибка при получении данных';
      } finally {
        this.statsLoading = false;
      }
    }
  }
});

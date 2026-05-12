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
    formatSlot(slot) {
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
    },

    async fetchSlots(date = null) {
      this.isLoading = true;
      try {
        const role = localStorage.getItem('is_logged_in') === 'true' ? 'admin' : 'client';
        const response = await slotsApi.getAllSlots(date, role);

        // Handle both array response and object with data array to prevent integration failures
        const slotsData = Array.isArray(response.data) ? response.data :
                         (response.data.success ? response.data.data : null);

        if (slotsData) {
          this.slots = slotsData.map(slot => this.formatSlot(slot));
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
          // Performance optimization: Mutate local array instead of re-fetching all slots
          this.slots.push(this.formatSlot(response.data.slot));
          this.slots.sort((a, b) => {
            const timeA = a.date + ' ' + a.start_time;
            const timeB = b.date + ' ' + b.start_time;
            // Performance optimization: Using standard string comparison operators instead of localeCompare
            // is significantly faster for simple, strictly formatted ISO-like date strings.
            return timeA < timeB ? -1 : (timeA > timeB ? 1 : 0);
          });
        } else {
          await this.fetchSlots();
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
          // Performance optimization: Mutate local array instead of re-fetching all slots
          const index = this.slots.findIndex(s => s.id === id);
          if (index !== -1) {
            this.slots[index] = this.formatSlot(response.data.slot);
          }
        } else {
          await this.fetchSlots();
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
          // Performance optimization: Mutate local array instead of re-fetching all slots
          const index = this.slots.findIndex(s => s.id === id);
          if (index !== -1) {
            this.slots[index] = this.formatSlot(response.data.slot);
          }
        } else {
          await this.fetchSlots();
        }
      } catch (error) {
        console.error('Error canceling booking:', error);
        throw error;
      }
    },

    async deleteSlot(id) {
      try {
        const response = await slotsApi.deleteSlot(id);
        if (response.data.success) {
          // Performance optimization: Mutate local array instead of re-fetching all slots
          this.slots = this.slots.filter(s => s.id !== id);
        } else {
          await this.fetchSlots();
        }
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

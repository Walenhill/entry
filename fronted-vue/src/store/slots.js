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
            // Safari compatibility fix
            const startDate = new Date(slot.start_time.replace(' ', 'T'));
            const endDate = new Date(slot.end_time.replace(' ', 'T'));

            const padZero = (num) => num.toString().padStart(2, '0');

            return {
              id: slot.id,
              date: `${startDate.getFullYear()}-${padZero(startDate.getMonth() + 1)}-${padZero(startDate.getDate())}`,
              start_time: `${padZero(startDate.getHours())}:${padZero(startDate.getMinutes())}`,
              end_time: `${padZero(endDate.getHours())}:${padZero(endDate.getMinutes())}`,
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
        await slotsApi.createSlot(slotData);
        await this.fetchSlots();
      } catch (error) {
        console.error('Error creating slot:', error);
        throw error;
      }
    },

    async bookSlot(id, bookingData) {
      try {
        await slotsApi.bookSlot(id, bookingData);
        await this.fetchSlots();
      } catch (error) {
        console.error('Error booking slot:', error);
        throw error;
      }
    },

    async cancelBooking(id) {
      try {
        await slotsApi.cancelBooking(id);
        await this.fetchSlots();
      } catch (error) {
        console.error('Error canceling booking:', error);
        throw error;
      }
    },

    async deleteSlot(id) {
      try {
        await slotsApi.deleteSlot(id);
        await this.fetchSlots();
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

import { defineStore } from 'pinia';
import { directoryApi } from '../api';

export const useDirectoryStore = defineStore('directory', {
  state: () => ({
    services: [],
    staff: [],
    isLoading: false,
    error: null,
  }),
  actions: {
    async fetchDirectories() {
      this.isLoading = true;
      this.error = null;
      try {
        const [servicesResponse, staffResponse] = await Promise.all([
          directoryApi.getServices(),
          directoryApi.getStaff(),
        ]);

        if (servicesResponse.data) {
          this.services = servicesResponse.data;
        }
        if (staffResponse.data) {
          this.staff = staffResponse.data;
        }
      } catch (err) {
        console.error('Error loading directories:', err);
        this.error = 'Не удалось загрузить списки услуг и сотрудников';
      } finally {
        this.isLoading = false;
      }
    }
  }
});

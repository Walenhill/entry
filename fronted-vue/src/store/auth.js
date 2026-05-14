import { defineStore } from 'pinia';
import { authApi } from '../api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    isAuthenticated: localStorage.getItem('is_logged_in') === 'true',
  }),
  actions: {
    async login(password) {
      try {
        const response = await authApi.login(password);
        if (response.data.success) {
          localStorage.setItem('is_logged_in', 'true');
          this.isAuthenticated = true;
          return { success: true };
        } else {
          return { success: false, message: 'Неверный ответ сервера' };
        }
      } catch (err) {
        console.error('Login error:', err);
        return {
          success: false,
          message: err.response?.data?.message || 'Ошибка входа. Проверьте пароль.'
        };
      }
    },
    async telegramLogin(initData) {
      try {
        const response = await authApi.telegramLogin(initData);
        if (response.data.success && response.data.isAdmin) {
          localStorage.setItem('is_logged_in', 'true');
          this.isAuthenticated = true;
        }
        return response.data;
      } catch (err) {
        console.error('Telegram login error:', err);
        return {
          success: false,
          error: err.response?.data?.error || 'Ошибка входа через Telegram'
        };
      }
    },
    async logout() {
      try {
        await authApi.logout();
      } catch (e) {
        console.error('Logout error', e);
      } finally {
        localStorage.removeItem('is_logged_in');
        this.isAuthenticated = false;
      }
    },
    checkAuth() {
      this.isAuthenticated = localStorage.getItem('is_logged_in') === 'true';
    }
  }
});

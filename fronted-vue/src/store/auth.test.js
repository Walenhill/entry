import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore } from './auth.js';
import { authApi } from '../api/index.js';

vi.mock('../api/index.js', () => ({
  authApi: {
    login: vi.fn(),
    logout: vi.fn(),
  }
}));

describe('Auth Store', () => {
  let localStorageMock;

  beforeEach(() => {
    setActivePinia(createPinia());

    // Mock localStorage
    localStorageMock = (() => {
      let store = {};
      return {
        getItem: vi.fn((key) => store[key] || null),
        setItem: vi.fn((key, value) => {
          store[key] = value.toString();
        }),
        removeItem: vi.fn((key) => {
          delete store[key];
        }),
        clear: vi.fn(() => {
          store = {};
        })
      };
    })();

    vi.stubGlobal('localStorage', localStorageMock);
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  describe('initialization', () => {
    it('sets isAuthenticated to true if localStorage has is_logged_in=true', () => {
      localStorageMock.setItem('is_logged_in', 'true');
      const store = useAuthStore();
      expect(store.isAuthenticated).toBe(true);
    });

    it('sets isAuthenticated to false if localStorage does not have is_logged_in=true', () => {
      localStorageMock.setItem('is_logged_in', 'false');
      const store = useAuthStore();
      expect(store.isAuthenticated).toBe(false);
    });
  });

  describe('checkAuth', () => {
    it('updates isAuthenticated based on localStorage', () => {
      const store = useAuthStore();
      expect(store.isAuthenticated).toBe(false);

      localStorageMock.setItem('is_logged_in', 'true');
      store.checkAuth();
      expect(store.isAuthenticated).toBe(true);
    });
  });

  describe('login', () => {
    it('handles successful login', async () => {
      const store = useAuthStore();
      authApi.login.mockResolvedValue({ data: { success: true } });

      const result = await store.login('password123');

      expect(authApi.login).toHaveBeenCalledWith('password123');
      expect(localStorageMock.setItem).toHaveBeenCalledWith('is_logged_in', 'true');
      expect(store.isAuthenticated).toBe(true);
      expect(result).toEqual({ success: true });
    });

    it('handles unsuccessful login (false success flag)', async () => {
      const store = useAuthStore();
      authApi.login.mockResolvedValue({ data: { success: false } });

      const result = await store.login('password123');

      expect(localStorageMock.setItem).not.toHaveBeenCalled();
      expect(store.isAuthenticated).toBe(false);
      expect(result).toEqual({ success: false, message: 'Неверный ответ сервера' });
    });

    it('handles login API error with response message', async () => {
      const store = useAuthStore();
      const mockError = {
        response: {
          data: { error: 'Invalid password' }
        }
      };
      authApi.login.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      const result = await store.login('wrong');

      expect(consoleSpy).toHaveBeenCalledWith('Login error:', mockError);
      expect(store.isAuthenticated).toBe(false);
      expect(result).toEqual({ success: false, message: 'Invalid password' });
    });

    it('handles generic login error', async () => {
      const store = useAuthStore();
      const mockError = new Error('Network error');
      authApi.login.mockRejectedValue(mockError);

      vi.spyOn(console, 'error').mockImplementation(() => {});

      const result = await store.login('password');

      expect(store.isAuthenticated).toBe(false);
      expect(result).toEqual({ success: false, message: 'Ошибка входа. Проверьте пароль.' });
    });
  });

  describe('logout', () => {
    it('handles successful logout', async () => {
      localStorageMock.setItem('is_logged_in', 'true');
      const store = useAuthStore();

      authApi.logout.mockResolvedValue({ data: { success: true } });

      await store.logout();

      expect(authApi.logout).toHaveBeenCalled();
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('is_logged_in');
      expect(store.isAuthenticated).toBe(false);
    });

    it('handles logout error but still removes local state', async () => {
      localStorageMock.setItem('is_logged_in', 'true');
      const store = useAuthStore();

      const mockError = new Error('API Error');
      authApi.logout.mockRejectedValue(mockError);

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

      await store.logout();

      expect(consoleSpy).toHaveBeenCalledWith('Logout error', mockError);
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('is_logged_in');
      expect(store.isAuthenticated).toBe(false);
    });
  });
});

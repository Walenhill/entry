import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore } from './auth.js';
import { authApi } from '../api/index.js';

vi.mock('../api/index.js', () => ({
  authApi: {
    login: vi.fn(),
    logout: vi.fn()
  }
}));

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    global.localStorage = {
      getItem: vi.fn(),
      setItem: vi.fn(),
      removeItem: vi.fn()
    };
    vi.clearAllMocks();
  });

  afterEach(() => {
    delete global.localStorage;
  });

  it('initializes isAuthenticated from localStorage', () => {
    global.localStorage.getItem.mockReturnValue('true');
    const store = useAuthStore();
    expect(store.isAuthenticated).toBe(true);
    expect(global.localStorage.getItem).toHaveBeenCalledWith('is_logged_in');
  });

  it('login success sets isAuthenticated and localStorage', async () => {
    global.localStorage.getItem.mockReturnValue('false');
    const store = useAuthStore();

    authApi.login.mockResolvedValue({ data: { success: true } });

    const result = await store.login('password123');

    expect(result).toEqual({ success: true });
    expect(store.isAuthenticated).toBe(true);
    expect(global.localStorage.setItem).toHaveBeenCalledWith('is_logged_in', 'true');
  });

  it('login failure returns error message', async () => {
    global.localStorage.getItem.mockReturnValue('false');
    const store = useAuthStore();

    authApi.login.mockResolvedValue({ data: { success: false } });

    const result = await store.login('wrong');

    expect(result).toEqual({ success: false, message: 'Неверный ответ сервера' });
    expect(store.isAuthenticated).toBe(false);
  });

  it('login catches API error and extracts message', async () => {
    const store = useAuthStore();
    const mockError = new Error('Network error');

    authApi.login.mockRejectedValue(mockError);
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    const result = await store.login('password');

    expect(result).toEqual({ success: false, message: 'Network error' });
    expect(consoleSpy).toHaveBeenCalledWith('Login error:', mockError);
  });

  it('logout calls authApi.logout and clears state', async () => {
    global.localStorage.getItem.mockReturnValue('true');
    const store = useAuthStore();

    authApi.logout.mockResolvedValue({});

    await store.logout();

    expect(authApi.logout).toHaveBeenCalled();
    expect(store.isAuthenticated).toBe(false);
    expect(global.localStorage.removeItem).toHaveBeenCalledWith('is_logged_in');
  });

  it('logout clears state even if API fails', async () => {
    global.localStorage.getItem.mockReturnValue('true');
    const store = useAuthStore();

    const mockError = new Error('Logout failed');
    authApi.logout.mockRejectedValue(mockError);
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    await store.logout();

    expect(consoleSpy).toHaveBeenCalledWith('Logout error', mockError);
    expect(store.isAuthenticated).toBe(false);
    expect(global.localStorage.removeItem).toHaveBeenCalledWith('is_logged_in');
  });

  it('checkAuth updates isAuthenticated from localStorage', () => {
    global.localStorage.getItem.mockReturnValue('false');
    const store = useAuthStore();

    global.localStorage.getItem.mockReturnValue('true');
    store.checkAuth();

    expect(store.isAuthenticated).toBe(true);
  });
});

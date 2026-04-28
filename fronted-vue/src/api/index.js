import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Интерсептор для добавления токена авторизации
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export const slotsApi = {
  // Получить все слоты
  getAllSlots() {
    return apiClient.get('/slots');
  },

  // Получить слот по ID
  getSlotById(id) {
    return apiClient.get(`/slots/${id}`);
  },

  // Создать новый слот
  createSlot(slotData) {
    return apiClient.post('/slots', slotData);
  },

  // Забронировать слот
  bookSlot(id, bookingData) {
    return apiClient.post(`/slots/${id}/book`, bookingData);
  },

  // Отменить бронь
  cancelBooking(id) {
    return apiClient.delete(`/slots/${id}/book`);
  },

  // Удалить слот
  deleteSlot(id) {
    return apiClient.delete(`/slots/${id}`);
  },
};

export const authApi = {
  // Войти в систему
  login(password) {
    return apiClient.post('/auth/login', { password });
  },

  // Выйти из системы
  logout() {
    localStorage.removeItem('auth_token');
  },
};

export default apiClient;
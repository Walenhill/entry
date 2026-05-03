import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('is_logged_in');
      // optionally trigger reload or router redirect if using vue-router
      window.location.reload();
    }
    return Promise.reject(error);
  }
);

export const slotsApi = {
  // Получить все слоты (для клиента - только свободные, для админа - все)
  getAllSlots(date = null, role = 'client') {
    const params = {};
    if (date) params.date = date;
    if (role) params.role = role;
    return apiClient.get('/slots', { params });
  },

  // Создать новый слот (только админ)
  createSlot(slotData) {
    return apiClient.post('/slots', slotData);
  },

  // Сгенерировать слоты по шаблону (только админ)
  generateSlots(templateData) {
    return apiClient.post('/slots/generate', templateData);
  },

  // Забронировать слот (клиент)
  bookSlot(id, bookingData) {
    return apiClient.post(`/slots/${id}/book`, bookingData);
  },

  // Отменить бронь (только админ)
  cancelBooking(id) {
    return apiClient.post(`/slots/${id}/cancel`);
  },

  // Удалить слот (только админ)
  deleteSlot(id) {
    return apiClient.delete(`/slots/${id}`);
  },

  // Получить статистику (только админ)
  getStats() {
    return apiClient.get('/stats');
  },
};

export const directoryApi = {
  getServices() {
    return apiClient.get('/services');
  },
  getStaff() {
    return apiClient.get('/staff');
  }
};

export const authApi = {
  // Войти в систему
  login(password) {
    return apiClient.post('/auth/login', { password });
  },

  // Выйти из системы
  logout() {
    localStorage.removeItem('is_logged_in');
    return apiClient.post('/auth/logout');
  },
  
  // Проверка аутентификации
  isAuthenticated() {
    return localStorage.getItem('is_logged_in') === 'true';
  },
};

export default apiClient;
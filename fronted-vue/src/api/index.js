import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true, // Enable cookies for session auth
});

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

export const authApi = {
  // Войти в систему
  async login(password) {
    const response = await apiClient.post('/auth/login', { password });
    return response.data.success === true;
  },

  // Выйти из системы
  async logout() {
    try {
      await apiClient.post('/auth/logout');
    } catch (e) {
      // Ignore errors on logout
    }
  },
  
  // Проверка аутентификации через проверку сессии на бэкенде
  async isAuthenticated() {
    try {
      // Пытаемся получить слоты как админ - если успешно, значит авторизован
      await apiClient.get('/slots', { params: { role: 'admin' } });
      return true;
    } catch (e) {
      return false;
    }
  },
};

export default apiClient;
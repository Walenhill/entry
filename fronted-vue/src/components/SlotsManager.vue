<template>
  <div class="slots-container">
    <header class="header">
      <h1>Управление слотами</h1>
      <div class="header-actions">
        <button @click="showStats = !showStats" class="btn-stats">📊 Статистика</button>
        <button @click="handleLogout" class="logout-btn">Выйти</button>
      </div>
    </header>

    <!-- Модальное окно статистики -->
    <div v-if="showStats" class="modal-overlay" @click.self="showStats = false">
      <div class="modal-content">
        <h2>📊 Статистика</h2>
        <button @click="showStats = false" class="close-btn">×</button>
        
        <div v-if="statsLoading" class="loading">Загрузка...</div>
        <div v-else-if="statsError" class="error">{{ statsError }}</div>
        <div v-else-if="statsData" class="stats-body">
          <div class="stats-cards">
            <div class="stat-card">
              <div class="stat-value">{{ statsData.summary.total }}</div>
              <div class="stat-label">Всего слотов</div>
            </div>
            <div class="stat-card booked">
              <div class="stat-value">{{ statsData.summary.booked }}</div>
              <div class="stat-label">Забронировано</div>
            </div>
            <div class="stat-card available">
              <div class="stat-value">{{ statsData.summary.available }}</div>
              <div class="stat-label">Свободно</div>
            </div>
            <div class="stat-card cancelled">
              <div class="stat-value">{{ statsData.summary.cancelled }}</div>
              <div class="stat-label">Отменено</div>
            </div>
          </div>

          <div class="load-section">
            <h3>Загрузка: {{ statsData.load_percentage }}%</h3>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: statsData.load_percentage + '%' }"></div>
            </div>
          </div>

          <div class="top-clients">
            <h3>🏆 Топ клиентов</h3>
            <div v-if="!statsData.top_clients || statsData.top_clients.length === 0" class="no-data">Нет данных</div>
            <table v-else class="clients-table">
              <thead>
                <tr>
                  <th>Имя</th>
                  <th>Телефон</th>
                  <th>Визитов</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(client, index) in statsData.top_clients" :key="index">
                  <td>{{ client.client_name }}</td>
                  <td>{{ client.client_phone }}</td>
                  <td><strong>{{ client.visits }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="controls">
      <button @click="showCreateForm = !showCreateForm" class="btn-primary">
        {{ showCreateForm ? 'Отмена' : '+ Создать слот' }}
      </button>
    </div>

    <!-- Форма создания слота -->
    <div v-if="showCreateForm" class="create-form">
      <h3>Новый слот</h3>
      <form @submit.prevent="createSlot">
        <div class="form-row">
          <div class="form-group">
            <label>Дата:</label>
            <input type="date" v-model="newSlot.date" required />
          </div>
          <div class="form-group">
            <label>Время начала:</label>
            <input type="time" v-model="newSlot.start_time" required />
          </div>
          <div class="form-group">
            <label>Время конца:</label>
            <input type="time" v-model="newSlot.end_time" required />
          </div>
        </div>
        <div class="form-group">
          <label>Описание:</label>
          <input type="text" v-model="newSlot.description" placeholder="Описание слота" />
        </div>
        <button type="submit" :disabled="isCreating" class="btn-success">
          {{ isCreating ? 'Создание...' : 'Создать' }}
        </button>
      </form>
    </div>

    <!-- Список слотов -->
    <div v-if="isLoading" class="loading">Загрузка...</div>
    
    <div v-else-if="slots.length === 0" class="empty-state">
      <p>Слоты не найдены. Создайте первый слот!</p>
    </div>

    <div v-else class="slots-grid">
      <div v-for="slot in slots" :key="slot.id" class="slot-card" :class="{ booked: slot.is_booked }">
        <div class="slot-header">
          <h3>{{ slot.date }}</h3>
          <span class="status-badge" :class="slot.is_booked ? 'booked' : 'available'">
            {{ slot.is_booked ? 'Забронирован' : 'Свободен' }}
          </span>
        </div>
        
        <div class="slot-time">
          {{ slot.start_time }} - {{ slot.end_time }}
        </div>
        
        <p v-if="slot.description" class="slot-description">{{ slot.description }}</p>
        
        <div v-if="slot.booked_by" class="booking-info">
          <strong>Забронировал:</strong> {{ slot.booked_by }}
          <p v-if="slot.booking_comment"><strong>Комментарий:</strong> {{ slot.booking_comment }}</p>
        </div>

        <div class="slot-actions">
          <button 
            v-if="!slot.is_booked" 
            @click="openBookingModal(slot)" 
            class="btn-book"
          >
            Забронировать
          </button>
          <button 
            v-else 
            @click="cancelBooking(slot.id)" 
            class="btn-cancel"
          >
            Отменить бронь
          </button>
          <button @click="deleteSlot(slot.id)" class="btn-delete">
            Удалить
          </button>
        </div>
      </div>
    </div>

    <!-- Модальное окно бронирования -->
    <div v-if="showBookingModal" class="modal-overlay" @click="closeBookingModal">
      <div class="modal" @click.stop>
        <h3>Бронирование слота</h3>
        <p class="modal-info">
          {{ selectedSlot?.date }} | {{ selectedSlot?.start_time }} - {{ selectedSlot?.end_time }}
        </p>
        <form @submit.prevent="bookSlot">
          <div class="form-group">
            <label>Ваше имя:</label>
            <input type="text" v-model="bookingData.name" required placeholder="Иван Иванов" />
          </div>
          <div class="form-group">
            <label>Комментарий:</label>
            <textarea v-model="bookingData.comment" rows="3" placeholder="Дополнительная информация"></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeBookingModal" class="btn-secondary">Отмена</button>
            <button type="submit" :disabled="isBooking" class="btn-primary">
              {{ isBooking ? 'Бронирование...' : 'Подтвердить' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { slotsApi } from '../api';

const slots = ref([]);
const isLoading = ref(false);
const showCreateForm = ref(false);
const isCreating = ref(false);
const showBookingModal = ref(false);
const isBooking = ref(false);
const selectedSlot = ref(null);
const showStats = ref(false);
const statsData = ref(null);
const statsLoading = ref(false);
const statsError = ref(null);

const newSlot = ref({
  date: '',
  start_time: '',
  end_time: '',
  description: ''
});

const bookingData = ref({
  name: '',
  comment: ''
});

const emit = defineEmits(['logout']);

// Загрузка статистики
const loadStats = async () => {
  statsLoading.value = true;
  statsError.value = null;
  try {
    const response = await slotsApi.getStats();
    if (response.data.success) {
      statsData.value = response.data.data;
    } else {
      statsError.value = 'Ошибка получения данных';
    }
  } catch (err) {
    console.error('Stats error:', err);
    statsError.value = err.response?.data?.error || 'Ошибка подключения';
  } finally {
    statsLoading.value = false;
  }
};

// Показ статистики с загрузкой
const toggleStats = () => {
  showStats.value = !showStats.value;
  if (showStats.value && !statsData.value) {
    loadStats();
  }
};

// Загрузка слотов
const loadSlots = async () => {
  isLoading.value = true;
  try {
    const response = await slotsApi.getAllSlots();
    slots.value = response.data.slots || [];
  } catch (error) {
    console.error('Error loading slots:', error);
    alert('Ошибка загрузки слотов');
  } finally {
    isLoading.value = false;
  }
};

// Создание слота
const createSlot = async () => {
  isCreating.value = true;
  try {
    await slotsApi.createSlot(newSlot.value);
    showCreateForm.value = false;
    newSlot.value = { date: '', start_time: '', end_time: '', description: '' };
    await loadSlots();
  } catch (error) {
    console.error('Error creating slot:', error);
    alert('Ошибка создания слота: ' + (error.response?.data?.message || error.message));
  } finally {
    isCreating.value = false;
  }
};

// Открытие модального окна бронирования
const openBookingModal = (slot) => {
  selectedSlot.value = slot;
  bookingData.value = { name: '', comment: '' };
  showBookingModal.value = true;
};

// Закрытие модального окна
const closeBookingModal = () => {
  showBookingModal.value = false;
  selectedSlot.value = null;
};

// Бронирование слота
const bookSlot = async () => {
  if (!selectedSlot.value) return;
  
  isBooking.value = true;
  try {
    await slotsApi.bookSlot(selectedSlot.value.id, {
      name: bookingData.value.name,
      comment: bookingData.value.comment
    });
    closeBookingModal();
    await loadSlots();
  } catch (error) {
    console.error('Error booking slot:', error);
    alert('Ошибка бронирования: ' + (error.response?.data?.message || error.message));
  } finally {
    isBooking.value = false;
  }
};

// Отмена бронирования
const cancelBooking = async (slotId) => {
  if (!confirm('Вы уверены, что хотите отменить бронь?')) return;
  
  try {
    await slotsApi.cancelBooking(slotId);
    await loadSlots();
  } catch (error) {
    console.error('Error canceling booking:', error);
    alert('Ошибка отмены брони: ' + (error.response?.data?.message || error.message));
  }
};

// Удаление слота
const deleteSlot = async (slotId) => {
  if (!confirm('Вы уверены, что хотите удалить этот слот?')) return;
  
  try {
    await slotsApi.deleteSlot(slotId);
    await loadSlots();
  } catch (error) {
    console.error('Error deleting slot:', error);
    alert('Ошибка удаления слота: ' + (error.response?.data?.message || error.message));
  }
};

// Выход
const handleLogout = () => {
  emit('logout');
};

onMounted(() => {
  loadSlots();
});
</script>

<style scoped>
.slots-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #eee;
}

.header h1 {
  color: #333;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.btn-stats {
  padding: 10px 20px;
  background-color: #9b59b6;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  font-size: 14px;
  transition: background 0.2s;
}

.btn-stats:hover {
  background-color: #8e44ad;
}

.logout-btn {
  padding: 10px 20px;
  background-color: #e74c3c;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.logout-btn:hover {
  background-color: #c0392b;
}

.controls {
  margin-bottom: 20px;
}

.btn-primary {
  padding: 12px 24px;
  background-color: #42b983;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s;
}

.btn-primary:hover {
  background-color: #369970;
}

.create-form {
  background: white;
  padding: 25px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.create-form h3 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #333;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 15px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #555;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #42b983;
  box-shadow: 0 0 0 2px rgba(66, 185, 131, 0.2);
}

.btn-success {
  padding: 12px 24px;
  background-color: #27ae60;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}

.btn-success:hover {
  background-color: #219a52;
}

.loading,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
}

.slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.slot-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  border-left: 4px solid #42b983;
}

.slot-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.slot-card.booked {
  border-left-color: #e74c3c;
  opacity: 0.9;
}

.slot-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.slot-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.available {
  background-color: #d4edda;
  color: #155724;
}

.status-badge.booked {
  background-color: #f8d7da;
  color: #721c24;
}

.slot-time {
  font-size: 16px;
  color: #42b983;
  font-weight: 600;
  margin-bottom: 10px;
}

.slot-description {
  color: #666;
  margin: 10px 0;
  font-size: 14px;
}

.booking-info {
  background-color: #f8f9fa;
  padding: 10px;
  border-radius: 4px;
  margin: 10px 0;
  font-size: 14px;
}

.booking-info strong {
  color: #333;
}

.slot-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.slot-actions button {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.btn-book {
  background-color: #42b983;
  color: white;
}

.btn-book:hover {
  background-color: #369970;
}

.btn-cancel {
  background-color: #f39c12;
  color: white;
}

.btn-cancel:hover {
  background-color: #e67e22;
}

.btn-delete {
  background-color: #e74c3c;
  color: white;
}

.btn-delete:hover {
  background-color: #c0392b;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 8px;
  max-width: 700px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  position: relative;
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  font-size: 32px;
  cursor: pointer;
  color: #999;
  line-height: 1;
}

.close-btn:hover {
  color: #333;
}

.stats-body {
  margin-top: 20px;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 15px;
  margin-bottom: 25px;
}

.stat-card {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.stat-value {
  font-size: 32px;
  font-weight: bold;
  color: #3498db;
  margin-bottom: 5px;
}

.stat-label {
  color: #7f8c8d;
  font-size: 12px;
  text-transform: uppercase;
}

.stat-card.booked .stat-value {
  color: #27ae60;
}

.stat-card.available .stat-value {
  color: #3498db;
}

.stat-card.cancelled .stat-value {
  color: #e74c3c;
}

.load-section {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 25px;
}

.load-section h3 {
  margin-top: 0;
  text-align: center;
  color: #2c3e50;
}

.progress-bar {
  height: 25px;
  background: #ecf0f1;
  border-radius: 12px;
  overflow: hidden;
  margin-top: 10px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3498db, #27ae60);
  transition: width 0.5s ease;
}

.top-clients {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
}

.top-clients h3 {
  margin-top: 0;
  text-align: center;
  color: #2c3e50;
}

.no-data {
  text-align: center;
  color: #95a5a6;
  padding: 20px;
}

.clients-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.clients-table th,
.clients-table td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.clients-table th {
  background: #ecf0f1;
  color: #2c3e50;
  font-weight: 600;
  font-size: 13px;
}

.clients-table tr:last-child td {
  border-bottom: none;
}

.modal {
  background: white;
  padding: 30px;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.modal h3 {
  margin-top: 0;
  margin-bottom: 10px;
  color: #333;
}

.modal-info {
  color: #666;
  margin-bottom: 20px;
  font-size: 14px;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn-secondary {
  padding: 10px 20px;
  background-color: #95a5a6;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-secondary:hover {
  background-color: #7f8c8d;
}
</style>

<template>
  <div class="stats-view">
    <div class="page-header mb-4">
      <h1>Статистика системы</h1>
      <p class="text-muted">Обзор загруженности и активности клиентов</p>
    </div>

    <div v-if="slotsStore.statsLoading" class="state-container">
      <div class="loader"></div>
      <p class="mt-3 text-muted">Загрузка статистики...</p>
    </div>

    <div v-else-if="slotsStore.statsError" class="state-container error-state">
      <p class="text-danger">{{ slotsStore.statsError }}</p>
      <button class="btn btn-outline mt-3" @click="slotsStore.fetchStats()">Повторить попытку</button>
    </div>

    <div v-else-if="slotsStore.stats" class="stats-content">
      <!-- Cards -->
      <div class="stats-cards mb-4">
        <div class="card stat-card">
          <div class="stat-icon info">📋</div>
          <div class="stat-details">
            <span class="stat-value">{{ slotsStore.stats.summary.total }}</span>
            <span class="stat-label">Всего слотов</span>
          </div>
        </div>

        <div class="card stat-card">
          <div class="stat-icon success">✓</div>
          <div class="stat-details">
            <span class="stat-value text-success">{{ slotsStore.stats.summary.booked }}</span>
            <span class="stat-label">Забронировано</span>
          </div>
        </div>

        <div class="card stat-card">
          <div class="stat-icon primary">⏳</div>
          <div class="stat-details">
            <span class="stat-value" style="color: var(--accent-secondary)">{{ slotsStore.stats.summary.available }}</span>
            <span class="stat-label">Свободно</span>
          </div>
        </div>

        <div class="card stat-card">
          <div class="stat-icon danger">✕</div>
          <div class="stat-details">
            <span class="stat-value text-danger">{{ slotsStore.stats.summary.cancelled }}</span>
            <span class="stat-label">Отменено</span>
          </div>
        </div>
      </div>

      <!-- Load Bar -->
      <div class="card mb-4">
        <h3 class="mb-3">Загруженность расписания</h3>
        <div class="progress-wrapper">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: `${slotsStore.stats.occupancy_rate}%` }"></div>
          </div>
          <span class="progress-text">{{ slotsStore.stats.occupancy_rate }}% заполнено</span>
        </div>
      </div>

      <!-- Top Clients Table -->
      <div class="card">
        <h3 class="mb-3">Активные клиенты</h3>

        <div class="table-responsive">
          <table class="table" v-if="slotsStore.stats.top_clients && slotsStore.stats.top_clients.length > 0">
            <thead>
              <tr>
                <th>Имя клиента</th>
                <th>Телефон</th>
                <th>Визитов</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(client, index) in slotsStore.stats.top_clients" :key="index">
                <td>{{ client.client_name }}</td>
                <td>{{ client.client_phone }}</td>
                <td><span class="badge badge-info">{{ client.visits }}</span></td>
              </tr>
            </tbody>
          </table>
          <div v-else class="text-center p-4 text-muted">
            Нет данных о клиентах
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useSlotsStore } from '../store/slots';

const slotsStore = useSlotsStore();

onMounted(() => {
  slotsStore.fetchStats();
});
</script>

<style scoped>
.page-header h1 {
  margin: 0 0 0.5rem 0;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--border-radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
}

.stat-icon.info { background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.stat-icon.success { background-color: var(--status-success-bg); color: var(--status-success); }
.stat-icon.primary { background-color: rgba(139, 109, 190, 0.1); color: var(--accent-secondary); }
.stat-icon.danger { background-color: var(--status-danger-bg); color: var(--status-danger); }

.stat-details {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.progress-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.progress-bar {
  flex: 1;
  height: 24px;
  background-color: var(--bg-main);
  border-radius: 9999px;
  overflow: hidden;
  border: 1px solid var(--border-color);
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent-secondary), var(--accent-primary));
  transition: width 0.5s ease-out;
}

.progress-text {
  font-weight: 600;
  color: var(--text-secondary);
  min-width: 100px;
}

.table-responsive {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th, .table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

.table th {
  font-weight: 600;
  color: var(--text-secondary);
  background-color: rgba(255, 255, 255, 0.02);
}

.table tr:last-child td {
  border-bottom: none;
}

.badge-info {
  background-color: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  background-color: var(--bg-surface);
  border-radius: var(--border-radius-lg);
  border: 1px dashed var(--border-color);
  text-align: center;
}

.loader {
  border: 4px solid var(--border-color);
  border-top: 4px solid var(--accent-secondary);
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .progress-wrapper {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

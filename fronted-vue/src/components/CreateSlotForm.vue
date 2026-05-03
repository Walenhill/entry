<template>
  <div class="card create-form">
    <h3 class="mb-3">Создать новый слот</h3>
    <div v-if="directoryStore.isLoading" class="mb-3 text-muted">Загрузка справочников...</div>
    <form v-else @submit.prevent="handleSubmit">
      <div class="form-row mb-3">
        <div class="form-group">
          <label for="staff">Специалист</label>
          <select id="staff" v-model="form.staff_id" required>
            <option v-for="person in directoryStore.staff" :key="person.id" :value="person.id">
              {{ person.name }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label for="service">Услуга</label>
          <select id="service" v-model="form.service_id" required>
            <option v-for="srv in directoryStore.services" :key="srv.id" :value="srv.id">
              {{ srv.name }} ({{ srv.duration_minutes }} мин, {{ srv.price }} ₽)
            </option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="date">Дата</label>
          <input type="date" id="date" v-model="form.date" required />
        </div>
        <div class="form-group">
          <label for="start_time">Начало</label>
          <input type="time" id="start_time" v-model="form.start_time" required />
        </div>
        <div class="form-group">
          <label for="end_time">Конец</label>
          <input type="time" id="end_time" v-model="form.end_time" required />
        </div>
      </div>

      <div class="form-group mb-3 mt-3">
        <label for="description">Описание (опционально)</label>
        <textarea id="description" v-model="form.description" rows="2" placeholder="Например: Стрижка бороды"></textarea>
      </div>

      <div class="form-actions justify-end">
        <button type="button" class="btn btn-outline mr-2" @click="$emit('cancel')">Отмена</button>
        <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
          {{ isSubmitting ? 'Создание...' : 'Добавить слот' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useDirectoryStore } from '../store/directory';

const directoryStore = useDirectoryStore();

const props = defineProps({
  isSubmitting: Boolean
});

const emit = defineEmits(['submit', 'cancel']);

// Get tomorrow's date as default
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const defaultDate = tomorrow.toISOString().split('T')[0];

const form = ref({
  staff_id: 1,
  service_id: 1,
  date: defaultDate,
  start_time: '10:00',
  end_time: '11:00',
  description: ''
});

onMounted(async () => {
  await directoryStore.fetchDirectories();
  if (directoryStore.staff.length > 0) {
    form.value.staff_id = directoryStore.staff[0].id;
  }
  if (directoryStore.services.length > 0) {
    form.value.service_id = directoryStore.services[0].id;
  }
});

const handleSubmit = () => {
  emit('submit', form.value);
};
</script>

<style scoped>
.create-form {
  margin-bottom: 2rem;
  border-left: 4px solid var(--accent-secondary);
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
}

.mr-2 {
  margin-right: 0.5rem;
}
</style>

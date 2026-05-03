<template>
  <div class="card create-form">
    <h3 class="mb-3">Создать новый слот</h3>
    <form @submit.prevent="handleSubmit">
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
import { ref } from 'vue';

const props = defineProps({
  isSubmitting: Boolean
});

const emit = defineEmits(['submit', 'cancel']);

// Get tomorrow's date as default
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const defaultDate = tomorrow.toISOString().split('T')[0];

const form = ref({
  date: defaultDate,
  start_time: '10:00',
  end_time: '11:00',
  description: ''
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

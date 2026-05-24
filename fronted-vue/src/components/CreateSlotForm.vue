<template>
  <div class="card create-form">
    <h3 class="mb-3">Создать новый слот</h3>
    <form @submit.prevent="handleSubmit">
      <div class="form-row">
        <div class="form-group">
          <label for="date">Дата <span class="text-danger">*</span></label>
          <input type="date" id="date" ref="dateInput" v-model="form.date" :min="today" required />
        </div>
        <div class="form-group">
          <label for="start_time">Начало <span class="text-danger">*</span></label>
          <input type="time" id="start_time" v-model="form.start_time" required :aria-invalid="!!timeError" :aria-describedby="timeError ? 'time-error' : null" @input="timeError = ''" />
        </div>
        <div class="form-group">
          <label for="end_time">Конец <span class="text-danger">*</span></label>
          <input type="time" id="end_time" v-model="form.end_time" required :aria-invalid="!!timeError" :aria-describedby="timeError ? 'time-error' : null" @input="timeError = ''" />
        </div>
      </div>

      <div v-if="timeError" id="time-error" class="error-message mb-3 mt-2" role="alert" aria-live="assertive">
        {{ timeError }}
      </div>

      <div class="form-group mb-3 mt-3">
        <label for="description">Описание (опционально)</label>
        <textarea
          id="description"
          v-model="form.description"
          rows="2"
          placeholder="Например: Стрижка бороды"
          maxlength="255"
          aria-describedby="desc-counter"
        ></textarea>
        <div id="desc-counter" class="text-muted" style="font-size: 0.75rem; text-align: right; margin-top: 0.25rem;">
          {{ form.description.length }} / 255
        </div>
      </div>

      <div class="form-actions justify-end">
        <button type="button" class="btn btn-outline mr-2" @click="$emit('cancel')" :disabled="isSubmitting">Отмена</button>
        <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
          <span v-if="isSubmitting" class="spinner-small" aria-hidden="true"></span>
          {{ isSubmitting ? 'Создание...' : 'Добавить слот' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  isSubmitting: Boolean
});

const emit = defineEmits(['submit', 'cancel']);

// Get tomorrow's date as default
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const defaultDate = tomorrow.toISOString().split('T')[0];

const today = new Date().toISOString().split('T')[0];

const dateInput = ref(null);

onMounted(() => {
  if (dateInput.value) {
    dateInput.value.focus();
  }
});

const form = ref({
  date: defaultDate,
  start_time: '10:00',
  end_time: '11:00',
  description: ''
});

const timeError = ref('');

const handleSubmit = () => {
  timeError.value = '';
  if (form.value.start_time >= form.value.end_time) {
    timeError.value = 'Время начала должно быть раньше времени окончания';
    return;
  }
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

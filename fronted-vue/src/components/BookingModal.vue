<template>
  <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Бронирование слота</h3>
        <button class="close-btn" @click="$emit('close')">×</button>
      </div>

      <div v-if="slot" class="modal-info mb-4">
        <p><strong>Дата:</strong> {{ slot.date }}</p>
        <p><strong>Время:</strong> {{ slot.start_time }} - {{ slot.end_time }}</p>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="form-group mb-3">
          <label for="clientName">Имя клиента <span class="text-danger">*</span></label>
          <input
            type="text"
            id="clientName"
            v-model="formData.name"
            required
            placeholder="Введите имя"
          />
        </div>

        <div class="form-group mb-4">
          <label for="clientPhone">Телефон <span class="text-danger">*</span></label>
          <input
            type="text"
            id="clientPhone"
            v-model="formData.phone"
            required
            placeholder="Введите номер телефона"
          />
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-outline" @click="$emit('close')">Отмена</button>
          <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
            {{ isSubmitting ? 'Сохранение...' : 'Подтвердить бронь' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  slot: Object,
  isSubmitting: Boolean
});

const emit = defineEmits(['close', 'submit']);

const formData = ref({
  name: '',
  phone: ''
});

// Reset form when modal opens
watch(() => props.show, (newVal) => {
  if (newVal) {
    formData.value = { name: '', phone: '' };
  }
});

const handleSubmit = () => {
  emit('submit', formData.value);
};
</script>

<style scoped>
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h3 {
  margin: 0;
  color: var(--text-primary);
}

.close-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  font-size: 1.5rem;
  cursor: pointer;
  line-height: 1;
}

.close-btn:hover {
  color: var(--text-primary);
}

.modal-info {
  background-color: var(--bg-main);
  padding: 1rem;
  border-radius: var(--border-radius-md);
  border: 1px solid var(--border-color);
}

.modal-info p {
  margin-bottom: 0.5rem;
  color: var(--text-secondary);
}

.modal-info p:last-child {
  margin-bottom: 0;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}
</style>

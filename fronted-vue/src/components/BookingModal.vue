<template>
  <div v-if="show" class="modal-overlay" @click.self="!isSubmitting && $emit('close')">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="booking-modal-title">
      <div class="modal-header">
        <h3 id="booking-modal-title">Бронирование слота</h3>
        <span :title="isSubmitting ? 'Действие недоступно во время загрузки' : 'Закрыть (Esc)'" style="display: inline-flex;">
          <button class="close-btn" @click="$emit('close')" :disabled="isSubmitting" :aria-label="isSubmitting ? 'Закрыть (Esc) - Действие недоступно во время загрузки' : 'Закрыть (Esc)'">×</button>
        </span>
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
            ref="nameInput"
            v-model="formData.name"
            required
            autocomplete="name"
            placeholder="Введите имя"
            maxlength="100"
            aria-describedby="name-counter"
          />
          <div id="name-counter" class="text-muted" style="font-size: 0.75rem; text-align: right; margin-top: 0.25rem;">
            {{ formData.name.length }} / 100
          </div>
        </div>

        <div class="form-group mb-4">
          <label for="clientPhone">Телефон <span class="text-danger">*</span></label>
          <input
            type="tel"
            id="clientPhone"
            v-model="formData.phone"
            required
            autocomplete="tel"
            placeholder="Введите номер телефона"
            maxlength="20"
            aria-describedby="phone-counter"
          />
          <div id="phone-counter" class="text-muted" style="font-size: 0.75rem; text-align: right; margin-top: 0.25rem;">
            {{ formData.phone.length }} / 20
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-outline" @click="$emit('close')" :disabled="isSubmitting">Отмена</button>
          <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="spinner-small" aria-hidden="true"></span>
            {{ isSubmitting ? 'Сохранение...' : 'Подтвердить бронь' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';

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

const nameInput = ref(null);
let previousActiveElement = null;

// Reset form when modal opens
watch(() => props.show, async (newVal) => {
  if (newVal) {
    previousActiveElement = document.activeElement;
    formData.value = { name: '', phone: '' };
    await nextTick();
    if (nameInput.value) {
      nameInput.value.focus();
    }
  } else {
    await nextTick();
    if (previousActiveElement) {
      previousActiveElement.focus();
    }
  }
});

const handleSubmit = () => {
  emit('submit', formData.value);
};

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show && !props.isSubmitting) {
    emit('close');
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
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
  min-width: 48px;
  min-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
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

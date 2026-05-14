<template>
  <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Бронирование слота</h3>
        <button class="close-btn" @click="$emit('close')" aria-label="Закрыть">×</button>
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
          />
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
          />
        </div>

        <div class="modal-actions" v-if="!isTelegramEnv">
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
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { isTMA, showMainButton, hideMainButton, getUser } from '../utils/telegram';

const props = defineProps({
  show: Boolean,
  slot: Object,
  isSubmitting: Boolean
});

const isTelegramEnv = isTMA();

const emit = defineEmits(['close', 'submit']);

const formData = ref({
  name: '',
  phone: ''
});

const nameInput = ref(null);

// Reset form when modal opens
watch(() => props.show, async (newVal) => {
  if (newVal) {
    formData.value = { name: '', phone: '' };

    if (isTelegramEnv) {
        const tgUser = getUser();
        if (tgUser && tgUser.first_name) {
            formData.value.name = tgUser.last_name ? `${tgUser.first_name} ${tgUser.last_name}` : tgUser.first_name;
        }
        showMainButton('Забронировать', handleTelegramSubmit);
    }

    await nextTick();
    if (nameInput.value && !isTelegramEnv) {
      nameInput.value.focus();
    }
  } else {
    if (isTelegramEnv) {
        hideMainButton(handleTelegramSubmit);
    }
  }
});

const handleSubmit = () => {
  if (isTelegramEnv) {
      // Prevent double submit if triggered by enter key
      return;
  }
  emit('submit', formData.value);
};

const handleTelegramSubmit = () => {
    // Basic validation before submitting via TG button
    if (!formData.value.name || !formData.value.phone) {
        alert("Пожалуйста, заполните имя и телефон");
        return;
    }
    emit('submit', formData.value);
}

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show) {
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

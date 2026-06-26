<template>
  <div class="card slot-card" :class="`status-${statusClass}`">
    <div class="slot-header">
      <div class="time-block">
        <span class="time">{{ slot.start_time }} - {{ slot.end_time }}</span>
        <span class="date">{{ slot.date }}</span>
      </div>
      <span class="badge" :class="`badge-${statusClass}`">{{ statusText }}</span>
    </div>

    <div class="slot-body">
      <p v-if="slot.description" class="description text-muted">{{ slot.description }}</p>

      <div v-if="slot.is_booked" class="booking-details">
        <div class="detail-item">
          <span class="label">Клиент:</span>
          <span class="value">{{ slot.booked_by }}</span>
        </div>
        <div class="detail-item" v-if="slot.booking_comment">
          <span class="label">Телефон:</span>
          <a :href="`tel:${slot.booking_comment}`" class="value phone-link" :aria-label="`Позвонить клиенту: ${slot.booking_comment}`">{{ slot.booking_comment }}</a>
        </div>
      </div>
    </div>

    <div class="slot-footer">
      <template v-if="!slot.is_booked">
        <button @click="$emit('book', slot)" class="btn btn-primary flex-1" :aria-label="`Забронировать слот на ${slot.date} с ${slot.start_time} до ${slot.end_time}`" :disabled="isCanceling || isDeleting">Забронировать</button>
      </template>
      <template v-else>
        <button @click="$emit('cancel', slot.id)" class="btn btn-warning flex-1" :aria-label="`Отменить бронь на ${slot.date} с ${slot.start_time} до ${slot.end_time}`" :disabled="isCanceling || isDeleting">
          <span v-if="isCanceling" class="spinner-small" aria-hidden="true"></span>
          {{ isCanceling ? 'Отмена...' : 'Отменить бронь' }}
        </button>
      </template>
      <span style="display: inline-flex;" :title="isDeleting || isCanceling ? 'Действие недоступно во время загрузки' : 'Удалить слот'">
        <button @click="$emit('delete', slot.id)" class="btn btn-outline btn-icon" :aria-label="`Удалить слот на ${slot.date} с ${slot.start_time} до ${slot.end_time}${isDeleting || isCanceling ? ' - Действие недоступно во время загрузки' : ''}`" :disabled="isDeleting || isCanceling">
          <span v-if="isDeleting" class="spinner-small" aria-hidden="true"></span>
          <span v-else aria-hidden="true">🗑</span>
        </button>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  slot: {
    type: Object,
    required: true
  },
  isCanceling: {
    type: Boolean,
    default: false
  },
  isDeleting: {
    type: Boolean,
    default: false
  }
});

defineEmits(['book', 'cancel', 'delete']);

const statusClass = computed(() => props.slot.is_booked ? 'booked' : 'available');
const statusText = computed(() => props.slot.is_booked ? 'Забронировано' : 'Свободно');
</script>

<style scoped>
.slot-card {
  display: flex;
  flex-direction: column;
  transition: transform 0.2s, box-shadow 0.2s;
  border-left: 4px solid var(--status-success);
}

.slot-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.status-booked {
  border-left-color: var(--status-danger);
}

.slot-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.time-block {
  display: flex;
  flex-direction: column;
}

.time {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
}

.date {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-available {
  background-color: var(--status-success-bg);
  color: var(--status-success);
}

.badge-booked {
  background-color: var(--status-danger-bg);
  color: var(--status-danger);
}

.slot-body {
  flex: 1;
  margin-bottom: 1rem;
}

.description {
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.booking-details {
  background-color: var(--bg-main);
  padding: 0.75rem;
  border-radius: var(--border-radius-sm);
  border: 1px solid var(--border-color);
}

.detail-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
}

.detail-item:not(:last-child) {
  margin-bottom: 0.25rem;
}

.detail-item .label {
  color: var(--text-muted);
}

.detail-item .value {
  color: var(--text-primary);
  font-weight: 500;
}

.slot-footer {
  display: flex;
  gap: 0.5rem;
}

.flex-1 {
  flex: 1;
}

.btn-icon {
  padding: 0.625rem;
  width: 48px;
  min-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

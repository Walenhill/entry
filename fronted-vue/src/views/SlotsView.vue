<template>
  <div class="slots-view">
    <div class="page-header flex justify-between items-center mb-4">
      <h1>Управление слотами</h1>
      <button
        v-show="!showCreateForm"
        @click="openCreateForm"
        class="btn btn-primary"
      >
        + Создать слот
      </button>
    </div>

    <!-- Форма создания -->
    <CreateSlotForm
      v-if="showCreateForm"
      :is-submitting="isCreating"
      @submit="handleCreateSlot"
      @cancel="closeCreateForm"
    />

    <!-- Фильтр (опционально, пока просто заголовок) -->
    <div class="mb-4">
      <h3 class="text-secondary">Все слоты</h3>
    </div>

    <!-- Состояния -->
    <div v-if="slotsStore.isLoading" class="state-container" role="status" aria-live="polite">
      <div class="loader" aria-hidden="true"></div>
      <p class="mt-3 text-muted">Загрузка слотов...</p>
    </div>

    <div v-else-if="slotsStore.slots.length === 0" class="state-container empty-state" role="status" aria-live="polite">
      <div class="empty-icon" aria-hidden="true">📅</div>
      <h3 class="mt-3">Нет доступных слотов</h3>
      <p class="text-muted mb-4">Создайте новый слот, чтобы он появился здесь.</p>
      <button class="btn btn-primary" @click="openCreateForm" v-show="!showCreateForm">
        + Создать слот
      </button>
    </div>

    <!-- Сетка слотов -->
    <div v-else class="slots-grid">
      <!-- Performance optimization: use v-memo to prevent O(N) re-renders
           when unrelated parent state changes (e.g. cancelingSlotId) -->
      <SlotCard
        v-for="slot in slotsStore.slots"
        :key="slot.id"
        v-memo="[slot.is_booked, slot.description, slot.booked_by, slot.booking_comment, cancelingSlotId === slot.id, deletingSlotId === slot.id]"
        :slot="slot"
        :is-canceling="cancelingSlotId === slot.id"
        :is-deleting="deletingSlotId === slot.id"
        @book="openBookingModal"
        @cancel="handleCancelBooking"
        @delete="handleDeleteSlot"
      />
    </div>

    <!-- Модальное окно бронирования -->
    <BookingModal
      :show="showBookingModal"
      :slot="selectedSlot"
      :is-submitting="isBooking"
      @close="closeBookingModal"
      @submit="handleBookSlot"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useSlotsStore } from '../store/slots';
import SlotCard from '../components/SlotCard.vue';
import CreateSlotForm from '../components/CreateSlotForm.vue';
import BookingModal from '../components/BookingModal.vue';
import { handleApiError } from '../utils/errorHandler';

const slotsStore = useSlotsStore();

// UI State
const showCreateForm = ref(false);
const showBookingModal = ref(false);
const selectedSlot = ref(null);
const isCreating = ref(false);
const isBooking = ref(false);
const cancelingSlotId = ref(null);
const deletingSlotId = ref(null);

const activeElementBeforeAction = ref(null);

// Actions
const handleCreateSlot = async (formData) => {
  isCreating.value = true;
  try {
    const start_time = `${formData.date} ${formData.start_time}:00`;
    const end_time = `${formData.date} ${formData.end_time}:00`;

    await slotsStore.createSlot({
      start_time,
      end_time,
      description: formData.description
    });

    closeCreateForm();
  } catch (error) {
    handleApiError(error, 'Error creating slot', 'Ошибка при создании слота');
  } finally {
    isCreating.value = false;
  }
};

const openCreateForm = () => {
  activeElementBeforeAction.value = document.activeElement;
  showCreateForm.value = true;
};

const closeCreateForm = async () => {
  showCreateForm.value = false;
  await nextTick();
  if (activeElementBeforeAction.value) {
    activeElementBeforeAction.value.focus();
    activeElementBeforeAction.value = null;
  }
};

const openBookingModal = (slot) => {
  activeElementBeforeAction.value = document.activeElement;
  selectedSlot.value = slot;
  showBookingModal.value = true;
};

const closeBookingModal = async () => {
  showBookingModal.value = false;
  selectedSlot.value = null;
  await nextTick();
  if (activeElementBeforeAction.value) {
    activeElementBeforeAction.value.focus();
    activeElementBeforeAction.value = null;
  }
};

const handleBookSlot = async (formData) => {
  if (!selectedSlot.value) return;

  isBooking.value = true;
  try {
    await slotsStore.bookSlot(selectedSlot.value.id, {
      client_name: formData.name,
      client_phone: formData.phone
    });
    closeBookingModal();
  } catch (error) {
    handleApiError(error, 'Error booking slot', 'Ошибка при бронировании');
  } finally {
    isBooking.value = false;
  }
};

const handleCancelBooking = async (slotId) => {
  if (!confirm('Вы уверены, что хотите отменить бронь?')) return;
  cancelingSlotId.value = slotId;
  try {
    await slotsStore.cancelBooking(slotId);
  } catch (error) {
    handleApiError(error, 'Error canceling booking', 'Ошибка при отмене брони');
  } finally {
    cancelingSlotId.value = null;
  }
};

const handleDeleteSlot = async (slotId) => {
  if (!confirm('Вы уверены, что хотите удалить этот слот?')) return;
  deletingSlotId.value = slotId;
  try {
    await slotsStore.deleteSlot(slotId);
  } catch (error) {
    handleApiError(error, 'Error deleting slot', 'Ошибка при удалении слота');
  } finally {
    deletingSlotId.value = null;
  }
};

onMounted(() => {
  slotsStore.fetchSlots();
});
</script>

<style scoped>
.page-header h1 {
  margin: 0;
  font-size: 1.75rem;
}

.slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
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

.empty-icon {
  font-size: 3rem;
  opacity: 0.5;
}

/* Simple Loader */
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

@media (max-width: 640px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .slots-grid {
    grid-template-columns: 1fr;
  }
}
</style>

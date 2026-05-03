<template>
  <div class="slots-view">
    <div class="page-header flex justify-between items-center mb-4">
      <h1>Управление слотами</h1>
      <button
        v-if="!showCreateForm"
        @click="showCreateForm = true"
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
      @cancel="showCreateForm = false"
    />

    <!-- Фильтр (опционально, пока просто заголовок) -->
    <div class="mb-4">
      <h3 class="text-secondary">Все слоты</h3>
    </div>

    <!-- Состояния -->
    <div v-if="slotsStore.isLoading" class="state-container">
      <div class="loader"></div>
      <p class="mt-3 text-muted">Загрузка слотов...</p>
    </div>

    <div v-else-if="slotsStore.slots.length === 0" class="state-container empty-state">
      <div class="empty-icon">📅</div>
      <h3 class="mt-3">Нет доступных слотов</h3>
      <p class="text-muted">Создайте новый слот, чтобы он появился здесь.</p>
    </div>

    <!-- Сетка слотов -->
    <div v-else class="slots-grid">
      <SlotCard
        v-for="slot in slotsStore.slots"
        :key="slot.id"
        :slot="slot"
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
import { ref, onMounted } from 'vue';
import { useSlotsStore } from '../store/slots';
import SlotCard from '../components/SlotCard.vue';
import CreateSlotForm from '../components/CreateSlotForm.vue';
import BookingModal from '../components/BookingModal.vue';

const slotsStore = useSlotsStore();

// UI State
const showCreateForm = ref(false);
const showBookingModal = ref(false);
const selectedSlot = ref(null);
const isCreating = ref(false);
const isBooking = ref(false);

// Actions
const handleCreateSlot = async (formData) => {
  isCreating.value = true;
  try {
    const start_time = `${formData.date} ${formData.start_time}:00`;
    const end_time = `${formData.date} ${formData.end_time}:00`;

    await slotsStore.createSlot({
      staff_id: formData.staff_id,
      service_id: formData.service_id,
      start_time,
      end_time,
      description: formData.description
    });

    showCreateForm.value = false;
  } catch (error) {
    alert('Ошибка при создании слота');
  } finally {
    isCreating.value = false;
  }
};

const openBookingModal = (slot) => {
  selectedSlot.value = slot;
  showBookingModal.value = true;
};

const closeBookingModal = () => {
  showBookingModal.value = false;
  selectedSlot.value = null;
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
    alert('Ошибка при бронировании');
  } finally {
    isBooking.value = false;
  }
};

const handleCancelBooking = async (slotId) => {
  if (!confirm('Вы уверены, что хотите отменить бронь?')) return;
  try {
    await slotsStore.cancelBooking(slotId);
  } catch (error) {
    alert('Ошибка при отмене брони');
  }
};

const handleDeleteSlot = async (slotId) => {
  if (!confirm('Вы уверены, что хотите удалить этот слот?')) return;
  try {
    await slotsStore.deleteSlot(slotId);
  } catch (error) {
    alert('Ошибка при удалении слота');
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

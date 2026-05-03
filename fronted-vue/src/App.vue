<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './store/auth';

const authStore = useAuthStore();

onMounted(() => {
  // Sync state with localStorage on initial load
  authStore.checkAuth();
});
</script>

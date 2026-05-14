<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './store/auth';
import { isTMA, initTelegramApp, getInitData } from './utils/telegram';

const authStore = useAuthStore();

import { useRouter } from 'vue-router';

const router = useRouter();

onMounted(async () => {
  // Sync state with localStorage on initial load
  authStore.checkAuth();

  // Telegram Mini App Initialization & Auth
  if (isTMA()) {
    initTelegramApp();
    const initData = getInitData();
    if (initData) {
      const response = await authStore.telegramLogin(initData);

      // If telegram login authenticated us, redirect to home if we are on login
      if (response.success && response.isAdmin && router.currentRoute.value.name === 'Login') {
          router.push('/');
      } else if (response.success && !response.isAdmin && router.currentRoute.value.name === 'Login') {
          // If we are a normal user, we shouldn't be asked for password anyway,
          // but we still need to bypass the password screen to view slots
          // We can mock 'is_logged_in' for client TMA
          localStorage.setItem('is_logged_in', 'true');
          authStore.checkAuth();
          router.push('/');
      }
    }
  }
});
</script>

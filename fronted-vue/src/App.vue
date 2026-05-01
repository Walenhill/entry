<template>
  <div id="app">
    <LoginForm v-if="!isAuthenticated" @login-success="handleLoginSuccess" />
    <SlotsManager v-else @logout="handleLogout" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import LoginForm from './components/LoginForm.vue';
import SlotsManager from './components/SlotsManager.vue';
import { authApi } from './api';

const isAuthenticated = ref(false);

const handleLoginSuccess = () => {
  isAuthenticated.value = true;
};

const handleLogout = () => {
  isAuthenticated.value = false;
  localStorage.removeItem('is_logged_in');
};

onMounted(async () => {
  // Проверка наличия сессии при загрузке на бэкенде
  try {
    const response = await authApi.check();
    if (response.data.authenticated) {
      isAuthenticated.value = true;
      localStorage.setItem('is_logged_in', 'true');
    }
  } catch (error) {
    isAuthenticated.value = false;
    localStorage.removeItem('is_logged_in');
  }
});
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  background-color: #f5f7fa;
  color: #333;
  line-height: 1.6;
}

#app {
  min-height: 100vh;
}
</style>

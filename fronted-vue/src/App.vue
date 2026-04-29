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
const isChecking = ref(true);

const handleLoginSuccess = () => {
  isAuthenticated.value = true;
};

const handleLogout = () => {
  isAuthenticated.value = false;
};

onMounted(async () => {
  // Проверка сессии на бэкенде при загрузке
  const authenticated = await authApi.isAuthenticated();
  isAuthenticated.value = authenticated;
  isChecking.value = false;
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

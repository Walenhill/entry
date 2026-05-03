<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <h2>Вход в панель</h2>
        <p class="text-muted">Введите пароль для доступа к управлению</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group mb-4">
          <label for="password">Пароль</label>
          <input
            type="password"
            id="password"
            v-model="password"
            required
            placeholder="••••••••"
            autocomplete="current-password"
          />
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="isLoading">
          {{ isLoading ? 'Вход...' : 'Войти' }}
        </button>

        <div v-if="error" class="error-message mt-3 text-center">
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/auth';

const router = useRouter();
const authStore = useAuthStore();

const password = ref('');
const isLoading = ref(false);
const error = ref('');

const handleLogin = async () => {
  isLoading.value = true;
  error.value = '';

  const result = await authStore.login(password.value);

  if (result.success) {
    router.push('/');
  } else {
    error.value = result.message;
  }

  isLoading.value = false;
};
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--bg-main);
  padding: 1rem;
}

.login-card {
  background-color: var(--bg-surface);
  width: 100%;
  max-width: 400px;
  padding: 2.5rem 2rem;
  border-radius: var(--border-radius-lg);
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-lg);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h2 {
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.w-100 {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
}

.error-message {
  color: var(--status-danger);
  background-color: var(--status-danger-bg);
  padding: 0.75rem;
  border-radius: var(--border-radius-sm);
  font-size: 0.875rem;
}
</style>

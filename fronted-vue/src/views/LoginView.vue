<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <h2>Вход в панель</h2>
        <p class="text-muted">Введите пароль для доступа к управлению</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group mb-4">
          <label for="password">Пароль <span class="text-danger">*</span></label>
          <div class="password-input-wrapper">
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
            ref="passwordInput"
            v-model="password"
            required
            placeholder="••••••••"
            autocomplete="current-password"
            :aria-invalid="!!error"
            :aria-describedby="error ? 'login-error' : null"
            :disabled="isLoading"
            />
            <span :title="isLoading ? (showPassword ? 'Скрыть пароль - Действие недоступно во время загрузки' : 'Показать пароль - Действие недоступно во время загрузки') : (showPassword ? 'Скрыть пароль' : 'Показать пароль')" style="display: inline-flex;">
              <button
                type="button"
                class="password-toggle-btn"
                @click="showPassword = !showPassword"
                :aria-label="isLoading ? (showPassword ? 'Скрыть пароль - Действие недоступно во время загрузки' : 'Показать пароль - Действие недоступно во время загрузки') : (showPassword ? 'Скрыть пароль' : 'Показать пароль')"
                :disabled="isLoading"
              >
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </span>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="isLoading">
          <span v-if="isLoading" class="spinner-small" aria-hidden="true"></span>
          {{ isLoading ? 'Вход...' : 'Войти' }}
        </button>

        <div v-if="error" id="login-error" class="error-message mt-3 text-center" role="alert" aria-live="assertive">
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/auth';

const router = useRouter();
const authStore = useAuthStore();

const passwordInput = ref(null);
const showPassword = ref(false);
const password = ref('');
const isLoading = ref(false);
const error = ref('');

onMounted(() => {
  nextTick(() => {
    if (passwordInput.value) {
      passwordInput.value.focus();
    }
  });
});

const handleLogin = async () => {
  isLoading.value = true;
  error.value = '';

  const result = await authStore.login(password.value);

  if (result.success) {
    router.push('/');
  } else {
    error.value = result.message;
    password.value = '';
    nextTick(() => {
      if (passwordInput.value) {
        passwordInput.value.focus();
      }
    });
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

.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input-wrapper input {
  padding-right: 3rem;
}

.password-toggle-btn {
  position: absolute;
  right: 0.5rem;
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--border-radius-sm);
  transition: color 0.2s;
}

.password-toggle-btn:hover:not(:disabled) {
  color: var(--text-primary);
}

.password-toggle-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.password-toggle-btn:focus-visible {
  outline: none;
  box-shadow: 0 0 0 2px var(--bg-main), 0 0 0 4px var(--accent-secondary);
}
</style>

<template>
  <div class="dashboard-layout">
    <aside class="sidebar" :class="{ 'sidebar-open': isMobileMenuOpen }">
      <div class="sidebar-header">
        <h2 class="logo">BookingApp</h2>
        <button class="close-mobile" @click="isMobileMenuOpen = false">×</button>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/" class="nav-item" @click="isMobileMenuOpen = false">
          Слоты
        </router-link>
        <router-link to="/stats" class="nav-item" @click="isMobileMenuOpen = false">
          Статистика
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn btn-outline w-100">Выйти</button>
      </div>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <button class="menu-toggle" @click="isMobileMenuOpen = true">☰</button>
        <div class="user-info">
          <span>Администратор</span>
        </div>
      </header>

      <main class="content-area">
        <router-view />
      </main>
    </div>

    <div
      v-if="isMobileMenuOpen"
      class="mobile-backdrop"
      @click="isMobileMenuOpen = false"
    ></div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/auth';

const router = useRouter();
const authStore = useAuthStore();
const isMobileMenuOpen = ref(false);

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
}

.sidebar {
  width: 250px;
  background-color: var(--bg-surface);
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  transition: transform 0.3s ease;
  z-index: 100;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  color: var(--accent-secondary);
  margin: 0;
  font-size: 1.5rem;
}

.close-mobile {
  display: none;
  background: none;
  border: none;
  color: var(--text-muted);
  font-size: 1.5rem;
  cursor: pointer;
}

.sidebar-nav {
  flex: 1;
  padding: 1.5rem 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  padding: 0.75rem 1.5rem;
  color: var(--text-secondary);
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background-color: var(--bg-surface-hover);
  color: var(--text-primary);
}

.nav-item.router-link-exact-active {
  background-color: rgba(139, 109, 190, 0.1);
  color: var(--accent-secondary);
  border-left-color: var(--accent-secondary);
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.w-100 {
  width: 100%;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0; /* Prevents flex children from overflowing */
}

.topbar {
  height: 64px;
  background-color: var(--bg-surface);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
}

.menu-toggle {
  display: none;
  background: none;
  border: none;
  color: var(--text-primary);
  font-size: 1.5rem;
  cursor: pointer;
}

.user-info {
  margin-left: auto;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.content-area {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

.mobile-backdrop {
  display: none;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    transform: translateX(-100%);
  }

  .sidebar-open {
    transform: translateX(0);
  }

  .close-mobile {
    display: block;
  }

  .menu-toggle {
    display: block;
  }

  .mobile-backdrop {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 50;
  }

  .content-area {
    padding: 1rem;
  }
}
</style>

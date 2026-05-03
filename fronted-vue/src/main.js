import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { registerSW } from 'virtual:pwa-register'
import router from './router'
import './style.css'
import App from './App.vue'

// Регистрация Service Worker для PWA
const updateSW = registerSW({
  onNeedRefresh() {
    // В будущем тут можно показать уведомление пользователю
    console.log('Доступно обновление, перезагрузите страницу.');
  },
  onOfflineReady() {
    console.log('Приложение готово к работе оффлайн.');
  },
})

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
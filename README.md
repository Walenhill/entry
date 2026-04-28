# Система записи на услуги

Простая система записи с Vue 3 фронтендом и PHP бэкендом.

## Быстрый старт с Docker

1. **Настройка переменных окружения**
   ```bash
   cp backend/.env.example backend/.env
   cp fronted-vue/.env.example fronted-vue/.env
   # Отредактируйте backend/.env, установив свои пароли
   ```

2. **Запуск контейнеров**
   ```bash
   docker-compose up -d
   ```

3. **Доступ к приложению**
   - Фронтенд (Vue): http://localhost:5173
   - Бэкенд (PHP API): http://localhost:8080
   - База данных: localhost:3306

4. **Первый вход**
   - Используйте пароль мастера из `backend/.env` (переменная `MASTER_PASSWORD`)
   - После входа вы сможете создавать и управлять слотами

## Возможности

- ✅ Авторизация по паролю мастера
- ✅ Просмотр всех слотов
- ✅ Создание новых слотов (дата, время, описание)
- ✅ Бронирование слотов клиентами (имя, комментарий)
- ✅ Отмена бронирования
- ✅ Удаление слотов
- ✅ Статусы слотов (свободен/забронирован)
- ✅ Адаптивный дизайн

## API Endpoints

### Authentication

#### POST /auth/login
Вход в систему

**Тело запроса:**
```json
{ "password": "ваш_пароль" }
```

**Ответ:**
```json
{ "token": "jwt_token_here" }
```

### Slots

#### GET /slots
Получить все слоты (требуется авторизация)

**Ответ:**
```json
{
  "slots": [
    {
      "id": 1,
      "date": "2025-04-29",
      "start_time": "14:00",
      "end_time": "15:00",
      "description": "Прием клиентов",
      "is_booked": false,
      "booked_by": null,
      "booking_comment": null
    }
  ]
}
```

#### POST /slots
Создать новый слот (требуется авторизация)

**Тело запроса:**
```json
{
  "date": "2025-04-30",
  "start_time": "10:00",
  "end_time": "11:00",
  "description": "Утренний слот"
}
```

#### POST /slots/:id/book
Забронировать слот (требуется авторизация)

**Тело запроса:**
```json
{
  "name": "Иван Иванов",
  "comment": "Хочу записаться на процедуру"
}
```

#### DELETE /slots/:id/book
Отменить бронь слота (требуется авторизация)

#### DELETE /slots/:id
Удалить слот (требуется авторизация)

## Структура проекта

```
/workspace
├── backend/              # PHP бэкенд
│   ├── main.php         # Основной файл API
│   ├── .env             # Переменные окружения
│   ├── .env.example     # Шаблон переменных окружения
│   ├── Dockerfile       # Docker образ PHP
│   ├── .htaccess        # Настройки Apache
│   └── init.sql         # SQL скрипт инициализации БД
├── fronted-vue/         # Vue 3 фронтенд
│   ├── src/
│   │   ├── api/         # API клиент (axios)
│   │   ├── components/  # Vue компоненты
│   │   │   ├── LoginForm.vue
│   │   │   └── SlotsManager.vue
│   │   ├── App.vue      # Главный компонент
│   │   └── main.js      # Точка входа
│   ├── .env             # Переменные окружения
│   ├── .env.example     # Шаблон переменных окружения
│   ├── Dockerfile       # Docker образ Node.js
│   └── package.json     # Зависимости npm
├── docker-compose.yml    # Конфигурация Docker Compose
└── README.md            # Этот файл
```

## Безопасность

⚠️ **Важно:** Перед запуском в production:
1. Измените пароль мастера в `backend/.env` (MASTER_PASSWORD)
2. Измените пароли базы данных
3. Настройте CORS для вашего домена
4. Используйте HTTPS
5. Не коммитьте файлы `.env` в репозиторий

## Разработка без Docker

### Бэкенд
1. Установите PHP 8.2+ и MySQL
2. Создайте базу данных и импортируйте `backend/init.sql`
3. Настройте `backend/.env`
4. Запустите встроенный сервер PHP:
   ```bash
   cd backend
   php -S localhost:8080
   ```

### Фронтенд
1. Установите Node.js 18+
2. Скопируйте `.env.example` в `.env`:
   ```bash
   cp fronted-vue/.env.example fronted-vue/.env
   ```
3. Установите зависимости:
   ```bash
   cd fronted-vue
   npm install
   ```
4. Запустите dev-сервер:
   ```bash
   npm run dev
   ```

## Технологии

- **Frontend:** Vue 3, Vite, Axios
- **Backend:** PHP 8.2, MySQL 8.0
- **DevOps:** Docker, Docker Compose

## Лицензия

MIT

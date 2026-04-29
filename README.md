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
{ 
  "success": true,
  "token": "sha256_hash_token"
}
```

### Slots

#### GET /slots
Получить слоты. Клиенты видят только свободные, админы - все.

**Параметры:**
- `date` (optional) - фильтрация по дате (YYYY-MM-DD)
- `role` (optional) - 'admin' для получения всех слотов (требуется авторизация)

**Ответ:**
```json
[
  {
    "id": 1,
    "start_time": "2024-06-01 10:00:00",
    "end_time": "2024-06-01 11:00:00",
    "description": "Утренний слот",
    "status": "available"
  }
]
```

#### POST /slots
Создать новый слот (требуется авторизация)

**Тело запроса:**
```json
{
  "start_time": "2024-06-01 10:00:00",
  "end_time": "2024-06-01 11:00:00",
  "description": "Утренний слот"
}
```

**Валидация:**
- Формат даты/времени: YYYY-MM-DD HH:MM:SS
- start_time должен быть раньше end_time
- Отсутствие пересечений с существующими слотами

#### POST /slots/generate
Сгенерировать слоты по шаблону (требуется авторизация)

**Тело запроса:**
```json
{
  "date": "2024-06-01",
  "start_hour": 9,
  "end_hour": 18,
  "duration": 60,
  "description": "Консультация"
}
```

#### POST /slots/:id/book
Забронировать слот

**Тело запроса:**
```json
{
  "client_name": "Иван Иванов",
  "client_phone": "+79991234567"
}
```

**Ответ:**
```json
{ 
  "success": true,
  "message": "Слот успешно забронирован"
}
```

#### POST /slots/:id/cancel
Отменить бронь (требуется авторизация)

**Ответ:**
```json
{ 
  "success": true,
  "message": "Бронь отменена"
}
```

#### DELETE /slots/:id
Удалить слот (требуется авторизация)

**Ответ:**
```json
{ 
  "success": true,
  "message": "Слот удален"
}
```

#### GET /stats
Получить статистику (требуется авторизация)

**Ответ:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total": 50,
      "booked": 35,
      "available": 10,
      "cancelled": 5
    },
    "load_percentage": 70.0,
    "top_clients": [
      {
        "client_name": "Иван Иванов",
        "client_phone": "+79991234567",
        "visits": 5
      }
    ]
  }
}
```

## Технологии

**Бэкенд:**
- PHP 8.2 (без фреймворков)
- MySQL 8.0
- Apache

**Фронтенд:**
- Vue 3 (Composition API)
- Vite
- Axios

**Инфраструктура:**
- Docker & Docker Compose

## Структура проекта

```
/workspace
├── backend/
│   ├── main.php          # API endpoint
│   ├── init.sql          # Схема БД
│   ├── .env              # Конфигурация
│   └── Dockerfile
├── fronted-vue/
│   ├── src/
│   │   ├── components/
│   │   │   ├── LoginForm.vue
│   │   │   └── SlotsManager.vue
│   │   ├── api/
│   │   │   └── index.js
│   │   └── App.vue
│   ├── .env
│   └── Dockerfile
├── docker-compose.yml
└── README.md
```

## Переменные окружения

**backend/.env:**
- `DB_HOST` - хост MySQL
- `DB_USERNAME` - пользователь БД
- `DB_PASSWORD` - пароль БД
- `DB_NAME` - имя БД
- `MASTER_PASSWORD` - пароль администратора
- `SECRET_KEY` - секретный ключ для токенов

**fronted-vue/.env:**
- `VITE_API_BASE_URL` - URL бэкенда

## Безопасность

- Пароли не хранятся в открытом виде
- Токены генерируются через hash(password + secret_key)
- Валидация всех входных данных
- Защита от пересечения слотов по времени
- Разделение доступа: клиенты видят только свободные слоты

## Changelog

### v0.3.0 - Аналитика и статистика
- 📊 Добавлен эндпоинт `/stats` для администраторов
- 📈 Общая статистика: всего/забронировано/свободно/отменено
- 📊 Процент загрузки слотов
- 🏆 Топ-5 клиентов по количеству визитов
- 🎨 Модальное окно статистики в админ-панели

### v0.2.0 - Безопасность и валидация
- 🔐 Авторизация через hash-токены (не пароль)
- ✅ Валидация формата даты/времени (YYYY-MM-DD HH:MM:SS)
- ⛔ Запрет пересечения слотов по времени
- ✅ Валидация параметров генерации слотов
- 📚 Актуализирована документация API
- 🔑 Добавлен SECRET_KEY в конфигурацию

### v0.1.0 - MVP
- ✅ Базовая система записи
- ✅ Создание и управление слотами
- ✅ Бронирование клиентами
- ✅ Vue 3 фронтенд
- ✅ Docker конфигурация

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
3. Установите уникальный SECRET_KEY для генерации токенов
4. Настройте CORS для вашего домена
5. Используйте HTTPS
6. Не коммитьте файлы `.env` в репозиторий

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

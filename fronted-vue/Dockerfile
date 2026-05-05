FROM node:18-alpine

WORKDIR /app

# Копирование package файлов
COPY package.json pnpm-lock.yaml ./

# Установка pnpm
RUN npm install -g pnpm

# Установка зависимостей
RUN pnpm install --frozen-lockfile

# Копирование исходного кода
COPY . .

# Проброс порта для Vite
EXPOSE 5173

# Запуск приложения в режиме разработки
CMD ["pnpm", "run", "dev", "--", "--host"]

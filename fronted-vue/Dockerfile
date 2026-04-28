FROM node:18-alpine

WORKDIR /app

# Копирование package файлов
COPY package*.json ./

# Установка зависимостей
RUN npm install

# Копирование исходного кода
COPY . .

# Проброс порта для Vite
EXPOSE 5173

# Запуск приложения в режиме разработки
CMD ["npm", "run", "dev", "--", "--host"]

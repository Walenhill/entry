#!/bin/bash

# Скрипт для быстрого запуска проекта (Linux/macOS)

echo "🚀 Подготовка к запуску проекта..."

# Проверяем бэкенд .env
if [ ! -f "backend/.env" ]; then
    echo "📄 Файл backend/.env не найден. Создаю из .env.example..."
    cp backend/.env.example backend/.env
    echo "✅ backend/.env создан. (Не забудьте настроить пароли, если нужно)"
else
    echo "✅ backend/.env уже существует."
fi

# Проверяем фронтенд .env
if [ ! -f "fronted-vue/.env" ]; then
    echo "📄 Файл fronted-vue/.env не найден. Создаю из .env.example..."
    cp fronted-vue/.env.example fronted-vue/.env
    echo "✅ fronted-vue/.env создан."
else
    echo "✅ fronted-vue/.env уже существует."
fi

echo "🐳 Запуск Docker-контейнеров..."
docker compose up -d --build

echo ""
echo "🎉 Проект успешно запущен!"
echo "🌐 Фронтенд: http://localhost:5173"
echo "⚙️  Бэкенд API: http://localhost:8080"
echo "Для остановки проекта используйте: docker compose down"

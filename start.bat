@echo off
chcp 65001 >nul
echo 🚀 Подготовка к запуску проекта...

REM Проверяем бэкенд .env
if not exist "backend\.env" (
    echo 📄 Файл backend\.env не найден. Создаю из .env.example...
    copy "backend\.env.example" "backend\.env" >nul
    echo ✅ backend\.env создан. (Не забудьте настроить пароли, если нужно)
) else (
    echo ✅ backend\.env уже существует.
)

REM Проверяем фронтенд .env
if not exist "fronted-vue\.env" (
    echo 📄 Файл fronted-vue\.env не найден. Создаю из .env.example...
    copy "fronted-vue\.env.example" "fronted-vue\.env" >nul
    echo ✅ fronted-vue\.env создан.
) else (
    echo ✅ fronted-vue\.env уже существует.
)

echo 🐳 Запуск Docker-контейнеров...
docker compose up -d --build

echo.
echo 🎉 Проект успешно запущен!
echo 🌐 Фронтенд: http://localhost:5173
echo ⚙️  Бэкенд API: http://localhost:8080
echo Для остановки проекта используйте: docker compose down
pause

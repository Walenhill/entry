.PHONY: start stop setup logs

# Базовые настройки
DOCKER_COMPOSE = docker compose

# Основная команда для запуска
start: setup
	@echo "🐳 Запуск Docker-контейнеров..."
	$(DOCKER_COMPOSE) up -d --build
	@echo "🎉 Проект успешно запущен!"
	@echo "🌐 Фронтенд: http://localhost:5173"
	@echo "⚙️  Бэкенд API: http://localhost:8080"

# Остановка контейнеров
stop:
	@echo "🛑 Остановка Docker-контейнеров..."
	$(DOCKER_COMPOSE) down

# Подготовка конфигурационных файлов
setup:
	@echo "🚀 Проверка конфигурационных файлов..."
	@if [ ! -f "backend/.env" ]; then \
		echo "📄 Копирование backend/.env.example -> backend/.env"; \
		cp backend/.env.example backend/.env; \
	fi
	@if [ ! -f "fronted-vue/.env" ]; then \
		echo "📄 Копирование fronted-vue/.env.example -> fronted-vue/.env"; \
		cp fronted-vue/.env.example fronted-vue/.env; \
	fi

# Просмотр логов
logs:
	$(DOCKER_COMPOSE) logs -f

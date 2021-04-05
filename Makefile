start:
	cp .env.example .env
	docker-compose up -d --build
	docker-compose exec app bash -c "composer install"
	docker-compose exec app bash -c "php artisan migrate"

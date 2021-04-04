start:
	docker-compose exec app bash -c "composer install"
	cp .env.example .env
	docker-compose exec app bash -c "php artisan migrate"

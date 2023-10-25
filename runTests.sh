#!/bin/bash

echo "Executando os testes..."
# docker exec -it backend-admin-video-catalog-app bash -c "./vendor/bin/phpunit"
# docker exec -it backend-admin-video-catalog-app bash -c "php artisan test"
docker exec -it backend-admin-video-catalog-app bash -c "php artisan test --stop-on-failure"

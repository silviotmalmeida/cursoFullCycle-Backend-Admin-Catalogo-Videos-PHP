#!/bin/bash

echo "Executando as migrations..."
docker exec -it backend-admin-video-catalog-app bash -c "php artisan migrate"

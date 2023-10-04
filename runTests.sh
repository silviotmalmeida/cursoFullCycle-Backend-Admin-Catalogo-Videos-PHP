#!/bin/bash

echo "Executando os testes..."
docker exec -it backend-admin-video-catalog-app bash -c "./vendor/bin/phpunit"

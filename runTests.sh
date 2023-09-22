#!/bin/bash

echo "Abrindo o terminal..."
docker exec -it backend-admin-video-catalog-app bash -c "./vendor/bin/phpunit"

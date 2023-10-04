#!/bin/bash

echo "Atualizando as dependências..."
docker exec -it backend-admin-video-catalog-app bash -c "composer update"

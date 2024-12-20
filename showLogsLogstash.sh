#!/bin/bash

echo "Mostrando os logs do conteiner..."
docker logs backend-admin-video-catalog-logstash --tail 50 -f
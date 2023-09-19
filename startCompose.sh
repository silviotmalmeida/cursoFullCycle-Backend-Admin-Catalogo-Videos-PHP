#!/bin/bash

# subindo os containers
# o -f a ponta para o arquivo yaml
# o -d cria os containers desanexados do terminal
# o --build força a recriação das imagens
docker-compose -f docker-compose.yaml up -d --build --remove-orphans
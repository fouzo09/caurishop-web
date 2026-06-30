#!/bin/bash
set -e

GITHUB_TOKEN=$1
GITHUB_ACTOR=$2
GITHUB_REPOSITORY=$3
IMAGE_TAG=$4

echo "$GITHUB_TOKEN" | docker login ghcr.io -u "$GITHUB_ACTOR" --password-stdin

export IMAGE_TAG GITHUB_REPOSITORY

docker compose -f docker-compose.prod.yml up -d db
docker compose -f docker-compose.prod.yml pull app
docker compose -f docker-compose.prod.yml up -d --no-deps app

docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize
docker compose -f docker-compose.prod.yml exec -T app php artisan storage:link

docker image prune -f
echo "Déploiement terminé — $GITHUB_REPOSITORY:$IMAGE_TAG"

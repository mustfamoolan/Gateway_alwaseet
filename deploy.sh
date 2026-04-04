#!/bin/bash

echo "🚀 Starting Deployment for Al-Waseet Gateway..."

# 1. Pull latest code if using git
git pull origin main

# 2. Rebuild and restart containers
docker-compose up -d --build

# 3. Wait for DB to be ready and run migrations
echo "⏳ Waiting for database to be ready..."
sleep 5
docker-compose exec app php artisan migrate --force

# 4. Clear cache
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

echo "✅ Gateway is UP and running!"
docker-compose ps

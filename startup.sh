#!/bin/bash

# Azure App Service Startup Script for Laravel Application
# This script runs at container startup to configure Laravel for production

set -e

echo "=== Azure App Service Laravel Startup ==="
echo "Timestamp: $(date)"
echo ""

# Navigate to site directory
cd /home/site/wwwroot || { echo "ERROR: Cannot cd to /home/site/wwwroot"; exit 1; }
echo "Working directory: $(pwd)"

# Set Redis environment variables from Azure App Settings
# These override any .env values for Azure Redis Cache
if [ -n "$AZURE_REDIS_HOST" ]; then
    export REDIS_HOST="$AZURE_REDIS_HOST"
    echo "Set REDIS_HOST=$REDIS_HOST"
fi

if [ -n "$AZURE_REDIS_PORT" ]; then
    export REDIS_PORT="$AZURE_REDIS_PORT"
    echo "Set REDIS_PORT=$REDIS_PORT"
fi

if [ -n "$AZURE_REDIS_PASSWORD" ]; then
    export REDIS_PASSWORD="$AZURE_REDIS_PASSWORD"
    echo "REDIS_PASSWORD set (hidden)"
fi

# Ensure storage directories are writable
echo ""
echo "Setting permissions on storage directories..."
chmod -R 775 storage 2>/dev/null || true
chmod -R 775 bootstrap/cache 2>/dev/null || true

# Production Laravel optimizations
echo ""
echo "Checking application mode..."

if [ "$APP_DEBUG" = "false" ]; then
    echo "Production mode detected (APP_DEBUG=false)"
    
    # Cache configuration
    echo "Caching configuration..."
    php artisan config:cache --no-interaction 2>/dev/null || echo "  config:cache skipped (may already be cached)"
    
    # Cache routes (if applicable)
    echo "Caching routes..."
    php artisan route:cache --no-interaction 2>/dev/null || echo "  route:cache skipped (routes may use closures)"
    
    # Cache views
    echo "Caching views..."
    php artisan view:cache --no-interaction 2>/dev/null || echo "  view:cache skipped"
    
else
    echo "Debug mode enabled (APP_DEBUG=true) - skipping config/route caching"
fi

# Clear any cached events/queues if needed
echo ""
echo "Clearing cached events..."
php artisan event:clear --no-interaction 2>/dev/null || true

# Start queue worker in background
echo ""
echo "Starting queue worker..."
echo "  Connection: redis"
echo "  Sleep: 3s | Tries: 3"

# Run queue worker with proper logging
nohup php artisan queue:work redis \
    --sleep=3 \
    --tries=3 \
    --max-time=3600 \
    --memory=128 \
    --quiet \
    > /home/site/logs/queue-worker.log 2>&1 &

QUEUE_PID=$!
echo "Queue worker started with PID: $QUEUE_PID"

# Verify queue worker is running
sleep 2
if ps -p $QUEUE_PID > /dev/null 2>&1; then
    echo "Queue worker is running"
else
    echo "WARNING: Queue worker may have failed to start. Check logs at /home/site/logs/queue-worker.log"
fi

echo ""
echo "=== Startup Complete ==="
echo "Application ready at /home/site/wwwroot"
echo "Logs available at /home/site/logs/"
echo ""

# Exit successfully - web server will handle requests
exit 0

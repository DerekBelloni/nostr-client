#!/bin/bash

echo "Starting development environment...."

# Store the original directory
ORIGINAL_DIR=${pwd}

# Start Laravel Development Server
php artisan serve &

# Start Laravel Reverb (Websocket Server)
php artisan reverb:start &

# Start listener for User Metadata
php artisan rabbitmq:user-metadata &

# Start listener for User Notes
php artisan rabbitmq:user-notes &

# Start listener for User Follows List
php artisan rabbitmq:follow-list &

#Start Vite/npm development server
npm run dev &

# Change to Go app directory and start it
echo "Starting GO socket server..."
cd ~/Developer/Personal/GO/go-socket-server
if [ $? -eq 0 ]; then
    go run cmd/server/main.go &
    echo "Go socket server started"
else 
    echo "Error: Could not change to Go application directory"
    exit 1
fi

cd $ORIGINAL_DIR

echo "Development servers started!"
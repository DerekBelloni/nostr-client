#!/bin/bash

# Store all process IDs in a temp file for tracking
PID_FILE="/tmp/php-environment-pids.txt"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to create a new tmux window and run commands in split panes
create_tmux_window() {
   local window_name="$1"
   shift  # Remove first argument (window_name) from arguments list
   local commands=("$@")  # Remaining arguments are commands
   local num_commands=${#commands[@]}

   # Create new window
   tmux new-window -n "$window_name"

   # For each command except the last one, create a split and run the command
   for ((i = 0; i < num_commands - 1; i++)); do
       if [ $i -eq 0 ]; then
           # First command goes in the original pane
           tmux send-keys "${commands[$i]}" C-m
       else
           # Split vertically and run command
           tmux split-window -v
           tmux send-keys "${commands[$i]}" C-m
       fi
   done

   # Run last command in the last pane
   if [ $num_commands -gt 1 ]; then
       tmux split-window -v
       tmux send-keys "${commands[$((num_commands-1))]}" C-m
   fi

   # Arrange panes evenly
   tmux select-layout even-vertical
}

graceful_shutdown() {
    echo "Initiating graceful shutdown..."
    
    # First, stop all RabbitMQ listeners
    echo "Stopping RabbitMQ listeners..."
    pkill -TERM -f "php artisan rabbitmq:" 2>/dev/null || true
    sleep 2
    # Force kill any remaining listeners
    pkill -KILL -f "php artisan rabbitmq:" 2>/dev/null || true
    echo -e "${GREEN}RabbitMQ listeners stopped${NC}"

    # Read and kill each stored PID
    if [ -f "$PID_FILE" ]; then
        while read -r pid; do
            if ps -p $pid > /dev/null 2>&1; then
                echo "Stopping process $pid..."
                kill -TERM $pid 2>/dev/null || true
                sleep 1
                # Check if process is still running and force kill if necessary
                if ps -p $pid > /dev/null 2>&1; then
                    echo "Force stopping process $pid..."
                    kill -KILL $pid 2>/dev/null || true
                fi
                pkill -P $pid 2>/dev/null || true
            fi
        done < "$PID_FILE"
        rm -f "$PID_FILE"
        echo -e "${GREEN}Main services stopped${NC}"
    else
        echo -e "${YELLOW}No PID file found. Main services may not be running.${NC}"
    fi

    # Stop Redis and RabbitMQ servers
    echo "Stopping Redis server..."
    pkill -TERM -f "redis-server" 2>/dev/null || true
    sleep 1
    echo "Stopping RabbitMQ server..."
    pkill -TERM -f "rabbitmq-server" 2>/dev/null || true
    
    # Clean up any remaining tmux windows
    echo "Cleaning up tmux windows..."
    tmux kill-window -t "services" 2>/dev/null || true
    tmux kill-window -t "rabbitmq-1" 2>/dev/null || true
    tmux kill-window -t "rabbitmq-2" 2>/dev/null || true

    echo -e "${GREEN}Environment shutdown complete${NC}"
}

case "$1" in
    "start")
        # [Previous start code remains the same...]
        ;;
        
    "stop")
        graceful_shutdown
        ;;
        
    *)
        echo "Usage: $0 {start|stop}"
        exit 1
        ;;
esac

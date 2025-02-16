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
       # Clear any existing PID file
       rm -f $PID_FILE

       # Create a new window for background services
       tmux new-window -n "services"

       # Handle RabbitMQ
       echo "Managing RabbitMQ server..."
       pkill -f "rabbitmq-server" 2>/dev/null || true
       tmux send-keys "rabbitmq-server" C-m
       # Give it a moment to start
       sleep 2
       # Get and store the PID
       rabbitmq_pid=$(pgrep -f "rabbitmq-server")
       if [ -n "$rabbitmq_pid" ]; then
           echo $rabbitmq_pid >> $PID_FILE
           echo -e "${GREEN}RabbitMQ server started${NC}"
       else
           echo -e "${RED}Failed to start RabbitMQ server${NC}"
           exit 1
       fi

       # Split the window vertically for Redis
       tmux split-window -v

       # Handle Redis
       echo "Managing Redis server..."
       pkill -f "redis-server" 2>/dev/null || true
       tmux send-keys "redis-server" C-m
       # Give it a moment to start
       sleep 2
       # Get and store the PID
       redis_pid=$(pgrep -f "redis-server")
       if [ -n "$redis_pid" ]; then
           echo $redis_pid >> $PID_FILE
           echo -e "${GREEN}Redis server started${NC}"
       else
           echo -e "${RED}Failed to start Redis server${NC}"
           exit 1
       fi

       # Arrange the panes evenly
       tmux select-layout even-vertical

       echo "Starting PHP development environment..."

       # Start the main PHP server
       echo "Starting PHP server..."
       php artisan serve & echo $! >> $PID_FILE

       # Start Reverb
       echo "Starting Reverb..."
       php artisan reverb:start & echo $! >> $PID_FILE

       # Start npm
       echo "Starting npm..."
       npm run dev & echo $! >> $PID_FILE

       # Define all RabbitMQ listener commands
       declare -a window1_commands=(
           "php artisan rabbitmq:listen-metadata"
           "php artisan rabbitmq:user-notes"
           "php artisan rabbitmq:follow-list"
       )

       declare -a window2_commands=(
           "php artisan rabbitmq:follows-metadata"
           "php artisan rabbitmq:search-results"
           "php artisan rabbitmq:author-metadata"
       )

       # Create two tmux windows with the listeners split evenly
       create_tmux_window "rabbitmq-1" "${window1_commands[@]}"
       create_tmux_window "rabbitmq-2" "${window2_commands[@]}"

       # Handle Go development environment
       echo "Setting up Go development environment..."
       GO_DIR=~/Developer/Personal/GO/go-socket-server
       if [ -d "$GO_DIR" ]; then
           # Create a new window for Neovim
           tmux new-window -n "go-editor"
           tmux send-keys "cd $GO_DIR" C-m
           tmux send-keys "nvim ." C-m

           # Add delay before starting Go server
           sleep 3
           echo "Starting Go server..."

           # Create a new window for the Go application
           tmux new-window -n "go-server"
           tmux send-keys "cd $GO_DIR" C-m
           tmux send-keys "go run cmd/server/main.go" C-m

           # Get the PID of the Go process after it starts
           sleep 2
           go_pid=$(pgrep -f "go run cmd/server/main.go")
           if [ -n "$go_pid" ]; then
               echo $go_pid >> $PID_FILE
               echo -e "${GREEN}Go server started${NC}"
           else
               echo -e "${YELLOW}Warning: Go server PID not found, but process may still be running${NC}"
           fi
       else
           echo -e "${RED}Error: Go application directory not found at $GO_DIR${NC}"
           exit 1
       fi
       ;;

   "stop")
       graceful_shutdown
       ;;

   *)
       echo "Usage: $0 {start|stop}"
       exit 1
       ;;
esac



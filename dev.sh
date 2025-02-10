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

case "$1" in
    "start")
        # Clear any existing PID file
        rm -f $PID_FILE

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

        # Handle Go server and editor
        echo "Setting up Go development environment..."
        GO_DIR=~/Developer/Personal/GO/go-socket-server
        if [ -d "$GO_DIR" ]; then
            # Create a window for Neovim
            tmux new-window -n "go-editor"
            tmux send-keys "cd $GO_DIR" C-m
            tmux send-keys "nvim ." C-m

            # Create a new window for Go server output
            tmux new-window -n "go-server"
            tmux send-keys "cd $GO_DIR" C-m

            # First build the Go server
            echo "Building Go server..."
            tmux send-keys "go build cmd/server/main.go" C-m

            # Give it a moment to build
            sleep 2

            # Run the server
            tmux send-keys "go run cmd/server/main.go" C-m

            echo -e "${GREEN}Go development environment started${NC}"
        else
            echo -e "${RED}Error: Go application directory not found at $GO_DIR${NC}"
            exit 1
        fi

        echo -e "${GREEN}All services started!${NC}"
        ;;

    "stop")
        echo "Stopping development environment..."

        # Read and kill each stored PID
        if [ -f "$PID_FILE" ]; then
            while read -r pid; do
                kill -TERM $pid 2>/dev/null || true
                pkill -P $pid 2>/dev/null || true
            done < "$PID_FILE"
            rm -f "$PID_FILE"
            echo -e "${GREEN}Main services stopped${NC}"
        else
            echo -e "${YELLOW}No PID file found. Main services may not be running.${NC}"
        fi

        # Kill any remaining PHP artisan processes
        echo "Stopping RabbitMQ listeners..."
        pkill -f "php artisan rabbitmq:" 2>/dev/null || true
        echo -e "${GREEN}RabbitMQ listeners stopped${NC}"
        ;;

    *)
        echo "Usage: $0 {start|stop}"
        exit 1
        ;;
esac

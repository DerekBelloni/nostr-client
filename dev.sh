#!/bin/bash

# Store all process IDs in a temp file for tracking
PID_FILE="/tmp/dev-environment-pids.txt"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if VS Code command line tools are installed
check_vscode_cli() {
    if ! command -v code &> /dev/null; then
        echo -e "${YELLOW}VS Code command line tools not found${NC}"
        echo "To install:"
        echo "1. Open VS Code"
        echo "2. Open Command Palette (Cmd+Shift+P)"
        echo "3. Type 'shell command'"
        echo "4. Select 'Shell Command: Install 'code' command in PATH'"
        echo "5. Restart your terminal"
        return 1
    fi
    return 0
}

# Function to check if VS Code is running with our Go project
is_vscode_running() {
    ps aux | grep "code" | grep "go-socket-server" >/dev/null
    return $?
}



# Function to check if VS Code is running with our Go project
is_vscode_running() {
    ps aux | grep "code" | grep "go-socket-server" >/dev/null
    return $?
}

case "$1" in
    "start")
        # Clear any existing PID file
        rm -f $PID_FILE
        
        echo "Starting development environment..."
        
        # Store original directory
        ORIGINAL_DIR=$(pwd)
        
        # Start each service and store its PID
        php artisan serve & echo $! >> $PID_FILE
        php artisan reverb:start & echo $! >> $PID_FILE
        php artisan rabbitmq:listen-metadata & echo $! >> $PID_FILE
        php artisan rabbitmq:user-notes & echo $! >> $PID_FILE
        php artisan rabbitmq:follow-list & echo $! >> $PID_FILE
        npm run dev & echo $! >> $PID_FILE
        
        # Start Go server
        # echo "Starting GO socket server..."
        # GO_DIR=~/Developer/Personal/GO/go-socket-server
        # cd "$GO_DIR"
        # if [ $? -eq 0 ]; then
        #     # Use osascript to open Tabby and change to the correct directory
        #     osascript -e "tell application \"Tabby\" to activate" \
        #             -e "tell application \"System Events\" to keystroke \"t\" using command down" \
        #             -e "delay 0.5" \
        #             -e "tell application \"System Events\" to keystroke \"cd $GO_DIR && clear && echo 'Go Server Output:' && tail -f /tmp/go-server.log\"" \
        #             -e "tell application \"System Events\" to keystroke return"
            
        #     # First build the Go server and check for errors
        #     echo "Building Go server..."
        #     go build cmd/server/main.go
        #     if [ $? -eq 0 ]; then
        #         echo -e "${GREEN}Build successful${NC}"
        #         # Start the Go server and store its PID
        #         go run cmd/server/main.go > /tmp/go-server.log 2>&1 & 
        #         GO_PID=$!
        #         echo $GO_PID >> $PID_FILE
        #     else
        #         echo -e "${RED}Build failed${NC}"
        #         exit 1
        #     fi
            
        #     echo -e "${GREEN}Go socket server started${NC}"
        # else 
        #     echo -e "${RED}Error: Could not change to Go application directory${NC}"
        #     exit 1
        # fi

        # Return to original directory
        cd $ORIGINAL_DIR
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
            echo -e "${GREEN}All services stopped${NC}"
        else
            echo -e "${RED}No PID file found. Services may not be running.${NC}"
        fi
        ;;
    
    *)
        echo "Usage: $0 {start|stop}"
        exit 1
        ;;
esac
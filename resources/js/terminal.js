import { Terminal } from '@xterm/xterm';
import { AttachAddon } from '@xterm/addon-attach';
import { FitAddon } from '@xterm/addon-fit';
import { WebLinksAddon } from '@xterm/addon-web-links';
import { SearchAddon } from '@xterm/addon-search';
import { WebglAddon } from '@xterm/addon-webgl';
import { CanvasAddon } from '@xterm/addon-canvas';

class PodTerminal {
    constructor() {
        this.terminal = null;
        this.fitAddon = null;
        this.sessionId = null;
        this.isConnected = false;
        this.pollingInterval = null;
        this.currentPod = null;
        this.currentLine = '';
        this.cursorPosition = 0;
        this.workingDirectory = '/';
        this.promptPrefix = '$ ';
        this.commandHistory = [];
        this.historyIndex = -1;
        this.websocketMode = false;
        this.websocket = null;
        this.attachAddon = null;
    }

    /**
     * Initialize terminal for a specific pod
     */
    async connect(namespace, pod, container = null, cluster = null) {
        try {
            // Disconnect any existing session first
            if (this.isConnected) {
                await this.disconnect();
            }

            // Reset terminal state
            this.currentLine = '';
            this.cursorPosition = 0;
            this.workingDirectory = '/';
            this.promptPrefix = '$ ';
            this.commandHistory = [];
            this.historyIndex = -1;

            // Store current pod info
            this.currentPod = { namespace, pod, container, cluster };

            // Create terminal instance with performance optimizations
            this.terminal = new Terminal({
                cursorBlink: true,
                cursorStyle: 'block',
                fontSize: 14,
                fontFamily: 'Monaco, Menlo, "Ubuntu Mono", monospace',
                theme: {
                    background: '#1e1e1e',
                    foreground: '#cccccc',
                    cursor: '#ffffff',
                    cursorAccent: '#1e1e1e',
                    selection: '#264f78',
                    selectionForeground: '#ffffff',
                    black: '#000000',
                    red: '#f44747',
                    green: '#608b4e',
                    yellow: '#dcdcaa',
                    blue: '#569cd6',
                    magenta: '#c586c0',
                    cyan: '#4ec9b0',
                    white: '#d4d4d4',
                    brightBlack: '#666666',
                    brightRed: '#f44747',
                    brightGreen: '#608b4e',
                    brightYellow: '#dcdcaa',
                    brightBlue: '#569cd6',
                    brightMagenta: '#c586c0',
                    brightCyan: '#4ec9b0',
                    brightWhite: '#ffffff'
                },
                allowTransparency: false,
                convertEol: true,
                scrollback: 5000, // Increased scrollback for more history
                tabStopWidth: 4,
                // Scrolling optimizations - Much faster scrolling
                fastScrollModifier: 'alt',
                fastScrollSensitivity: 25, // Increased from 10
                scrollSensitivity: 8, // Increased from 3
                // Enable scrollbar and better input handling
                scrollOnUserInput: true,
                altClickMovesCursor: true,
                macOptionIsMeta: true,
                rightClickSelectsWord: false,
                // Renderer optimizations
                rendererType: 'canvas', // Will be upgraded to WebGL if available
                allowProposedApi: true,
                // Memory optimizations
                windowsMode: false,
                macOptionIsMeta: true,
                // Disable features that can impact performance
                rightClickSelectsWord: false,
                wordSeparator: ' ()[]{}\'"`'
            });

            // Add addons with performance optimizations
            this.fitAddon = new FitAddon();
            this.terminal.loadAddon(this.fitAddon);
            this.terminal.loadAddon(new WebLinksAddon());
            this.terminal.loadAddon(new SearchAddon());

            // Try to load WebGL renderer for best performance, fallback to Canvas
            try {
                const webglAddon = new WebglAddon();
                this.terminal.loadAddon(webglAddon);
                console.log('WebGL renderer loaded for optimal performance');
            } catch (e) {
                console.warn('WebGL not available, falling back to Canvas renderer');
                try {
                    const canvasAddon = new CanvasAddon();
                    this.terminal.loadAddon(canvasAddon);
                    console.log('Canvas renderer loaded');
                } catch (e2) {
                    console.warn('Canvas renderer not available, using DOM renderer');
                }
            }

            // Get terminal container
            const terminalContainer = document.getElementById('terminal-container');
            if (!terminalContainer) {
                throw new Error('Terminal container not found');
            }

            // Clear any existing content and open terminal in container
            terminalContainer.innerHTML = ''; // Clear container
            this.terminal.open(terminalContainer);
            this.terminal.clear(); // Clear terminal content
            this.fitAddon.fit();

            // Start shell session
            await this.startShellSession(namespace, pod, container);

            // Setup event listeners
            this.setupEventListeners();

            // Show terminal
            this.showTerminal();

            return true;

        } catch (error) {
            console.error('Failed to connect to pod terminal:', error);
            this.showError('Failed to connect to pod terminal: ' + error.message);
            return false;
        }
    }

    /**
     * Start shell session via HTTP
     */
    async startShellSession(namespace, pod, container) {
        try {
            const response = await fetch('/kubernetes/shell/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    namespace: namespace,
                    pod: pod,
                    container: container
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to start shell session');
            }

            this.sessionId = data.session_id;
            this.isConnected = true;

            // Show clean connection message
            this.terminal.write('\x1b[32m✓ Connected to ' + namespace + '/' + pod + (container ? '/' + container : '') + '\x1b[0m\r\n');
            this.terminal.write('\x1b[33m⚡ Initializing shell session...\x1b[0m\r\n');

            // Focus terminal
            this.terminal.focus();

            // Start polling for output (this will get the initial working directory)
            this.startOutputPolling();

            return true;

        } catch (error) {
            console.error('Failed to start shell session:', error);
            this.terminal.write('\r\n\x1b[31mFailed to start shell: ' + error.message + '\x1b[0m\r\n');
            throw error;
        }
    }

    /**
     * Start polling for output
     */
    startOutputPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }

        this.pollingInterval = setInterval(async () => {
            if (!this.sessionId || !this.isConnected) {
                return;
            }

            try {
                const response = await fetch(`/kubernetes/shell/output/${this.sessionId}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    if (response.status === 404) {
                        this.terminal.write('\r\n\x1b[31mSession not found. Connection lost.\x1b[0m\r\n');
                        this.disconnect();
                        return;
                    }
                    throw new Error('Failed to get output');
                }

                const data = await response.json();

                // Update working directory if provided
                if (data.working_directory) {
                    this.workingDirectory = data.working_directory;
                    this.updatePrompt();
                }

                // Check if we have any output to display or if a command was executed
                const hasOutput = (data.output && data.output.length > 0) || (data.error_output && data.error_output.length > 0);
                const commandExecuted = data.command_executed || false;

                // Write output to terminal
                if (data.output && data.output.length > 0) {
                    // Check if user is scrolled up (not at bottom)
                    const wasAtBottom = this.terminal.buffer.active.viewportY + this.terminal.rows >= this.terminal.buffer.active.length;

                    this.terminal.write(data.output);

                    // Only auto-scroll to bottom if user was already at bottom
                    if (wasAtBottom) {
                        this.terminal.scrollToBottom();
                    }
                }

                if (data.error_output && data.error_output.length > 0) {
                    this.terminal.write('\x1b[31m' + data.error_output + '\x1b[0m');
                }

                // Write prompt after command execution if we have output OR if a command was executed
                if (hasOutput || commandExecuted) {
                    this.writePrompt();
                }

                // Check if process ended
                if (!data.is_running && data.exit_code !== null) {
                    this.terminal.write('\r\n\x1b[33mProcess ended with exit code: ' + data.exit_code + '\x1b[0m\r\n');
                    this.disconnect();
                }

            } catch (error) {
                console.error('Error polling output:', error);
                // Don't disconnect on temporary errors, just log them
            }
        }, 150); // Optimized polling frequency for better responsiveness
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Handle terminal input
        this.terminal.onData(async (data) => {
            if (!this.isConnected || !this.sessionId) {
                return;
            }

            // Handle different key inputs
            for (let i = 0; i < data.length; i++) {
                const char = data[i];
                const charCode = char.charCodeAt(0);

                if (charCode === 13) { // Enter key
                    if (this.currentLine.trim()) {
                        const command = this.currentLine.trim();

                        // Add command to history (avoid duplicates)
                        if (this.commandHistory.length === 0 || this.commandHistory[this.commandHistory.length - 1] !== command) {
                            this.commandHistory.push(command);
                            // Keep history size reasonable (max 1000 commands)
                            if (this.commandHistory.length > 1000) {
                                this.commandHistory.shift();
                            }
                        }
                        this.historyIndex = -1; // Reset history navigation

                        // Handle special commands locally
                        if (command === 'clear') {
                            this.terminal.clear();
                            this.currentLine = '';
                            this.cursorPosition = 0;
                            return;
                        } else if (command === 'history') {
                            this.showHistory();
                            this.currentLine = '';
                            this.cursorPosition = 0;
                            this.terminal.write('\r\n');
                            return;
                        } else if (command === 'vi' || command.startsWith('vi ') || command === 'vim' || command.startsWith('vim ')) {
                            // Try to enable WebSocket mode for VI support
                            this.terminal.write('\r\n\x1b[33m🔄 Switching to WebSocket mode for VI support...\x1b[0m\r\n');
                            await this.enableWebSocketMode();

                            // Now execute the VI command
                            await this.executeCommand(command);
                            this.currentLine = '';
                            this.cursorPosition = 0;
                            return;
                        } else if (command === 'edit' || command.startsWith('edit ')) {
                            // Quick edit helper
                            const parts = command.split(' ');
                            if (parts.length > 1) {
                                const filename = parts[1];
                                this.terminal.write('\r\n\x1b[33m📝 Quick Edit Options:\x1b[0m\r\n');
                                this.terminal.write(`\x1b[32m   nano ${filename}     (Full editor)\x1b[0m\r\n`);
                                this.terminal.write(`\x1b[32m   cat ${filename}     (View content)\x1b[0m\r\n`);
                                this.terminal.write(`\x1b[32m   cat > ${filename}   (Replace content)\x1b[0m\r\n`);
                                this.terminal.write(`\x1b[32m   cat >> ${filename}  (Append content)\x1b[0m\r\n`);
                            } else {
                                this.terminal.write('\r\n\x1b[33mUsage: edit filename.txt\x1b[0m\r\n');
                            }
                            this.currentLine = '';
                            this.cursorPosition = 0;
                            this.terminal.write('\r\n');
                            this.writePrompt();
                            return;
                        } else if (command === 'help-edit' || command === 'help edit') {
                            // File editing help
                            this.terminal.write('\r\n\x1b[36m📚 File Editing Guide for Web Terminal:\x1b[0m\r\n');
                            this.terminal.write('\r\n\x1b[33m🔧 Text Editors (Web-Compatible):\x1b[0m\r\n');
                            this.terminal.write('\x1b[32m   nano filename.txt     - Simple, user-friendly editor\x1b[0m\r\n');
                            this.terminal.write('\x1b[32m   emacs -nw file.txt    - Emacs in terminal mode\x1b[0m\r\n');
                            this.terminal.write('\r\n\x1b[33m⚡ Quick File Operations:\x1b[0m\r\n');
                            this.terminal.write('\x1b[32m   cat > file.txt        - Create/replace file content\x1b[0m\r\n');
                            this.terminal.write('\x1b[32m   cat >> file.txt       - Append to file\x1b[0m\r\n');
                            this.terminal.write('\x1b[32m   echo "text" > file    - Write single line\x1b[0m\r\n');
                            this.terminal.write('\x1b[32m   cat file.txt          - View file content\x1b[0m\r\n');
                            this.terminal.write('\r\n\x1b[33m🚫 Not Supported:\x1b[0m\r\n');
                            this.terminal.write('\x1b[31m   vi, vim               - Require direct TTY access\x1b[0m\r\n');
                            this.terminal.write('\r\n\x1b[36m💡 Tip: Use "edit filename" for quick editing options\x1b[0m\r\n');
                            this.currentLine = '';
                            this.cursorPosition = 0;
                            this.terminal.write('\r\n');
                            this.writePrompt();
                            return;
                        }

                        // Send the complete command
                        await this.executeCommand(command);
                    }
                    this.currentLine = '';
                    this.cursorPosition = 0;
                    this.terminal.write('\r\n');
                    // Don't write prompt here - it will be updated after command execution
                } else if (charCode === 127 || charCode === 8) { // Backspace/Delete
                    if (this.cursorPosition > 0) {
                        this.currentLine = this.currentLine.slice(0, this.cursorPosition - 1) +
                                         this.currentLine.slice(this.cursorPosition);
                        this.cursorPosition--;
                        this.terminal.write('\b \b');
                    }
                } else if (charCode === 9) { // Tab key - Auto completion
                    await this.handleTabCompletion();
                } else if (charCode === 27) { // Escape sequences (arrow keys, etc.)
                    // Handle escape sequences for arrow keys, etc.
                    if (i + 2 < data.length && data[i + 1] === '[') {
                        const escapeChar = data[i + 2];
                        if (escapeChar === 'D' && this.cursorPosition > 0) { // Left arrow
                            this.cursorPosition--;
                            this.terminal.write('\x1b[D');
                        } else if (escapeChar === 'C' && this.cursorPosition < this.currentLine.length) { // Right arrow
                            this.cursorPosition++;
                            this.terminal.write('\x1b[C');
                        } else if (escapeChar === 'A') { // Up arrow - Previous command
                            this.navigateHistory(-1);
                        } else if (escapeChar === 'B') { // Down arrow - Next command
                            this.navigateHistory(1);
                        }
                        i += 2; // Skip the escape sequence
                    } else {
                        // Pass through other escape sequences (for VI, nano, etc.)
                        this.terminal.write(char);
                    }
                } else if (charCode >= 32 && charCode <= 126) { // Printable characters
                    this.currentLine = this.currentLine.slice(0, this.cursorPosition) +
                                     char +
                                     this.currentLine.slice(this.cursorPosition);
                    this.cursorPosition++;
                    this.terminal.write(char);
                }
            }
        });

        // Handle window resize with debouncing for better performance
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (this.fitAddon) {
                    this.fitAddon.fit();
                }
            }, 100);
        });

        // Handle terminal container resize with debouncing
        let containerResizeTimeout;
        const resizeObserver = new ResizeObserver(() => {
            clearTimeout(containerResizeTimeout);
            containerResizeTimeout = setTimeout(() => {
                if (this.fitAddon) {
                    this.fitAddon.fit();
                }
            }, 50);
        });

        const terminalContainer = document.getElementById('terminal-container');
        if (terminalContainer) {
            resizeObserver.observe(terminalContainer);
        }

        // Add keyboard shortcuts for faster scrolling
        this.terminal.attachCustomKeyEventHandler((event) => {
            // Ctrl+Shift+PageUp/PageDown for fast page scrolling
            if (event.ctrlKey && event.shiftKey) {
                if (event.key === 'PageUp') {
                    this.terminal.scrollPages(-2); // Scroll 2 pages instead of 1
                    return false; // Prevent default
                } else if (event.key === 'PageDown') {
                    this.terminal.scrollPages(2); // Scroll 2 pages instead of 1
                    return false; // Prevent default
                }
            }

            // Shift+PageUp/PageDown for single page scrolling
            if (event.shiftKey && !event.ctrlKey) {
                if (event.key === 'PageUp') {
                    this.terminal.scrollPages(-1);
                    return false; // Prevent default
                } else if (event.key === 'PageDown') {
                    this.terminal.scrollPages(1);
                    return false; // Prevent default
                }
            }

            // Ctrl+Up/Down for line-by-line scrolling (faster)
            if (event.ctrlKey && !event.shiftKey) {
                if (event.key === 'ArrowUp') {
                    this.terminal.scrollLines(-5); // Scroll 5 lines up
                    return false; // Prevent default
                } else if (event.key === 'ArrowDown') {
                    this.terminal.scrollLines(5); // Scroll 5 lines down
                    return false; // Prevent default
                }
            }

            // Ctrl+Home/End for scroll to top/bottom
            if (event.ctrlKey) {
                if (event.key === 'Home') {
                    this.terminal.scrollToTop();
                    return false; // Prevent default
                } else if (event.key === 'End') {
                    this.terminal.scrollToBottom();
                    return false; // Prevent default
                }
            }

            return true; // Allow other keys to be processed normally
        });
    }

    /**
     * Update the prompt with current working directory
     */
    updatePrompt() {
        // Extract just the directory name for a cleaner prompt
        const dirName = this.workingDirectory === '/' ? '/' : this.workingDirectory.split('/').pop();
        this.promptPrefix = `[${dirName}]$ `;
    }

    /**
     * Write prompt to terminal
     */
    writePrompt() {
        this.terminal.write(this.promptPrefix);
    }

    /**
     * Navigate command history
     */
    navigateHistory(direction) {
        if (this.commandHistory.length === 0) {
            return;
        }

        if (direction === -1) { // Up arrow - go back in history
            if (this.historyIndex === -1) {
                this.historyIndex = this.commandHistory.length - 1;
            } else if (this.historyIndex > 0) {
                this.historyIndex--;
            }
        } else if (direction === 1) { // Down arrow - go forward in history
            if (this.historyIndex === -1) {
                return; // Already at current command
            } else if (this.historyIndex < this.commandHistory.length - 1) {
                this.historyIndex++;
            } else {
                this.historyIndex = -1; // Back to current command
                this.replaceCurrentLine('');
                return;
            }
        }

        if (this.historyIndex >= 0 && this.historyIndex < this.commandHistory.length) {
            const historyCommand = this.commandHistory[this.historyIndex];
            this.replaceCurrentLine(historyCommand);
        }
    }

    /**
     * Replace current line with new text
     */
    replaceCurrentLine(newText) {
        // Clear current line
        this.terminal.write('\r\x1b[K');
        this.terminal.write(this.promptPrefix + newText);

        this.currentLine = newText;
        this.cursorPosition = newText.length;
    }

    /**
     * Show command history
     */
    showHistory() {
        this.terminal.write('\r\n');
        if (this.commandHistory.length === 0) {
            this.terminal.write('No commands in history.\r\n');
        } else {
            this.commandHistory.forEach((cmd, index) => {
                const lineNumber = (index + 1).toString().padStart(4, ' ');
                this.terminal.write(`${lineNumber}  ${cmd}\r\n`);
            });
        }
        this.writePrompt();
    }

    /**
     * Handle tab completion
     */
    async handleTabCompletion() {
        if (!this.isConnected || !this.sessionId) {
            return;
        }

        // If no current line, just send a tab to the shell
        if (!this.currentLine.trim()) {
            await this.sendRawInput('\t');
            return;
        }

        try {
            const response = await fetch(`/kubernetes/shell/complete/${this.sessionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    partial_command: this.currentLine,
                    cursor_position: this.cursorPosition
                })
            });

            if (!response.ok) {
                // Fallback: send tab directly to shell
                await this.sendRawInput('\t');
                return;
            }

            const data = await response.json();

            if (data.completion) {
                // Replace the current line with the completion
                const newLine = data.completion;

                // Clear current line
                this.terminal.write('\r\x1b[K');
                this.terminal.write(this.promptPrefix + newLine);

                this.currentLine = newLine;
                this.cursorPosition = newLine.length;
            } else if (data.suggestions && data.suggestions.length > 0) {
                // Show multiple suggestions
                this.terminal.write('\r\n');
                const suggestions = data.suggestions.slice(0, 20); // Show more suggestions

                // Display in columns
                const maxCols = 4;
                for (let i = 0; i < suggestions.length; i += maxCols) {
                    const row = suggestions.slice(i, i + maxCols);
                    this.terminal.write(row.map(s => s.padEnd(20)).join('') + '\r\n');
                }
                this.terminal.write(this.promptPrefix + this.currentLine);

                // Position cursor correctly
                const cursorOffset = this.currentLine.length - this.cursorPosition;
                if (cursorOffset > 0) {
                    this.terminal.write('\x1b[' + cursorOffset + 'D');
                }
            } else {
                // No completion found, send tab to shell for native completion
                await this.sendRawInput('\t');
            }

        } catch (error) {
            // Fallback: send tab directly to shell
            console.debug('Tab completion failed, using fallback:', error);
            await this.sendRawInput('\t');
        }
    }

    /**
     * Send raw input directly to shell
     */
    async sendRawInput(input) {
        try {
            const response = await fetch(`/kubernetes/shell/execute/${this.sessionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    raw_input: input
                })
            });

            if (!response.ok) {
                console.debug('Failed to send raw input');
            }

        } catch (error) {
            console.debug('Failed to send raw input:', error);
        }
    }

    /**
     * Enable WebSocket mode for VI/interactive applications
     */
    async enableWebSocketMode() {
        if (this.websocketMode) {
            return; // Already in WebSocket mode
        }

        try {
            // Stop HTTP polling
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }

            // Create WebSocket connection
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsUrl = `${protocol}//${window.location.host}/kubernetes/shell/ws/${this.sessionId}`;

            this.websocket = new WebSocket(wsUrl);

            // Create and load attach addon
            this.attachAddon = new AttachAddon(this.websocket);
            this.terminal.loadAddon(this.attachAddon);

            // Set up WebSocket event handlers
            this.websocket.onopen = () => {
                this.websocketMode = true;
                this.terminal.write('\x1b[32m✓ WebSocket mode enabled - VI/VIM now supported!\x1b[0m\r\n');
                console.log('WebSocket connection established for VI support');
            };

            this.websocket.onclose = () => {
                this.websocketMode = false;
                this.terminal.write('\r\n\x1b[33m⚠️  WebSocket connection closed. Switching back to HTTP mode.\x1b[0m\r\n');
                console.log('WebSocket connection closed');

                // Restart HTTP polling
                this.startOutputPolling();
            };

            this.websocket.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.terminal.write('\r\n\x1b[31m❌ WebSocket error. Using fallback mode.\x1b[0m\r\n');

                // Fallback to HTTP mode
                this.websocketMode = false;
                this.startOutputPolling();
            };

            // Wait for connection to be established
            await new Promise((resolve, reject) => {
                const timeout = setTimeout(() => {
                    reject(new Error('WebSocket connection timeout'));
                }, 5000);

                this.websocket.onopen = () => {
                    clearTimeout(timeout);
                    this.websocketMode = true;
                    this.terminal.write('\x1b[32m✓ WebSocket mode enabled - VI/VIM now supported!\x1b[0m\r\n');
                    resolve();
                };

                this.websocket.onerror = () => {
                    clearTimeout(timeout);
                    reject(new Error('WebSocket connection failed'));
                };
            });

        } catch (error) {
            console.error('Failed to enable WebSocket mode:', error);
            this.terminal.write('\r\n\x1b[31m❌ Failed to enable WebSocket mode. Using alternatives:\x1b[0m\r\n');
            this.terminal.write('\x1b[33m💡 Try: nano filename.txt (works in HTTP mode)\x1b[0m\r\n');

            // Ensure HTTP polling is running
            if (!this.pollingInterval) {
                this.startOutputPolling();
            }

            throw error;
        }
    }

    /**
     * Execute a command
     */
    async executeCommand(command) {
        try {
            const response = await fetch(`/kubernetes/shell/execute/${this.sessionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    command: command
                })
            });

            if (!response.ok) {
                throw new Error('Failed to execute command');
            }

        } catch (error) {
            console.error('Failed to send command:', error);
            this.terminal.write('\r\n\x1b[31mFailed to execute command: ' + error.message + '\x1b[0m\r\n');
        }
    }

    /**
     * Disconnect from terminal
     */
    async disconnect() {
        // Stop polling
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }

        // Close WebSocket connection
        if (this.websocket) {
            this.websocket.close();
            this.websocket = null;
        }

        // Dispose attach addon
        if (this.attachAddon) {
            this.attachAddon.dispose();
            this.attachAddon = null;
        }

        this.websocketMode = false;

        // Terminate session
        if (this.sessionId) {
            try {
                await fetch(`/kubernetes/shell/terminate/${this.sessionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            } catch (error) {
                console.error('Failed to terminate session:', error);
            }
            this.sessionId = null;
        }

        this.isConnected = false;

        // Clean up terminal instance
        if (this.terminal) {
            this.terminal.dispose();
            this.terminal = null;
        }

        // Reset state
        this.fitAddon = null;
        this.currentLine = '';
        this.cursorPosition = 0;
        this.workingDirectory = '/';
        this.promptPrefix = '$ ';
        this.commandHistory = [];
        this.historyIndex = -1;

        this.hideTerminal();
    }

    /**
     * Show terminal panel
     */
    showTerminal() {
        const terminalPanel = document.getElementById('terminal-panel');
        if (terminalPanel) {
            terminalPanel.classList.remove('hidden');
            terminalPanel.classList.add('flex');

            // Optimized terminal display with requestAnimationFrame
            requestAnimationFrame(() => {
                if (this.fitAddon) {
                    this.fitAddon.fit();
                }
                if (this.terminal) {
                    this.terminal.focus();
                }
            });
        }
    }

    /**
     * Hide terminal panel
     */
    hideTerminal() {
        const terminalPanel = document.getElementById('terminal-panel');
        if (terminalPanel) {
            terminalPanel.classList.add('hidden');
            terminalPanel.classList.remove('flex');
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        if (this.terminal) {
            this.terminal.write('\r\n\x1b[31m' + message + '\x1b[0m\r\n');
        } else {
            alert(message);
        }
    }

    /**
     * Clear terminal
     */
    clear() {
        if (this.terminal) {
            this.terminal.clear();
        }
    }

    /**
     * Reset terminal for new session
     */
    reset() {
        // Clear terminal content
        this.clear();

        // Reset state variables
        this.currentLine = '';
        this.cursorPosition = 0;
        this.workingDirectory = '/';
        this.promptPrefix = '$ ';

        // Clear container
        const terminalContainer = document.getElementById('terminal-container');
        if (terminalContainer) {
            terminalContainer.innerHTML = '';
        }
    }


}

// Global terminal instance
window.podTerminal = new PodTerminal();

export default PodTerminal;

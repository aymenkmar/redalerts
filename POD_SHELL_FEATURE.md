# Pod Shell Access Feature

This document explains the pod shell access feature implemented in the redalertsv2 project, similar to Lens IDE functionality.

## 🚀 Features

### Real Pod Shell Access
- **Full Linux Command Support**: Execute ANY Linux command without exceptions including:
  - **System Commands**: `ps`, `top`, `htop`, `free`, `df`, `lsof`, `netstat`, `ss`
  - **File Operations**: `ls`, `cat`, `grep`, `find`, `locate`, `which`, `file`
  - **Text Editors**: `vi`, `vim`, `nano`, `emacs` (if available)
  - **Package Management**: `apt`, `yum`, `apk`, `pip`, `npm` (if available)
  - **Network Tools**: `curl`, `wget`, `ping`, `telnet`, `nc`
  - **Development Tools**: `git`, `make`, `gcc`, `python`, `node`, `java`
  - **Archive Tools**: `tar`, `gzip`, `unzip`, `zip`
  - **Process Management**: `kill`, `killall`, `jobs`, `nohup`
  - **System Info**: `uname`, `whoami`, `id`, `groups`, `env`
- **Interactive Terminal**: Real-time terminal with xterm.js for full terminal capabilities
- **Tab Completion**: Full auto-completion support for commands and file paths
  - **Command Completion**: Auto-complete Linux commands (ls, cat, grep, etc.)
  - **Path Completion**: Auto-complete file and directory paths
  - **Smart Suggestions**: Shows multiple options when available
- **Multiple Container Support**: Access specific containers within pods
- **Session Management**: Proper session handling with cleanup
- **Full Environment**: Complete Linux environment with proper PATH and shell variables

### User Interface
- **Shell Icon**: Click the terminal icon next to running pods
- **Bottom Panel Terminal**: Terminal opens at the bottom of the page (400px height)
- **Terminal Controls**: Clear and close buttons in terminal header
- **Visual Feedback**: Connection status and error messages

## 🛠️ Technical Implementation

### Backend Components

#### 1. PodShellController (`app/Http/Controllers/PodShellController.php`)
- **Session Management**: Creates and manages shell sessions
- **kubectl Integration**: Executes `kubectl exec` commands like Lens IDE
- **Process Handling**: Manages interactive shell processes
- **API Endpoints**: RESTful endpoints for terminal operations

#### 2. Routes (`routes/web.php`)
```php
Route::prefix('kubernetes/shell')->group(function () {
    Route::post('/start', [PodShellController::class, 'startShell']);
    Route::post('/execute/{sessionId}', [PodShellController::class, 'executeCommand']);
    Route::get('/output/{sessionId}', [PodShellController::class, 'getOutput']);
    Route::delete('/terminate/{sessionId}', [PodShellController::class, 'terminateShell']);
    Route::get('/sessions', [PodShellController::class, 'listSessions']);
    Route::post('/cleanup', [PodShellController::class, 'cleanup']);
});
```

### Frontend Components

#### 1. Terminal JavaScript (`resources/js/terminal.js`)
- **xterm.js Integration**: Full terminal emulation
- **HTTP Polling**: Real-time output via AJAX polling (200ms intervals)
- **Input Handling**: Sends user input to backend
- **Session Management**: Handles connection and disconnection

#### 2. Pod List Integration (`resources/views/livewire/kubernetes/workloads/pod-list.blade.php`)
- **Shell Icon**: Added to Actions column for running pods
- **Terminal Panel**: Fixed bottom panel with terminal container
- **Alpine.js Functions**: `openPodShell()` and `isPodRunning()` functions

#### 3. Dependencies (`package.json`)
```json
"dependencies": {
    "@xterm/xterm": "^5.5.0",
    "@xterm/addon-fit": "^0.10.0",
    "@xterm/addon-web-links": "^0.11.0",
    "@xterm/addon-search": "^0.15.0"
}
```

## 🔧 How It Works

### 1. Shell Session Creation
```bash
kubectl exec -i -t -n {namespace} {pod} -c {container} -- sh -c "clear; (bash || ash || sh)"
```

### 2. Communication Flow
1. **User clicks shell icon** → Frontend calls `/kubernetes/shell/start`
2. **Backend creates session** → Starts kubectl exec process
3. **Frontend polls output** → GET `/kubernetes/shell/output/{sessionId}` every 200ms
4. **User types commands** → POST `/kubernetes/shell/execute/{sessionId}`
5. **Session cleanup** → DELETE `/kubernetes/shell/terminate/{sessionId}`

### 3. Terminal Features
- **Real-time Output**: 200ms polling for responsive experience
- **Full Terminal Support**: Colors, cursor movement, special characters
- **Resize Handling**: Automatic terminal resizing
- **Error Handling**: Connection loss detection and cleanup

## 📋 Usage Instructions

### 1. Prerequisites
- Running Kubernetes cluster with kubectl access
- Pod in "Running" state
- Valid kubeconfig file uploaded

### 2. Opening Shell
1. Navigate to Pods list in Kubernetes dashboard
2. Find a pod with "Running" status
3. Click the terminal icon in the Actions column
4. Terminal panel opens at bottom of page
5. Wait for connection message

### 3. Using Terminal
- **Type commands normally**: Full Linux command support
- **Tab Completion**: Press Tab for auto-completion
  - Command completion: `ca` + Tab → `cat`, `cal`, etc.
  - Path completion: `ls /e` + Tab → `ls /etc/`
  - Multiple suggestions shown in columns when available
- **Navigation**: Use arrow keys, backspace, etc.
- **Examples**:
  - Install packages: `apt update && apt install vim`
  - Edit files: `vim /etc/hosts`
  - Navigate filesystem: `cd /app && ls -la`
  - Auto-complete paths: `cat /etc/hos` + Tab → `cat /etc/hostname`

### 4. Closing Terminal
- Click the "✕" button in terminal header
- Or call `exit` command in terminal
- Session automatically cleans up after 30 minutes of inactivity

## 🎨 UI/UX Features

### Terminal Panel
- **Fixed Position**: Bottom of page, 400px height
- **Dark Theme**: Professional terminal appearance
- **Header Controls**: Title shows namespace/pod/container, Clear and Close buttons
- **Responsive**: Automatically fits terminal size

### Shell Icon
- **Conditional Display**: Only shown for running pods
- **Visual States**: Enabled (gray) for running pods, disabled for others
- **Tooltip**: "Open Shell" on hover

### Error Handling
- **Connection Errors**: Red error messages in terminal
- **Session Loss**: Automatic detection and user notification
- **Process Ended**: Shows exit code and closes session

## 🔒 Security Considerations

### Authentication
- **Laravel Sanctum**: API authentication required
- **CSRF Protection**: All requests include CSRF tokens
- **Session Isolation**: Each user has separate shell sessions

### Process Management
- **Timeout Handling**: 30-minute session timeout
- **Resource Cleanup**: Automatic process termination
- **Error Isolation**: Failed sessions don't affect others

## 🚀 Performance

### Optimizations
- **HTTP Polling**: 200ms intervals for responsive feel
- **Session Caching**: In-memory session storage
- **Incremental Output**: Only new output sent to frontend
- **Automatic Cleanup**: Inactive sessions removed

### Resource Usage
- **Memory**: Minimal overhead per session
- **CPU**: Low impact polling
- **Network**: Efficient incremental updates

## 🔧 Configuration

### Environment Variables
```env
KUBECONFIG_PATH=/path/to/kubeconfigs
```

### Customization
- **Polling Interval**: Modify in `terminal.js` (default: 200ms)
- **Session Timeout**: Modify in `PodShellController.php` (default: 30 minutes)
- **Terminal Theme**: Customize colors in `terminal.js`

## 🐛 Troubleshooting

### Common Issues
1. **"Pod must be in Running state"**: Ensure pod status is Running
2. **"Session not found"**: Session may have timed out, refresh page
3. **"Failed to start shell"**: Check kubeconfig and pod accessibility
4. **Terminal not responding**: Check browser console for errors

### Debug Steps
1. Check browser console for JavaScript errors
2. Verify kubeconfig file exists and is valid
3. Test kubectl access manually: `kubectl exec -it pod-name -- sh`
4. Check Laravel logs for backend errors

This implementation provides a comprehensive pod shell access feature that matches the functionality of Lens IDE while being integrated into your Laravel Livewire application.

# Pod Logs Feature - Lens IDE Style

This document describes the implementation of the Pod Logs feature in the Kubernetes dashboard, designed to mimic the functionality of Lens IDE.

## Overview

The Pod Logs feature allows users to view real-time logs from Kubernetes pods directly in the web interface. Similar to Lens IDE, it provides:

- **Easy Access**: Click a logs icon next to each pod in the pods list
- **Left Panel Display**: Logs appear in a left panel similar to Lens IDE
- **Container Selection**: Support for multi-container pods
- **Configurable Lines**: Choose how many log lines to display (100, 500, 1000, 5000)
- **Download Capability**: Download logs as text files
- **Real-time Refresh**: Manually refresh logs to get latest entries

## Features

### 1. Pod Logs Icon
- Added a logs icon (📄) next to the shell icon in the pods list
- Available for all pods (not just running ones, as logs can be viewed from terminated pods)
- Uses a clean, minimal design consistent with the existing UI

### 2. Logs Panel
- **VS Code Terminal Style**: Matches the existing terminal panel design
- **Left Panel Layout**: Opens on the left side like Lens IDE
- **Dark Theme**: Black background with green text for readability
- **Responsive Design**: Adapts to different screen sizes

### 3. Container Support
- **Auto-detection**: Automatically detects containers in the pod
- **Dropdown Selector**: Shows container selector for multi-container pods
- **Default Container**: Uses the first container if only one exists
- **All Containers**: Option to view logs from all containers

### 4. Log Controls
- **Lines Selector**: Choose 100, 500, 1000, or 5000 lines
- **Refresh Button**: Manually refresh logs
- **Download Button**: Download logs as a text file
- **Close Button**: Close the logs panel

## Implementation Details

### Backend Components

#### 1. PodLogsController (`app/Http/Controllers/PodLogsController.php`)
- **getLogs()**: Fetches pod logs via Kubernetes API
- **getPodContainers()**: Gets container information for a pod

#### 2. KubernetesService Updates (`app/Services/KubernetesService.php`)
- **getPodLogs()**: New method to fetch logs using `/api/v1/namespaces/{namespace}/pods/{pod}/log`
- **getPodDetails()**: Get detailed pod information including containers
- **makeK8SRequest()**: Updated to support raw text responses (for logs)

#### 3. Routes (`routes/web.php`)
```php
Route::prefix('kubernetes/logs')->group(function () {
    Route::get('/get', [PodLogsController::class, 'getLogs']);
    Route::get('/containers', [PodLogsController::class, 'getPodContainers']);
});
```

### Frontend Components

#### 1. Logs Button
Added to the pod list table (`resources/views/livewire/kubernetes/workloads/pod-list.blade.php`):
```html
<button @click="openPodLogs(pod.metadata?.namespace || 'default', pod.metadata?.name || '')"
        class="p-1 rounded hover:bg-gray-100 text-gray-600 hover:text-gray-800"
        title="View Logs">
    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
        <!-- Logs icon SVG -->
    </svg>
</button>
```

#### 2. Logs Panel
```html
<div id="logs-panel" class="hidden fixed bottom-0 left-0 right-0 terminal-vscode z-50">
    <!-- Header with controls -->
    <div class="terminal-header">
        <!-- Container selector, lines selector, refresh, download, close buttons -->
    </div>
    <!-- Logs content area -->
    <div id="logs-container" class="w-full h-full bg-black text-green-400 font-mono">
        <div id="logs-content">Loading logs...</div>
    </div>
</div>
```

#### 3. JavaScript Functions
- **openPodLogs()**: Opens the logs panel for a specific pod
- **fetchPodContainers()**: Gets container information
- **loadPodLogs()**: Fetches and displays logs
- **refreshLogs()**: Refreshes the current logs
- **downloadLogs()**: Downloads logs as a text file
- **closeLogs()**: Closes the logs panel

## API Endpoints

### GET `/kubernetes/logs/get`
Fetches pod logs.

**Parameters:**
- `cluster`: Cluster name (required)
- `namespace`: Pod namespace (required)
- `pod`: Pod name (required)
- `container`: Container name (optional)
- `lines`: Number of lines to fetch (optional, default: 1000)

**Response:**
```json
{
    "logs": "2024-01-01T10:00:00Z Log line 1\n2024-01-01T10:00:01Z Log line 2\n...",
    "pod": "my-pod",
    "namespace": "default",
    "container": "my-container",
    "lines": 1000
}
```

### GET `/kubernetes/logs/containers`
Gets container information for a pod.

**Parameters:**
- `cluster`: Cluster name (required)
- `namespace`: Pod namespace (required)
- `pod`: Pod name (required)

**Response:**
```json
{
    "containers": [
        {
            "name": "main-container",
            "image": "nginx:latest"
        },
        {
            "name": "sidecar",
            "image": "busybox:latest"
        }
    ],
    "pod": "my-pod",
    "namespace": "default"
}
```

## Usage

1. **Navigate to Pods**: Go to Kubernetes > Workloads > Pods
2. **Select Cluster**: Ensure a cluster is selected
3. **Click Logs Icon**: Click the logs icon (📄) next to any pod
4. **View Logs**: The logs panel opens showing the pod's logs
5. **Select Container**: If multiple containers, use the dropdown to select one
6. **Adjust Lines**: Use the lines selector to change how many lines to display
7. **Refresh**: Click refresh to get the latest logs
8. **Download**: Click download to save logs as a text file
9. **Close**: Click the X button to close the logs panel

## Styling

The logs panel uses the same VS Code-inspired styling as the terminal:
- **Dark Theme**: Black background (#1e1e1e)
- **Green Text**: Terminal-style green text (#00ff00)
- **Monospace Font**: Consistent with terminal display
- **Rounded Borders**: Modern, clean appearance
- **Shadow Effects**: Subtle depth and focus

## Future Enhancements

1. **Real-time Streaming**: Implement WebSocket-based log streaming
2. **Log Filtering**: Add search and filter capabilities
3. **Log Levels**: Color-code different log levels (ERROR, WARN, INFO, DEBUG)
4. **Timestamps**: Toggle timestamp display
5. **Follow Mode**: Auto-scroll to follow new log entries
6. **Multiple Pods**: View logs from multiple pods simultaneously
7. **Log Export**: Export logs in different formats (JSON, CSV)

## Security Considerations

- **Authentication**: Uses existing Laravel authentication
- **Authorization**: Respects Kubernetes RBAC permissions
- **Input Validation**: All inputs are validated and sanitized
- **CSRF Protection**: All requests include CSRF tokens
- **Error Handling**: Graceful error handling with user-friendly messages

## Troubleshooting

### Common Issues

1. **"No logs available"**: Pod may not have generated logs yet
2. **"Failed to fetch logs"**: Check cluster connectivity and pod status
3. **"Container not found"**: Container may have been removed or renamed
4. **Empty logs panel**: Check browser console for JavaScript errors
5. **"Multiple root elements detected"**: Fixed by moving cluster selection script to layout file

### Debug Steps

1. Check browser console for JavaScript errors
2. Verify cluster is selected and accessible
3. Ensure pod exists and is accessible via kubectl
4. Check Laravel logs for backend errors
5. Verify kubeconfig file is valid and accessible

### Recent Fixes

- **Livewire Multiple Root Elements**: Moved the `window.selectedCluster` script from the pod-list component to the main Kubernetes layout file to resolve Livewire's single root element requirement
- **Cluster Selection**: Now uses `session('selectedCluster')` in the layout file to ensure the selected cluster is available globally

This implementation provides a comprehensive pod logs viewing experience that closely matches the functionality and user experience of Lens IDE while being fully integrated into the Laravel Livewire application.

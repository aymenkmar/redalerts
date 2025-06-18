# kubectl-ai Integration Documentation

## Overview

This document describes the kubectl-ai integration in redalertsv2, which provides an AI-powered chatbot widget for interacting with Kubernetes clusters using natural language.

## Features

- **AI-Powered Kubernetes Assistant**: Chat with an AI that understands kubectl commands and Kubernetes concepts
- **Multi-Cluster Support**: Automatically switches context when you navigate between clusters
- **Security**: Built-in security measures to prevent dangerous operations
- **Dual Frontend Support**: Works in both React and Laravel Livewire interfaces
- **Session Management**: Maintains separate chat sessions for each cluster

## Installation

### Prerequisites

1. kubectl must be installed and accessible
2. Valid kubeconfig files for your clusters
3. AI provider API key (Gemini, OpenAI, etc.)

### Setup

1. **kubectl-ai Binary** (already included in project):
   - Location: `storage/kubectl-ai-google/kubectl-ai`
   - Version: 0.0.14
   - The service automatically uses the project-local binary
   - Falls back to system installation if needed

2. **Configure Environment Variables**:
   Add the following to your `.env` file:
   ```env
   # kubectl-ai Configuration
   KUBECTL_AI_MODEL=gemini-2.5-flash-preview-04-17
   KUBECTL_AI_PROVIDER=gemini
   KUBECTL_AI_MAX_ITERATIONS=20
   GEMINI_API_KEY=your_gemini_api_key_here
   OPENAI_API_KEY=your_openai_api_key_here
   ```

3. **Test Installation**:
   Visit `/api/kubectl-ai/test` to verify the installation.

## Usage

### Accessing the Chatbot

The chatbot widget appears as a floating button in the bottom-right corner of the screen when:
- You are logged in
- A cluster is selected
- You are on any page within the application

### Basic Commands

The AI assistant can help with various Kubernetes operations:

**Information Queries:**
- "Show me all pods in the default namespace"
- "What's the status of my deployments?"
- "List all services"
- "Describe the nginx pod"

**Troubleshooting:**
- "Why is my pod not starting?"
- "Check the logs for the failing deployment"
- "What's wrong with my service?"

**Resource Management:**
- "Scale my deployment to 3 replicas"
- "Create a service for my app"
- "Show me resource usage"

### Security Features

The integration includes several security measures:

1. **Rate Limiting**: 10 requests per minute per user
2. **Command Filtering**: Dangerous commands are blocked
3. **Input Sanitization**: Prevents command injection
4. **Audit Logging**: All requests are logged for security monitoring

### Blocked Operations

For security, the following operations are restricted:
- `delete`, `destroy`, `remove`, `rm`
- `kill`, `terminate`, `drain`
- `exec`, `port-forward`, `proxy`
- Direct file system access
- Shell command injection attempts

## API Endpoints

### POST /api/kubectl-ai/chat
Send a message to the AI assistant.

**Request:**
```json
{
  "message": "Show me all pods",
  "cluster": "my-cluster",
  "conversation_id": "optional-conversation-id"
}
```

**Response:**
```json
{
  "success": true,
  "response": "Here are all the pods in your cluster...",
  "conversation_id": "chat_12345",
  "cluster": "my-cluster",
  "execution_time": 2.5,
  "timestamp": "2024-01-01T12:00:00Z"
}
```

### GET /api/kubectl-ai/config
Get current configuration.

### GET /api/kubectl-ai/models
Get available AI models.

### POST /api/kubectl-ai/validate
Validate kubectl-ai setup for a specific cluster.

### GET /api/kubectl-ai/test
Test the kubectl-ai installation.

## Configuration

### AI Providers

Supported providers:
- **Gemini** (default): Requires `GEMINI_API_KEY`
- **OpenAI**: Requires `OPENAI_API_KEY`
- **Ollama**: For local models
- **Grok**: Requires `GROK_API_KEY`

### Customization

You can customize the behavior by modifying:
- `app/Services/KubectlAiService.php`: Core service logic
- `app/Http/Middleware/KubectlAiSecurity.php`: Security rules
- Frontend components for UI customization

## Troubleshooting

### Common Issues

1. **"No cluster selected"**
   - Ensure a cluster is selected in the cluster selector
   - Check that kubeconfig files exist in the configured path

2. **"AI service configuration error"**
   - Verify API keys are set correctly
   - Check internet connectivity
   - Ensure the AI provider service is accessible

3. **"Cannot connect to Kubernetes cluster"**
   - Verify kubeconfig file is valid
   - Check cluster connectivity
   - Ensure kubectl can access the cluster

4. **Rate limit exceeded**
   - Wait a minute before making more requests
   - Consider increasing rate limits in the middleware

### Debug Mode

Enable debug logging by setting `LOG_LEVEL=debug` in your `.env` file.

### Logs

Check the following log files:
- Laravel logs: `storage/logs/laravel.log`
- kubectl-ai trace: `/tmp/kubectl-ai-trace.txt`

## Security Considerations

1. **API Keys**: Store securely and rotate regularly
2. **User Permissions**: Ensure users have appropriate cluster access
3. **Network Security**: Use HTTPS in production
4. **Audit Logs**: Monitor for suspicious activity
5. **Rate Limiting**: Adjust based on your needs

## Development

### Adding New Features

1. **Backend**: Extend `KubectlAiService` or `KubectlAiController`
2. **Frontend**: Modify React or Livewire components
3. **Security**: Update `KubectlAiSecurity` middleware as needed

### Testing

Run tests to ensure functionality:
```bash
# Test API endpoints
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost/api/kubectl-ai/test

# Test with a cluster
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"message":"get pods","cluster":"your-cluster"}' \
  http://localhost/api/kubectl-ai/chat
```

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review logs for error details
3. Consult the kubectl-ai documentation: https://github.com/GoogleCloudPlatform/kubectl-ai
4. Contact your system administrator

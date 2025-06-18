<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class KubectlAiSecurity
{
    /**
     * Dangerous kubectl commands that should be restricted
     */
    private array $dangerousCommands = [
        'delete',
        'destroy',
        'remove',
        'rm',
        'kill',
        'terminate',
        'drain',
        'cordon',
        'uncordon',
        'taint',
        'patch',
        'replace',
        'scale',
        'rollout',
        'restart',
        'exec',
        'port-forward',
        'proxy',
        'cp',
        'attach'
    ];

    /**
     * Sensitive resource types that require extra caution
     */
    private array $sensitiveResources = [
        'secrets',
        'configmaps',
        'serviceaccounts',
        'roles',
        'rolebindings',
        'clusterroles',
        'clusterrolebindings',
        'persistentvolumes',
        'persistentvolumeclaims',
        'storageclasses',
        'networkpolicies',
        'podsecuritypolicies'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rate limiting
        if (!$this->checkRateLimit($request)) {
            return response()->json([
                'error' => 'Rate limit exceeded. Please wait before making another request.',
                'type' => 'rate_limit'
            ], 429);
        }

        // Validate request structure
        if (!$this->validateRequest($request)) {
            return response()->json([
                'error' => 'Invalid request format',
                'type' => 'validation_error'
            ], 400);
        }

        // Check message content for security issues
        $message = $request->input('message', '');
        $securityCheck = $this->analyzeMessage($message);
        
        if (!$securityCheck['safe']) {
            Log::warning('kubectl-ai security violation', [
                'user_id' => auth()->id(),
                'message' => $message,
                'reason' => $securityCheck['reason'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'error' => $securityCheck['reason'],
                'type' => 'security_violation',
                'suggestions' => $securityCheck['suggestions'] ?? []
            ], 403);
        }

        // Log the request for audit purposes
        $this->logRequest($request);

        return $next($request);
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(Request $request): bool
    {
        $key = 'kubectl-ai:' . ($request->user()?->id ?? $request->ip());
        
        // Allow 10 requests per minute per user/IP
        return RateLimiter::attempt($key, 10, function() {
            // This callback is executed if the rate limit is not exceeded
        }, 60);
    }

    /**
     * Validate request structure
     */
    private function validateRequest(Request $request): bool
    {
        // Check required fields
        if (!$request->has('message') || !$request->has('cluster')) {
            return false;
        }

        // Check message length
        $message = $request->input('message');
        if (strlen($message) > 1000) {
            return false;
        }

        // Check cluster name format
        $cluster = $request->input('cluster');
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $cluster)) {
            return false;
        }

        return true;
    }

    /**
     * Analyze message for security issues
     */
    private function analyzeMessage(string $message): array
    {
        $message = strtolower(trim($message));
        
        // Check for dangerous commands
        foreach ($this->dangerousCommands as $command) {
            if (strpos($message, $command) !== false) {
                return [
                    'safe' => false,
                    'reason' => "Command '$command' is restricted for security reasons.",
                    'suggestions' => [
                        'Try using read-only commands like "get", "describe", or "list"',
                        'Ask for information about resources instead of modifying them'
                    ]
                ];
            }
        }

        // Check for sensitive resources with modification intent
        $modificationWords = ['create', 'update', 'modify', 'change', 'set', 'edit', 'apply'];
        foreach ($this->sensitiveResources as $resource) {
            if (strpos($message, $resource) !== false) {
                foreach ($modificationWords as $modWord) {
                    if (strpos($message, $modWord) !== false) {
                        return [
                            'safe' => false,
                            'reason' => "Modification of sensitive resource '$resource' is restricted.",
                            'suggestions' => [
                                'You can view this resource using "get" or "describe" commands',
                                'Contact your cluster administrator for modification requests'
                            ]
                        ];
                    }
                }
            }
        }

        // Check for shell injection attempts
        $shellPatterns = [
            '/[;&|`$(){}[\]\\\\]/',
            '/\b(bash|sh|zsh|fish|cmd|powershell)\b/',
            '/\b(sudo|su|chmod|chown)\b/',
            '/\b(wget|curl|nc|netcat)\b/',
            '/\b(base64|eval|exec)\b/'
        ];

        foreach ($shellPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return [
                    'safe' => false,
                    'reason' => 'Message contains potentially dangerous shell commands or characters.',
                    'suggestions' => [
                        'Use natural language to describe what you want to do',
                        'Avoid special characters and shell commands'
                    ]
                ];
            }
        }

        // Check for file system access attempts
        $fileSystemPatterns = [
            '/\/etc\//',
            '/\/var\//',
            '/\/tmp\//',
            '/\/home\//',
            '/\/root\//',
            '/\.\.\//',
            '/\~\//'
        ];

        foreach ($fileSystemPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return [
                    'safe' => false,
                    'reason' => 'File system access patterns detected.',
                    'suggestions' => [
                        'Focus on Kubernetes resources and operations',
                        'Avoid file system paths in your queries'
                    ]
                ];
            }
        }

        // Check message length and complexity
        if (strlen($message) > 500) {
            return [
                'safe' => false,
                'reason' => 'Message is too long. Please keep queries concise.',
                'suggestions' => [
                    'Break down complex requests into smaller parts',
                    'Focus on one specific task per message'
                ]
            ];
        }

        return ['safe' => true];
    }

    /**
     * Log request for audit purposes
     */
    private function logRequest(Request $request): void
    {
        Log::info('kubectl-ai request', [
            'user_id' => auth()->id(),
            'cluster' => $request->input('cluster'),
            'message_length' => strlen($request->input('message', '')),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }
}

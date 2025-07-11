<?php

namespace App\Services;

use App\Models\WebsiteUrl;
use App\Models\WebsiteMonitoringLog;
use App\Services\WebsiteNotificationService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Carbon\Carbon;

class WebsiteMonitoringService
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify' => false, // For SSL validation, we'll handle this separately
            'allow_redirects' => true,
            'headers' => [
                'User-Agent' => 'RedAlerts Website Monitor/1.0',
            ],
        ]);
    }

    /**
     * Monitor a website URL for all enabled check types.
     */
    public function monitorWebsiteUrl(WebsiteUrl $websiteUrl): array
    {
        $results = [];

        if ($websiteUrl->monitor_status) {
            $results['status'] = $this->checkStatus($websiteUrl);
        }

        if ($websiteUrl->monitor_domain) {
            $results['domain'] = $this->checkDomain($websiteUrl);
        }

        if ($websiteUrl->monitor_ssl) {
            $results['ssl'] = $this->checkSSL($websiteUrl);
        }

        // Update the overall status
        $this->updateOverallStatus($websiteUrl, $results);

        return $results;
    }

    /**
     * Check the HTTP status of a website.
     */
    public function checkStatus(WebsiteUrl $websiteUrl): array
    {
        $startTime = microtime(true);
        $checkedAt = Carbon::now();

        try {
            $response = $this->httpClient->get($websiteUrl->url);
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $statusCode = $response->getStatusCode();

            $status = $statusCode === 200 ? 'up' : 'down';
            $errorMessage = $statusCode !== 200 ? "HTTP {$statusCode}" : null;

            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'status',
                'status' => $status,
                'response_time' => $responseTime,
                'status_code' => $statusCode,
                'error_message' => $errorMessage,
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            // Update URL status and response data
            $websiteUrl->update([
                'response_time' => $responseTime,
                'status_code' => $statusCode,
            ]);

            // Update the status properly (this will trigger overall status update)
            $websiteUrl->updateStatus($status, $errorMessage);

            return [
                'status' => $status,
                'response_time' => $responseTime,
                'status_code' => $statusCode,
                'error' => $errorMessage,
            ];

        } catch (ConnectException $e) {
            // Handle connection-specific errors (connection refused, timeout, etc.)
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $errorMessage = $this->formatConnectionError($e->getMessage());

            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'status',
                'status' => 'down',
                'response_time' => $responseTime,
                'status_code' => null,
                'error_message' => $errorMessage,
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            // Update the status properly (this will trigger overall status update)
            $websiteUrl->updateStatus('down', $errorMessage);

            return [
                'status' => 'down',
                'response_time' => $responseTime,
                'status_code' => null,
                'error' => $errorMessage,
            ];
        } catch (RequestException $e) {
            // Handle other HTTP-related errors
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;
            $errorMessage = $e->getMessage();

            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'status',
                'status' => 'down',
                'response_time' => $responseTime,
                'status_code' => $statusCode,
                'error_message' => $errorMessage,
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            // Update the status properly (this will trigger overall status update)
            $websiteUrl->updateStatus('down', $errorMessage);

            return [
                'status' => 'down',
                'response_time' => $responseTime,
                'status_code' => $statusCode,
                'error' => $errorMessage,
            ];
        } catch (GuzzleException $e) {
            // Handle any other Guzzle-related errors
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $errorMessage = $e->getMessage();

            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'status',
                'status' => 'down',
                'response_time' => $responseTime,
                'status_code' => null,
                'error_message' => $errorMessage,
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            // Update the status properly (this will trigger overall status update)
            $websiteUrl->updateStatus('down', $errorMessage);

            return [
                'status' => 'down',
                'response_time' => $responseTime,
                'status_code' => null,
                'error' => $errorMessage,
            ];
        }
    }

    /**
     * Check domain expiration date.
     */
    public function checkDomain(WebsiteUrl $websiteUrl): array
    {
        $checkedAt = Carbon::now();
        $url = parse_url($websiteUrl->url);
        $domain = $url['host'] ?? null;

        if (!$domain) {
            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'domain',
                'status' => 'error',
                'error_message' => 'Invalid domain',
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            return [
                'status' => 'error',
                'error' => 'Invalid domain',
            ];
        }

        // Clean domain name for whois lookup (remove www. prefix)
        $cleanDomain = $this->cleanDomainForWhois($domain);

        try {
            // Use whois command to get domain expiration date with timeout
            $whoisOutput = shell_exec("timeout 10 whois " . escapeshellarg($cleanDomain) . " 2>&1");

            if (empty($whoisOutput)) {
                throw new \Exception('Failed to retrieve whois information - server may be unavailable');
            }

            // Check for common whois error messages
            if (stripos($whoisOutput, 'connection refused') !== false) {
                throw new \Exception('Whois server connection refused');
            }

            if (stripos($whoisOutput, 'no match') !== false || stripos($whoisOutput, 'not found') !== false) {
                throw new \Exception('Domain not found in whois database');
            }

            // Parse expiration date from whois output
            $expirationDate = $this->parseExpirationDateFromWhois($whoisOutput);

            if (!$expirationDate) {
                throw new \Exception('Could not parse expiration date from whois output');
            }

            $now = Carbon::now();

            // Calculate days until expiry (positive = future, negative = past)
            $daysUntilExpiry = $expirationDate->diffInDays($now, false);

            // For display purposes, show absolute value as integer
            $daysUntilExpiryDisplay = (int) abs($daysUntilExpiry);

            $status = 'up';
            if ($expirationDate->isPast()) {
                $status = 'down';
            } elseif ($daysUntilExpiry <= 30) {
                $status = 'warning';
            }

            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'domain',
                'status' => $status,
                'additional_data' => [
                    'domain' => $domain,
                    'expiration_date' => $expirationDate->toISOString(),
                    'days_until_expiry' => $daysUntilExpiryDisplay,
                    'is_expired' => $expirationDate->isPast(),
                ],
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            // Check if domain expiry notification should be sent (< 30 days)
            if ($daysUntilExpiryDisplay <= 30 && !$expirationDate->isPast()) {
                $this->checkDomainExpiryNotification($websiteUrl, $daysUntilExpiryDisplay);
            }

            return [
                'status' => $status,
                'expiration_date' => $expirationDate,
                'days_until_expiry' => $daysUntilExpiryDisplay,
                'is_expired' => $expirationDate->isPast(),
                'domain' => $domain,
            ];

        } catch (\Exception $e) {
            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'domain',
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check SSL certificate.
     */
    public function checkSSL(WebsiteUrl $websiteUrl): array
    {
        $checkedAt = Carbon::now();
        $url = parse_url($websiteUrl->url);
        $domain = $url['host'] ?? null;
        $port = $url['port'] ?? 443;

        if (!$domain || $url['scheme'] !== 'https') {
            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'ssl',
                'status' => 'error',
                'error_message' => 'Not an HTTPS URL',
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            return [
                'status' => 'error',
                'error' => 'Not an HTTPS URL',
            ];
        }

        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $socket = stream_socket_client(
                "ssl://{$domain}:{$port}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$socket) {
                throw new \Exception("Failed to connect: {$errstr}");
            }

            $cert = stream_context_get_params($socket)['options']['ssl']['peer_certificate'];
            $certInfo = openssl_x509_parse($cert);

            $expiryDate = Carbon::createFromTimestamp($certInfo['validTo_time_t']);
            $now = Carbon::now();

            // Calculate days until expiry (positive = future, negative = past)
            $daysUntilExpiry = $expiryDate->diffInDays($now, false);

            // For display purposes, show absolute value as integer
            $daysUntilExpiryDisplay = (int) abs($daysUntilExpiry);

            $status = 'up';
            if ($expiryDate->isPast()) {
                $status = 'down';
            } elseif ($daysUntilExpiry <= 7) {
                $status = 'warning';
            }

            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'ssl',
                'status' => $status,
                'additional_data' => [
                    'issuer' => $certInfo['issuer']['CN'] ?? 'Unknown',
                    'subject' => $certInfo['subject']['CN'] ?? 'Unknown',
                    'valid_from' => Carbon::createFromTimestamp($certInfo['validFrom_time_t'])->toISOString(),
                    'valid_to' => $expiryDate->toISOString(),
                    'days_until_expiry' => $daysUntilExpiryDisplay,
                    'is_expired' => $expiryDate->isPast(),
                ],
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            // Check if SSL expiry notification should be sent (< 30 days)
            if ($daysUntilExpiryDisplay <= 30 && !$expiryDate->isPast()) {
                $this->checkSslExpiryNotification($websiteUrl, $daysUntilExpiryDisplay);
            }

            fclose($socket);

            return [
                'status' => $status,
                'expiry_date' => $expiryDate,
                'days_until_expiry' => $daysUntilExpiryDisplay,
                'is_expired' => $expiryDate->isPast(),
                'issuer' => $certInfo['issuer']['CN'] ?? 'Unknown',
                'subject' => $certInfo['subject']['CN'] ?? 'Unknown',
            ];

        } catch (\Exception $e) {
            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'ssl',
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if SSL expiry notification should be sent.
     */
    private function checkSslExpiryNotification(WebsiteUrl $websiteUrl, int $daysUntilExpiry): void
    {
        $now = now();
        $lastNotificationSent = $websiteUrl->ssl_warning_notification_sent_at;

        // Send notification if:
        // 1. Never sent before, OR
        // 2. Last notification was sent more than 24 hours ago
        $shouldSend = !$lastNotificationSent ||
                     $lastNotificationSent->diffInHours($now) >= 24;

        if ($shouldSend) {
            $notificationService = new WebsiteNotificationService();
            $notificationService->sendSslExpiryWarning($websiteUrl, $daysUntilExpiry);
        }
    }

    /**
     * Check if domain expiry notification should be sent.
     */
    private function checkDomainExpiryNotification(WebsiteUrl $websiteUrl, int $daysUntilExpiry): void
    {
        $now = now();
        $lastNotificationSent = $websiteUrl->domain_warning_notification_sent_at;

        // Send notification if:
        // 1. Never sent before, OR
        // 2. Last notification was sent more than 24 hours ago
        $shouldSend = !$lastNotificationSent ||
                     $lastNotificationSent->diffInHours($now) >= 24;

        if ($shouldSend) {
            $notificationService = new WebsiteNotificationService();
            $notificationService->sendDomainExpiryWarning($websiteUrl, $daysUntilExpiry);
        }
    }

    /**
     * Update the overall status of a website URL based on check results.
     */
    private function updateOverallStatus(WebsiteUrl $websiteUrl, array $results): void
    {
        $statuses = array_column($results, 'status');

        // Priority order: down > warning > up
        // Note: 'error' status from domain/SSL checks should not override 'up' status from HTTP checks
        // Only 'down' status from HTTP checks should mark the website as down

        if (isset($results['status']) && $results['status']['status'] === 'down') {
            // HTTP status check failed - this is the most critical
            $overallStatus = 'down';
        } elseif (in_array('down', $statuses)) {
            // Other checks (domain/SSL) are down
            $overallStatus = 'warning';
        } elseif (in_array('warning', $statuses)) {
            // Some checks have warnings
            $overallStatus = 'warning';
        } elseif (isset($results['status']) && $results['status']['status'] === 'up') {
            // HTTP status is up - this is the primary indicator
            $overallStatus = 'up';
        } else {
            // Fallback
            $overallStatus = 'unknown';
        }

        $websiteUrl->updateStatus($overallStatus);
    }

    /**
     * Parse expiration date from whois output.
     */
    private function parseExpirationDateFromWhois(string $whoisOutput): ?Carbon
    {
        // Common patterns for expiration dates in whois output
        $patterns = [
            // Registry Expiry Date: 2024-12-31T23:59:59Z
            '/Registry Expiry Date:\s*(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z?)/i',
            // Registrar Registration Expiration Date: 2025-07-20T16:00:14+02:00
            '/Registrar Registration Expiration Date:\s*(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2})/i',
            // Expiry Date: 31-Dec-2024
            '/Expiry Date:\s*(\d{1,2}-\w{3}-\d{4})/i',
            // Expiration Date: 2024-12-31
            '/Expiration Date:\s*(\d{4}-\d{2}-\d{2})/i',
            // expires: 2024-12-31
            '/expires:\s*(\d{4}-\d{2}-\d{2})/i',
            // Expiry : 31/12/2024
            '/Expiry\s*:\s*(\d{1,2}\/\d{1,2}\/\d{4})/i',
            // expire: 20241231
            '/expire:\s*(\d{8})/i',
            // Expiration Time: 2024-12-31 23:59:59
            '/Expiration Time:\s*(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/i',
            // paid-till: 2024-12-31T23:59:59Z
            '/paid-till:\s*(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z?)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $whoisOutput, $matches)) {
                $dateString = $matches[1];

                try {
                    // Handle different date formats
                    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z?$/', $dateString)) {
                        // ISO format: 2024-12-31T23:59:59Z
                        return Carbon::parse($dateString);
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $dateString)) {
                        // ISO format with timezone: 2025-07-20T16:00:14+02:00
                        return Carbon::parse($dateString);
                    } elseif (preg_match('/^\d{1,2}-\w{3}-\d{4}$/', $dateString)) {
                        // Format: 31-Dec-2024
                        return Carbon::createFromFormat('d-M-Y', $dateString);
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
                        // Format: 2024-12-31
                        return Carbon::createFromFormat('Y-m-d', $dateString);
                    } elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $dateString)) {
                        // Format: 31/12/2024
                        return Carbon::createFromFormat('d/m/Y', $dateString);
                    } elseif (preg_match('/^\d{8}$/', $dateString)) {
                        // Format: 20241231
                        return Carbon::createFromFormat('Ymd', $dateString);
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/', $dateString)) {
                        // Format: 2024-12-31 23:59:59
                        return Carbon::createFromFormat('Y-m-d H:i:s', $dateString);
                    }
                } catch (\Exception $e) {
                    // Continue to next pattern if parsing fails
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Clean domain name for whois lookup.
     */
    private function cleanDomainForWhois(string $domain): string
    {
        // Remove www. prefix if present
        if (str_starts_with(strtolower($domain), 'www.')) {
            $domain = substr($domain, 4);
        }

        $domain = strtolower($domain);

        // Extract root domain for subdomains
        // This handles cases like subdomain.example.com -> example.com
        $parts = explode('.', $domain);
        if (count($parts) > 2) {
            // For most cases, take the last two parts (domain.tld)
            // Special handling for known multi-part TLDs could be added here if needed
            $domain = implode('.', array_slice($parts, -2));
        }

        return $domain;
    }

    /**
     * Format connection error messages to be more user-friendly.
     */
    private function formatConnectionError(string $errorMessage): string
    {
        // Extract meaningful information from cURL error messages
        if (strpos($errorMessage, 'Connection refused') !== false) {
            return 'Connection refused - The server is not accepting connections';
        }

        if (strpos($errorMessage, 'Connection timed out') !== false) {
            return 'Connection timed out - The server did not respond in time';
        }

        if (strpos($errorMessage, 'Could not resolve host') !== false) {
            return 'DNS resolution failed - The domain name could not be resolved';
        }

        if (strpos($errorMessage, 'SSL connect error') !== false) {
            return 'SSL connection failed - Unable to establish secure connection';
        }

        if (strpos($errorMessage, 'Operation timed out') !== false) {
            return 'Request timed out - The server took too long to respond';
        }

        // Return the original message if no specific pattern is matched
        return $errorMessage;
    }
}

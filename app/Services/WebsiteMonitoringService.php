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
     * Check domain validation.
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

        try {
            $dnsRecords = dns_get_record($domain, DNS_A);
            $status = !empty($dnsRecords) ? 'up' : 'down';
            $errorMessage = empty($dnsRecords) ? 'No DNS records found' : null;

            $logData = [
                'website_url_id' => $websiteUrl->id,
                'check_type' => 'domain',
                'status' => $status,
                'error_message' => $errorMessage,
                'additional_data' => ['dns_records' => $dnsRecords],
                'checked_at' => $checkedAt,
            ];

            WebsiteMonitoringLog::create($logData);

            return [
                'status' => $status,
                'dns_records' => $dnsRecords,
                'error' => $errorMessage,
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

        if (in_array('down', $statuses) || in_array('error', $statuses)) {
            $overallStatus = 'down';
        } elseif (in_array('warning', $statuses)) {
            $overallStatus = 'warning';
        } else {
            $overallStatus = 'up';
        }

        $websiteUrl->updateStatus($overallStatus);
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

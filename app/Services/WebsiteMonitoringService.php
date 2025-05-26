<?php

namespace App\Services;

use App\Models\WebsiteUrl;
use App\Models\WebsiteMonitoringLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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

        } catch (RequestException $e) {
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
}

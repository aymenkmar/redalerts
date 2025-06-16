<?php

namespace App\Services;

use Ovh\Api;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class OvhApiService
{
    private Api $client;

    public function __construct()
    {
        $this->client = new Api(
            Config::get('services.ovh.api_key'),
            Config::get('services.ovh.api_secret'),
            Config::get('services.ovh.api_endpoint'),
            Config::get('services.ovh.consumer_key')
        );
    }

    /**
     * Get all VPS services
     */
    public function getVpsServices(): array
    {
        try {
            $vpsServices = [];
            $vpsIds = $this->client->get('/vps');
            
            foreach ($vpsIds as $vpsId) {
                $vpsDetails = $this->client->get("/vps/{$vpsId}");
                $serviceInfo = $this->client->get("/vps/{$vpsId}/serviceInfos");

                // Get the service ID from serviceInfo to fetch additional service details
                $serviceId = $serviceInfo['serviceId'] ?? null;
                $serviceDetails = null;

                if ($serviceId) {
                    try {
                        $serviceDetails = $this->client->get("/service/{$serviceId}");
                    } catch (\Exception $e) {
                        Log::warning("Could not fetch service details for service ID: {$serviceId}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $vpsServices[] = [
                    'service_name' => $vpsId,
                    'display_name' => $vpsDetails['displayName'] ?? $vpsId,
                    'state' => $vpsDetails['state'] ?? 'unknown',
                    'expiration_date' => isset($serviceDetails['nextBillingDate']) ? Carbon::parse($serviceDetails['nextBillingDate']) : null,
                    'engagement_date' => isset($serviceDetails['engagementDate']) ? Carbon::parse($serviceDetails['engagementDate']) : null,
                    'renewal_type' => $serviceInfo['renew']['automatic'] ?? false ? 'automatic' : 'manual',
                    'raw_data' => [
                        'details' => $vpsDetails,
                        'service_info' => $serviceInfo,
                        'service_details' => $serviceDetails
                    ]
                ];
            }
            
            return $vpsServices;
        } catch (\Exception $e) {
            Log::error('Failed to fetch VPS services from OVH API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Get all dedicated server services
     */
    public function getDedicatedServerServices(): array
    {
        try {
            $serverServices = [];
            $serverIds = $this->client->get('/dedicated/server');
            
            foreach ($serverIds as $serverId) {
                $serverDetails = $this->client->get("/dedicated/server/{$serverId}");
                $serviceInfo = $this->client->get("/dedicated/server/{$serverId}/serviceInfos");

                // Get the service ID from serviceInfo to fetch additional service details
                $serviceId = $serviceInfo['serviceId'] ?? null;
                $serviceDetails = null;

                if ($serviceId) {
                    try {
                        $serviceDetails = $this->client->get("/service/{$serviceId}");
                    } catch (\Exception $e) {
                        Log::warning("Could not fetch service details for service ID: {$serviceId}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $serverServices[] = [
                    'service_name' => $serverId,
                    'display_name' => $serverDetails['iam']['displayName'] ?? $serverDetails['name'] ?? $serverId,
                    'state' => $serverDetails['state'] ?? 'unknown',
                    'expiration_date' => isset($serviceDetails['nextBillingDate']) ? Carbon::parse($serviceDetails['nextBillingDate']) : null,
                    'engagement_date' => isset($serviceDetails['engagementDate']) ? Carbon::parse($serviceDetails['engagementDate']) : null,
                    'renewal_type' => $serviceInfo['renew']['automatic'] ?? false ? 'automatic' : 'manual',
                    'raw_data' => [
                        'details' => $serverDetails,
                        'service_info' => $serviceInfo,
                        'service_details' => $serviceDetails
                    ]
                ];
            }
            
            return $serverServices;
        } catch (\Exception $e) {
            Log::error('Failed to fetch dedicated server services from OVH API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Get all domain services
     */
    public function getDomainServices(): array
    {
        try {
            $domainServices = [];
            $domainIds = $this->client->get('/domain');
            
            foreach ($domainIds as $domainId) {
                $serviceInfo = $this->client->get("/domain/{$domainId}/serviceInfos");

                // Get the service ID from serviceInfo to fetch additional service details
                $serviceId = $serviceInfo['serviceId'] ?? null;
                $serviceDetails = null;

                if ($serviceId) {
                    try {
                        $serviceDetails = $this->client->get("/service/{$serviceId}");
                    } catch (\Exception $e) {
                        Log::warning("Could not fetch service details for service ID: {$serviceId}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $domainServices[] = [
                    'service_name' => $domainId,
                    'display_name' => $domainId,
                    'state' => 'active', // Domains don't have a state like VPS/servers
                    'expiration_date' => isset($serviceDetails['nextBillingDate']) ? Carbon::parse($serviceDetails['nextBillingDate']) : null,
                    'engagement_date' => isset($serviceDetails['engagementDate']) ? Carbon::parse($serviceDetails['engagementDate']) : null,
                    'renewal_type' => $serviceInfo['renew']['automatic'] ?? false ? 'automatic' : 'manual',
                    'raw_data' => [
                        'service_info' => $serviceInfo,
                        'service_details' => $serviceDetails
                    ]
                ];
            }
            
            return $domainServices;
        } catch (\Exception $e) {
            Log::error('Failed to fetch domain services from OVH API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Get all services combined
     */
    public function getAllServices(): array
    {
        return [
            'vps' => $this->getVpsServices(),
            'dedicated_servers' => $this->getDedicatedServerServices(),
            'domains' => $this->getDomainServices()
        ];
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $this->client->get('/me');
            return true;
        } catch (\Exception $e) {
            Log::error('OVH API connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

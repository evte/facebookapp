<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;

class DomainHealthCheckService
{
    /**
     * Check single tenant domain health status
     */
    public function checkTenant(Tenant $tenant): array
    {
        $results = [
            'dns_status' => 'unchecked',
            'ssl_status' => 'none',
            'is_accessible' => false,
            'check_message' => '',
        ];

        if (empty($tenant->domain)) {
            $results['check_message'] = 'No domain configured';
            return $results;
        }

        try {
            // 1. Check DNS resolution
            $dnsResult = $this->checkDNS($tenant->domain);
            $results['dns_status'] = $dnsResult['status'];
            $results['check_message'] = $dnsResult['message'];

            // 2. Check domain accessibility
            if ($dnsResult['status'] === 'configured') {
                $accessResult = $this->checkAccessibility($tenant->domain);
                $results['is_accessible'] = $accessResult['accessible'];
                $results['ssl_status'] = $accessResult['ssl_status'];

                if (!$accessResult['accessible']) {
                    $results['check_message'] .= "\n" . $accessResult['message'];
                }
            }

            // 3. Update tenant record
            $tenant->update([
                'dns_status' => $results['dns_status'],
                'ssl_status' => $results['ssl_status'],
                'is_accessible' => $results['is_accessible'],
                'last_checked_at' => now(),
                'check_message' => $results['check_message'],
            ]);

            return $results;

        } catch (\Exception $e) {
            $results['check_message'] = 'Check failed: ' . $e->getMessage();

            $tenant->update([
                'dns_status' => 'error',
                'last_checked_at' => now(),
                'check_message' => $results['check_message'],
            ]);

            return $results;
        }
    }

    /**
     * Check DNS configuration
     */
    protected function checkDNS(string $domain): array
    {
        // Get server IP
        $serverIp = $this->getServerIp();

        // Resolve domain
        $records = @dns_get_record($domain, DNS_A);

        if (empty($records)) {
            return [
                'status' => 'not_configured',
                'message' => "Domain has no DNS records or cannot be resolved",
            ];
        }

        // Check if points to this server
        $domainIps = array_column($records, 'ip');

        if (in_array($serverIp, $domainIps)) {
            return [
                'status' => 'configured',
                'message' => "DNS correctly configured, pointing to {$serverIp}",
            ];
        }

        if (in_array('127.0.0.1', $domainIps) || in_array('::1', $domainIps)) {
            return [
                'status' => 'localhost',
                'message' => "Domain points to localhost (development environment)",
            ];
        }

        return [
            'status' => 'misconfigured',
            'message' => "Domain points to " . implode(', ', $domainIps) . ", but server IP is {$serverIp}",
        ];
    }

    /**
     * Check domain accessibility
     */
    protected function checkAccessibility(string $domain): array
    {
        $result = [
            'accessible' => false,
            'ssl_status' => 'none',
            'message' => '',
        ];

        // Check HTTPS first
        try {
            $response = Http::timeout(5)->get("https://{$domain}");

            if ($response->successful()) {
                $result['accessible'] = true;
                $result['ssl_status'] = 'valid';
                return $result;
            }
        } catch (\Exception $e) {
            // HTTPS failed, try HTTP
        }

        // Check HTTP
        try {
            $response = Http::timeout(5)->get("http://{$domain}");

            if ($response->successful()) {
                $result['accessible'] = true;
                $result['ssl_status'] = 'none';
                $result['message'] = 'Domain accessible but no SSL certificate configured';
                return $result;
            }
        } catch (\Exception $e) {
            $result['message'] = 'Domain not accessible: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Get server public IP
     */
    protected function getServerIp(): string
    {
        // Try from environment variable
        if ($ip = env('SERVER_IP')) {
            return $ip;
        }

        // Local development environment
        if (app()->environment('local')) {
            return '127.0.0.1';
        }

        // Try to get public IP
        try {
            $response = Http::timeout(3)->get('https://api.ipify.org');
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Exception $e) {
            // Fail to default
        }

        return gethostbyname(gethostname());
    }

    /**
     * Get DNS status label
     */
    public static function getDnsStatusLabel(string $status): string
    {
        return match($status) {
            'configured' => 'Configured',
            'not_configured' => 'Not Configured',
            'misconfigured' => 'Misconfigured',
            'localhost' => 'Localhost',
            'error' => 'Check Failed',
            default => 'Unchecked',
        };
    }

    /**
     * Get SSL status label
     */
    public static function getSslStatusLabel(string $status): string
    {
        return match($status) {
            'valid' => 'Valid',
            'invalid' => 'Invalid Certificate',
            'expired' => 'Expired',
            'none' => 'Not Configured',
            default => 'Unchecked',
        };
    }

    /**
     * 获取状态颜色
     */
    public static function getStatusColor(string $status): string
    {
        return match($status) {
            'configured', 'valid' => 'success',
            'localhost' => 'info',
            'not_configured', 'none' => 'warning',
            'misconfigured', 'invalid', 'expired', 'error' => 'danger',
            default => 'gray',
        };
    }
}

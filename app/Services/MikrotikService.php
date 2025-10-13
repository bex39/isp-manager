<?php

// app/Services/MikrotikService.php

namespace App\Services;

use App\Models\Router;
use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected $client;
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->connect();
    }

    private function connect()
    {
        try {
            $this->client = new Client([
                'host' => $this->router->ip_address,
                'user' => $this->router->username,
                'pass' => $this->router->password,
                'port' => $this->router->api_port ?? 8728,
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to connect to router: " . $e->getMessage());
        }
    }

    /**
     * Test Connection - TAMBAHKAN METHOD INI
     */
    public function testConnection()
    {
        try {
            $query = new Query('/system/resource/print');
            $response = $this->client->query($query)->read();

            if (empty($response)) {
                return [
                    'success' => false,
                    'message' => 'No response from router'
                ];
            }

            return [
                'success' => true,
                'message' => 'Router is online and responding',
                'data' => $response[0] ?? []
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create PPPoE User
     */
    public function createPPPoEUser($username, $password, $profile = 'default')
    {
        try {
            // Check if user already exists
            $query = new Query('/ppp/secret/print');
            $query->where('name', $username);
            $existing = $this->client->query($query)->read();

            if (!empty($existing)) {
                throw new \Exception("PPPoE user already exists");
            }

            // Create user
            $query = new Query('/ppp/secret/add');
            $query->equal('name', $username);
            $query->equal('password', $password);
            $query->equal('profile', $profile);
            $query->equal('service', 'pppoe');

            $this->client->query($query)->read();

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create PPPoE user: " . $e->getMessage());
        }
    }

    /**
     * Delete PPPoE User
     */
    public function deletePPPoEUser($username)
    {
        try {
            // Find user
            $query = new Query('/ppp/secret/print');
            $query->where('name', $username);
            $users = $this->client->query($query)->read();

            if (empty($users)) {
                return true; // Already deleted
            }

            // Delete user
            $query = new Query('/ppp/secret/remove');
            $query->equal('.id', $users[0]['.id']);
            $this->client->query($query)->read();

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to delete PPPoE user: " . $e->getMessage());
        }
    }

    /**
     * Create/Update PPPoE Profile (for bandwidth control)
     */
    public function createProfile($profileName, $downloadSpeed, $uploadSpeed)
    {
        try {
            // Format: 10M = 10 Mbps
            $downloadLimit = $downloadSpeed . 'M';
            $uploadLimit = $uploadSpeed . 'M';

            // Check if profile exists
            $query = new Query('/ppp/profile/print');
            $query->where('name', $profileName);
            $existing = $this->client->query($query)->read();

            if (!empty($existing)) {
                // Update existing profile
                $query = new Query('/ppp/profile/set');
                $query->equal('.id', $existing[0]['.id']);
                $query->equal('rate-limit', $uploadLimit . '/' . $downloadLimit);
                $this->client->query($query)->read();
            } else {
                // Create new profile
                $query = new Query('/ppp/profile/add');
                $query->equal('name', $profileName);
                $query->equal('rate-limit', $uploadLimit . '/' . $downloadLimit);
                $this->client->query($query)->read();
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create/update profile: " . $e->getMessage());
        }
    }

    /**
     * Change User Speed
     */
    public function changeUserSpeed($username, $downloadSpeed, $uploadSpeed)
    {
        try {
            $profileName = "profile_" . $downloadSpeed . "M";

            // Create/Update profile
            $this->createProfile($profileName, $downloadSpeed, $uploadSpeed);

            // Update user profile
            $query = new Query('/ppp/secret/print');
            $query->where('name', $username);
            $users = $this->client->query($query)->read();

            if (empty($users)) {
                throw new \Exception("User not found");
            }

            $query = new Query('/ppp/secret/set');
            $query->equal('.id', $users[0]['.id']);
            $query->equal('profile', $profileName);
            $this->client->query($query)->read();

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to change user speed: " . $e->getMessage());
        }
    }

    /**
     * Get Active PPPoE Sessions
     */
    public function getActiveSessions()
    {
        try {
            $query = new Query('/ppp/active/print');
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            throw new \Exception("Failed to get active sessions: " . $e->getMessage());
        }
    }

    /**
     * Disconnect User
     */
    public function disconnectUser($username)
    {
        try {
            $query = new Query('/ppp/active/print');
            $query->where('name', $username);
            $sessions = $this->client->query($query)->read();

            if (empty($sessions)) {
                return true; // Not connected
            }

            $query = new Query('/ppp/active/remove');
            $query->equal('.id', $sessions[0]['.id']);
            $this->client->query($query)->read();

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to disconnect user: " . $e->getMessage());
        }
    }


        /**
     * Disable PPPoE user (suspend)
     */
    public function disablePPPoEUser(string $username)
    {
        $users = $this->client->query('/ppp/secret/print', [
            '?name' => $username
        ])->read();

        if (empty($users)) {
            throw new \Exception("User {$username} not found");
        }

        $userId = $users[0]['.id'];

        $this->client->query('/ppp/secret/set', [
            '.id' => $userId,
            'disabled' => 'yes'
        ])->read();

        return true;
    }

    /**
     * Enable PPPoE user (activate)
     */
    public function enablePPPoEUser(string $username)
    {
        $users = $this->client->query('/ppp/secret/print', [
            '?name' => $username
        ])->read();

        if (empty($users)) {
            throw new \Exception("User {$username} not found");
        }

        $userId = $users[0]['.id'];

        $this->client->query('/ppp/secret/set', [
            '.id' => $userId,
            'disabled' => 'no'
        ])->read();

        return true;
    }

    /**
     * Get Router System Info
     */
    public function getSystemInfo()
    {
        try {
            $query = new Query('/system/resource/print');
            $resource = $this->client->query($query)->read();

            $query = new Query('/system/identity/print');
            $identity = $this->client->query($query)->read();

            return [
                'identity' => $identity[0]['name'] ?? 'Unknown',
                'uptime' => $resource[0]['uptime'] ?? 'N/A',
                'version' => $resource[0]['version'] ?? 'N/A',
                'cpu_load' => $resource[0]['cpu-load'] ?? 0,
                'free_memory' => $resource[0]['free-memory'] ?? 0,
                'total_memory' => $resource[0]['total-memory'] ?? 0,
            ];
        } catch (\Exception $e) {
            throw new \Exception("Failed to get system info: " . $e->getMessage());
        }
    }
}

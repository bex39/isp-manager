<?php

namespace App\Services;

use App\Models\OLT;
use phpseclib3\Net\SSH2;

class OLTService
{
    protected $ssh;
    protected $olt;

    public function __construct(OLT $olt)
    {
        $this->olt = $olt;
    }

    /**
     * Connect to OLT via SSH
     */
    public function connect()
    {
        try {
            $this->ssh = new SSH2($this->olt->ip_address, $this->olt->ssh_port);

            if (!$this->ssh->login($this->olt->username, $this->olt->password)) {
                throw new \Exception('SSH Authentication failed');
            }

            // Update last seen
            $this->olt->update(['last_seen' => now()]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute command on OLT
     */
    public function executeCommand($command)
    {
        if (!$this->ssh) {
            $this->connect();
        }

        return $this->ssh->exec($command);
    }

    /**
     * Get ONT list on specific PON port
     */
    public function getONTList($ponPort)
    {
        $this->connect();

        // Command varies by OLT type
        $command = match($this->olt->olt_type) {
            'huawei' => "display ont info summary {$ponPort}",
            'zte' => "show pon power attenuation gpon-onu_{$ponPort}",
            'fiberhome' => "show pon onu-info all {$ponPort}",
            default => "show ont info {$ponPort}"
        };

        $output = $this->executeCommand($command);

        return $this->parseONTList($output);
    }

    /**
     * Parse ONT list output
     */
    private function parseONTList($output)
    {
        // This is simplified - you'll need to adjust based on your OLT output format
        $onts = [];
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            // Parse each line to extract ONT info
            // Format varies by OLT vendor
            if (preg_match('/(\d+)\s+(\S+)\s+(\w+)\s+(-?\d+\.?\d*)\s+(-?\d+\.?\d*)/', $line, $matches)) {
                $onts[] = [
                    'id' => $matches[1],
                    'sn' => $matches[2],
                    'status' => $matches[3],
                    'rx_power' => $matches[4],
                    'tx_power' => $matches[5],
                ];
            }
        }

        return $onts;
    }

    /**
     * Get specific ONT status
     */
    public function getONTStatus($ponPort, $ontId)
    {
        $this->connect();

        $command = match($this->olt->olt_type) {
            'huawei' => "display ont optical-info {$ponPort} {$ontId}",
            'zte' => "show gpon onu detail-info gpon-onu_{$ponPort}:{$ontId}",
            default => "show ont status {$ponPort} {$ontId}"
        };

        $output = $this->executeCommand($command);

        return $this->parseONTStatus($output);
    }

    /**
     * Parse ONT status
     */
    private function parseONTStatus($output)
    {
        // Simplified parsing
        $status = [
            'online' => false,
            'rx_power' => 0,
            'tx_power' => 0,
            'distance' => 0,
            'uptime' => '',
        ];

        // Extract RX power
        if (preg_match('/RX.*?(-?\d+\.?\d*)/', $output, $matches)) {
            $status['rx_power'] = floatval($matches[1]);
        }

        // Extract TX power
        if (preg_match('/TX.*?(-?\d+\.?\d*)/', $output, $matches)) {
            $status['tx_power'] = floatval($matches[1]);
        }

        // Check online status
        $status['online'] = stripos($output, 'online') !== false;

        return $status;
    }

    /**
     * Test OLT connection
     */
    public function testConnection()
    {
        try {
            $this->connect();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Disconnect
     */
    public function disconnect()
    {
        if ($this->ssh) {
            $this->ssh->disconnect();
        }
    }
}

<?php

namespace App\Services;

use App\Models\ONT;
use App\Models\OLT;
use phpseclib3\Net\SSH2;
use Exception;

class ONTProvisioningService
{
    /**
     * Provision ONT to OLT
     */
    public function provisionONT(ONT $ont, array $config = []): array
    {
        $olt = $ont->olt;

        if (!$olt) {
            return ['success' => false, 'message' => 'OLT not found'];
        }

        try {
            // Detect OLT brand and call appropriate method
            $brand = strtolower($olt->brand ?? '');

            if (str_contains($brand, 'huawei')) {
                return $this->provisionHuaweiOLT($ont, $olt, $config);
            } elseif (str_contains($brand, 'zte')) {
                return $this->provisionZTEOLT($ont, $olt, $config);
            } elseif (str_contains($brand, 'fiberhome')) {
                return $this->provisionFiberhomeOLT($ont, $olt, $config);
            } else {
                return ['success' => false, 'message' => 'OLT brand not supported for auto-provisioning'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Provision to Huawei OLT (MA5800, MA5608T, etc)
     */
    private function provisionHuaweiOLT(ONT $ont, OLT $olt, array $config): array
    {
        $ssh = new SSH2($olt->ip_address, $olt->ssh_port ?? 23);

        if (!$ssh->login($olt->username, $olt->password)) {
            return ['success' => false, 'message' => 'Failed to connect to OLT'];
        }

        // Set terminal length
        $ssh->write("screen-length 0 temporary\n");
        sleep(1);
        $ssh->write("enable\n");
        sleep(1);
        $ssh->write("config\n");
        sleep(1);

        // Huawei ONT provisioning
        $frame = 0;
        $slot = $ont->pon_port; // PON slot
        $port = $ont->pon_port; // PON port
        $ontId = $ont->ont_id;
        $sn = $ont->sn;

        $commands = [
            "interface gpon {$frame}/{$slot}",
            "ont add {$port} {$ontId} sn-auth {$sn} omci ont-lineprofile-id 1 ont-srvprofile-id 1 desc \"{$ont->name}\"",
            "ont port native-vlan {$port} {$ontId} eth 1 vlan 100 priority 0",
            "quit",
            "service-port vlan 100 gpon {$frame}/{$slot}/{$port} ont {$ontId} gemport 1 multi-service user-vlan 100",
        ];

        $output = '';
        foreach ($commands as $cmd) {
            $ssh->write($cmd . "\n");
            sleep(1);
            $output .= $ssh->read();
        }

        $ssh->write("quit\n");
        $ssh->write("quit\n");
        $ssh->write("save\n");
        $ssh->write("y\n");

        $ont->update(['status' => 'online', 'last_seen' => now()]);

        return [
            'success' => true,
            'message' => 'ONT provisioned successfully on Huawei OLT',
            'output' => $output
        ];
    }

    /**
     * Provision to ZTE OLT (C300, C320, etc)
     */
    private function provisionZTEOLT(ONT $ont, OLT $olt, array $config): array
    {
        $ssh = new SSH2($olt->ip_address, $olt->ssh_port ?? 23);

        if (!$ssh->login($olt->username, $olt->password)) {
            return ['success' => false, 'message' => 'Failed to connect to OLT'];
        }

        $ssh->write("enable\n");
        sleep(1);
        $ssh->write("config terminal\n");
        sleep(1);

        // ZTE provisioning
        $rack = 1;
        $shelf = 1;
        $slot = $ont->pon_port;
        $ontId = $ont->ont_id;
        $sn = $ont->sn;

        $commands = [
            "interface gpon-olt_{$rack}/{$shelf}/{$slot}",
            "onu {$ontId} type ROUTER sn {$sn}",
            "exit",
            "interface gpon-onu_{$rack}/{$shelf}/{$slot}:{$ontId}",
            "name \"{$ont->name}\"",
            "tcont 1 profile UP-1G",
            "gemport 1 tcont 1",
            "switchport mode hybrid vport 1",
            "service-port 1 vport 1 user-vlan 100 vlan 100",
            "exit",
        ];

        $output = '';
        foreach ($commands as $cmd) {
            $ssh->write($cmd . "\n");
            sleep(1);
            $output .= $ssh->read();
        }

        $ssh->write("exit\n");
        $ssh->write("write\n");

        $ont->update(['status' => 'online', 'last_seen' => now()]);

        return [
            'success' => true,
            'message' => 'ONT provisioned successfully on ZTE OLT',
            'output' => $output
        ];
    }

    /**
     * Provision to Fiberhome OLT (AN5516, AN5116, etc)
     */
    private function provisionFiberhomeOLT(ONT $ont, OLT $olt, array $config): array
    {
        $ssh = new SSH2($olt->ip_address, $olt->ssh_port ?? 23);

        if (!$ssh->login($olt->username, $olt->password)) {
            return ['success' => false, 'message' => 'Failed to connect to OLT'];
        }

        $ssh->write("enable\n");
        sleep(1);
        $ssh->write("config\n");
        sleep(1);

        // Fiberhome EPON/GPON
        $ponPort = $ont->pon_port;
        $ontId = $ont->ont_id;
        $sn = $ont->sn;

        if ($ont->pon_type === 'EPON') {
            $commands = [
                "interface EPON0/{$ponPort}",
                "epon bind-onu mac {$sn} {$ontId}",
                "epon onu-profile {$ontId} PROFILE1",
                "exit",
                "interface EPON0/{$ponPort}:{$ontId}",
                "description \"{$ont->name}\"",
                "service-profile PROFILE1",
                "exit",
            ];
        } else {
            // GPON
            $commands = [
                "interface GPON0/{$ponPort}",
                "ont add {$ontId} sn {$sn}",
                "ont profile {$ontId} line-profile PROFILE1 service-profile PROFILE1",
                "ont description {$ontId} \"{$ont->name}\"",
                "exit",
            ];
        }

        $output = '';
        foreach ($commands as $cmd) {
            $ssh->write($cmd . "\n");
            sleep(1);
            $output .= $ssh->read();
        }

        $ssh->write("exit\n");
        $ssh->write("write\n");

        $ont->update(['status' => 'online', 'last_seen' => now()]);

        return [
            'success' => true,
            'message' => 'ONT provisioned successfully on Fiberhome OLT',
            'output' => $output
        ];
    }

    /**
     * Get ONT signal/optical power from OLT
     */
    public function getONTSignal(ONT $ont): array
    {
        $olt = $ont->olt;

        if (!$olt) {
            return ['success' => false, 'message' => 'OLT not found'];
        }

        try {
            $ssh = new SSH2($olt->ip_address, $olt->ssh_port ?? 23);

            if (!$ssh->login($olt->username, $olt->password)) {
                return ['success' => false, 'message' => 'Failed to connect'];
            }

            $brand = strtolower($olt->brand ?? '');
            $command = '';

            if (str_contains($brand, 'huawei')) {
                $command = "display ont optical-info {$ont->pon_port} {$ont->ont_id}";
            } elseif (str_contains($brand, 'zte')) {
                $command = "show gpon onu detail-info gpon-onu_1/{$ont->pon_port}:{$ont->ont_id}";
            } elseif (str_contains($brand, 'fiberhome')) {
                $command = "show onu-optical-info EPON0/{$ont->pon_port}:{$ont->ont_id}";
            }

            $ssh->write($command . "\n");
            sleep(2);
            $output = $ssh->read();

            // Parse output untuk extract RX/TX power
            preg_match('/Rx.*?(-?\d+\.\d+)/', $output, $rxMatch);
            preg_match('/Tx.*?(-?\d+\.\d+)/', $output, $txMatch);

            $rxPower = $rxMatch[1] ?? null;
            $txPower = $txMatch[1] ?? null;

            if ($rxPower) {
                $ont->update([
                    'rx_power' => $rxPower,
                    'tx_power' => $txPower,
                    'status' => 'online',
                    'last_seen' => now()
                ]);
            }

            return [
                'success' => true,
                'rx_power' => $rxPower,
                'tx_power' => $txPower,
                'output' => $output
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Configure WiFi on ONT
     */
    public function configureWiFi(ONT $ont, string $ssid, string $password): array
    {
        if (!$ont->management_ip) {
            return ['success' => false, 'message' => 'ONT management IP not set'];
        }

        try {
            // This is generic - need to adapt based on ONT model
            $ssh = new SSH2($ont->management_ip);

            if (!$ssh->login($ont->username ?? 'admin', $ont->password ?? 'admin')) {
                return ['success' => false, 'message' => 'Failed to connect to ONT'];
            }

            // Generic WiFi commands (adapt per ONT model)
            $commands = [
                "wifi set ssid {$ssid}",
                "wifi set password {$password}",
                "wifi apply",
            ];

            foreach ($commands as $cmd) {
                $ssh->write($cmd . "\n");
                sleep(1);
            }

            $ont->update([
                'wifi_ssid' => $ssid,
                'wifi_password' => $password
            ]);

            return ['success' => true, 'message' => 'WiFi configured successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete ONT from OLT
     */
    public function deleteONTFromOLT(ONT $ont): array
    {
        $olt = $ont->olt;

        if (!$olt) {
            return ['success' => false, 'message' => 'OLT not found'];
        }

        try {
            $ssh = new SSH2($olt->ip_address, $olt->ssh_port ?? 23);

            if (!$ssh->login($olt->username, $olt->password)) {
                return ['success' => false, 'message' => 'Failed to connect'];
            }

            $brand = strtolower($olt->brand ?? '');

            if (str_contains($brand, 'huawei')) {
                $ssh->write("config\n");
                sleep(1);
                $ssh->write("interface gpon 0/{$ont->pon_port}\n");
                sleep(1);
                $ssh->write("ont delete {$ont->pon_port} {$ont->ont_id}\n");
                sleep(1);
                $ssh->write("quit\n");
                $ssh->write("quit\n");
            } elseif (str_contains($brand, 'zte')) {
                $ssh->write("config terminal\n");
                sleep(1);
                $ssh->write("interface gpon-olt_1/1/{$ont->pon_port}\n");
                sleep(1);
                $ssh->write("no onu {$ont->ont_id}\n");
                sleep(1);
                $ssh->write("exit\n");
            }

            return ['success' => true, 'message' => 'ONT deleted from OLT'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

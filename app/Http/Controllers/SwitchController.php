<?php

namespace App\Http\Controllers;

use App\Models\NetworkSwitch;
use Illuminate\Http\Request;
use phpseclib3\Net\SSH2;

class SwitchController extends Controller
{
    public function index()
    {
        $switches = NetworkSwitch::latest()->paginate(20);
        return view('switches.index', compact('switches'));
    }

    public function create()
    {
        return view('switches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'ip_address' => 'required|ip|unique:switches',
            'brand' => 'required|string',
            'model' => 'nullable|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'ssh_port' => 'nullable|integer',
            'port_count' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['ssh_port'] = $validated['ssh_port'] ?? 22;
        NetworkSwitch::create($validated);

        return redirect()->route('switches.index')->with('success', 'Switch created!');
    }

    public function show(NetworkSwitch $switch)
    {
        return view('switches.show', compact('switch'));
    }

    public function edit(NetworkSwitch $switch)
    {
        return view('switches.edit', compact('switch'));
    }

    public function update(Request $request, NetworkSwitch $switch)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'ip_address' => 'required|ip|unique:switches,ip_address,' . $switch->id,
            'brand' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
        ]);

        $switch->update($validated);
        return redirect()->route('switches.show', $switch)->with('success', 'Updated!');
    }

    public function destroy(NetworkSwitch $switch)
    {
        $switch->delete();
        return redirect()->route('switches.index')->with('success', 'Deleted!');
    }

    public function sshTerminal(NetworkSwitch $switch)
    {
        return view('switches.ssh-terminal', compact('switch'));
    }

    public function executeSSH(Request $request, NetworkSwitch $switch)
    {
        try {
            $ssh = new SSH2($switch->ip_address, $switch->ssh_port);

            if (!$ssh->login($switch->username, $switch->password)) {
                return response()->json(['success' => false, 'output' => 'Login failed']);
            }

            $output = $ssh->exec($request->command);

            return response()->json(['success' => true, 'output' => $output]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'output' => $e->getMessage()]);
        }
    }

    public function ping(NetworkSwitch $switch)
    {
        exec("ping -c 4 {$switch->ip_address}", $output, $result);

        $isOnline = $result === 0;
        $latency = null;

        foreach($output as $line) {
            if(preg_match('/time=(\d+\.?\d*)/', $line, $matches)) {
                $latency = (int)$matches[1];
                break;
            }
        }

        $switch->update([
            'status' => $isOnline ? 'online' : 'offline',
            'ping_latency' => $latency,
            'last_seen' => $isOnline ? now() : $switch->last_seen
        ]);

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'latency' => $latency
        ]);
    }
}

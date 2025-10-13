@extends('layouts.admin')

@section('title', 'SSH Terminal - ' . $router->name)
@section('page-title', 'SSH Terminal: ' . $router->name)

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('routers.show', $router) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Router
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-terminal"></i>
                <strong>{{ $router->name }}</strong> ({{ $router->ip_address }})
            </div>
            <button class="btn btn-danger btn-sm" id="clearTerminal">
                <i class="bi bi-trash"></i> Clear
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Terminal Output -->
        <div id="terminal" style="background: #1e1e1e; color: #00ff00; font-family: 'Courier New', monospace; padding: 20px; min-height: 500px; max-height: 500px; overflow-y: auto;">
            <div class="terminal-line">MikroTik SSH Terminal Ready...</div>
            <div class="terminal-line">Connected to: {{ $router->ip_address }}</div>
            <div class="terminal-line">Type your commands below.</div>
            <div class="terminal-line">---</div>
        </div>

        <!-- Command Input -->
        <div class="p-3 bg-dark">
            <div class="input-group">
                <span class="input-group-text bg-secondary text-white border-0">
                    <i class="bi bi-chevron-right"></i>
                </span>
                <input type="text" id="commandInput" class="form-control bg-dark text-white border-0"
                       placeholder="Enter command (e.g., /system resource print)"
                       style="font-family: 'Courier New', monospace;"
                       autocomplete="off">
                <button class="btn btn-success" id="executeBtn">
                    <i class="bi bi-play-fill"></i> Execute
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Commands -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Quick Commands</h6>
        <div class="btn-group btn-group-sm flex-wrap" role="group">
            <button class="btn btn-outline-primary quick-cmd" data-cmd="/system resource print">System Resources</button>
            <button class="btn btn-outline-primary quick-cmd" data-cmd="/interface print">Interfaces</button>
            <button class="btn btn-outline-primary quick-cmd" data-cmd="/ip address print">IP Addresses</button>
            <button class="btn btn-outline-primary quick-cmd" data-cmd="/ppp active print">Active PPPoE</button>
            <button class="btn btn-outline-primary quick-cmd" data-cmd="/ppp secret print">PPPoE Secrets</button>
            <button class="btn btn-outline-primary quick-cmd" data-cmd="/system identity print">Identity</button>
            <button class="btn btn-outline-primary quick-cmd" data-cmd="/system routerboard print">RouterBoard</button>
            <button class="btn btn-outline-primary quick-cmd" data-cmd="/log print">Logs</button>
        </div>
    </div>
</div>

<style>
#terminal {
    scrollbar-width: thin;
    scrollbar-color: #00ff00 #1e1e1e;
}

#terminal::-webkit-scrollbar {
    width: 8px;
}

#terminal::-webkit-scrollbar-track {
    background: #1e1e1e;
}

#terminal::-webkit-scrollbar-thumb {
    background: #00ff00;
    border-radius: 4px;
}

.terminal-line {
    margin-bottom: 5px;
    word-wrap: break-word;
}

.terminal-command {
    color: #00ffff;
    font-weight: bold;
}

.terminal-error {
    color: #ff4444;
}

.terminal-success {
    color: #00ff00;
}
</style>
@endsection

@push('scripts')
<script>
const terminal = document.getElementById('terminal');
const commandInput = document.getElementById('commandInput');
const executeBtn = document.getElementById('executeBtn');
const clearBtn = document.getElementById('clearTerminal');

// Command history
let commandHistory = [];
let historyIndex = -1;

function addTerminalLine(text, className = '') {
    const line = document.createElement('div');
    line.className = `terminal-line ${className}`;
    line.textContent = text;
    terminal.appendChild(line);
    terminal.scrollTop = terminal.scrollHeight;
}

function executeCommand() {
    const command = commandInput.value.trim();

    if (!command) return;

    // Add to history
    commandHistory.push(command);
    historyIndex = commandHistory.length;

    // Show command in terminal
    addTerminalLine(`> ${command}`, 'terminal-command');

    // Disable input
    commandInput.disabled = true;
    executeBtn.disabled = true;
    addTerminalLine('Executing...', 'text-warning');

    // Execute via AJAX
    fetch('{{ route("routers.ssh-command", $router) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ command: command })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Split output by newlines and add each line
            const lines = data.output.split('\n');
            lines.forEach(line => {
                if (line.trim()) {
                    addTerminalLine(line, 'terminal-success');
                }
            });
        } else {
            addTerminalLine(data.output, 'terminal-error');
        }
    })
    .catch(error => {
        addTerminalLine('Error: ' + error.message, 'terminal-error');
    })
    .finally(() => {
        commandInput.value = '';
        commandInput.disabled = false;
        executeBtn.disabled = false;
        commandInput.focus();
        addTerminalLine('---');
    });
}

// Execute button click
executeBtn.addEventListener('click', executeCommand);

// Enter key to execute
commandInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        executeCommand();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (historyIndex > 0) {
            historyIndex--;
            commandInput.value = commandHistory[historyIndex];
        }
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (historyIndex < commandHistory.length - 1) {
            historyIndex++;
            commandInput.value = commandHistory[historyIndex];
        } else {
            historyIndex = commandHistory.length;
            commandInput.value = '';
        }
    }
});

// Clear terminal
clearBtn.addEventListener('click', function() {
    terminal.innerHTML = '<div class="terminal-line">Terminal cleared.</div>';
});

// Quick commands
document.querySelectorAll('.quick-cmd').forEach(btn => {
    btn.addEventListener('click', function() {
        commandInput.value = this.dataset.cmd;
        commandInput.focus();
    });
});

// Auto focus on input
commandInput.focus();
</script>
@endpush

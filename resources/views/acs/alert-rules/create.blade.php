@extends('layouts.admin')

@section('title', 'Create Alert Rule')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('acs.alert-rules.index') }}">Alert Rules</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Create Alert Rule</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('acs.alert-rules.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Condition Type <span class="text-danger">*</span></label>
                        <select name="condition_type" class="form-select @error('condition_type') is-invalid @enderror"
                                id="conditionType" onchange="updateConditionFields()" required>
                            <option value="">Select Condition</option>
                            <option value="offline" {{ old('condition_type') == 'offline' ? 'selected' : '' }}>Device Offline</option>
                            <option value="signal_low" {{ old('condition_type') == 'signal_low' ? 'selected' : '' }}>Signal Low</option>
                            <option value="los" {{ old('condition_type') == 'los' ? 'selected' : '' }}>Loss of Signal (LOS)</option>
                            <option value="no_inform" {{ old('condition_type') == 'no_inform' ? 'selected' : '' }}>No ACS Inform</option>
                        </select>
                        @error('condition_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="conditionParametersDiv">
                        <label class="form-label">Condition Parameters <span class="text-danger">*</span></label>
                        <div id="parametersContainer">
                            <!-- Dynamic fields will be inserted here -->
                        </div>
                        <input type="hidden" name="condition_parameters" id="conditionParametersJson">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notification Channels <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="notification_channels[]"
                                       value="email" id="channelEmail">
                                <label class="form-check-label" for="channelEmail">Email</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="notification_channels[]"
                                       value="slack" id="channelSlack">
                                <label class="form-check-label" for="channelSlack">Slack</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="notification_channels[]"
                                       value="telegram" id="channelTelegram">
                                <label class="form-check-label" for="channelTelegram">Telegram</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="notification_channels[]"
                                       value="webhook" id="channelWebhook">
                                <label class="form-check-label" for="channelWebhook">Webhook</label>
                            </div>
                        </div>
                        @error('notification_channels')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Recipients (comma-separated) <span class="text-danger">*</span></label>
                        <textarea name="recipients" class="form-control @error('recipients') is-invalid @enderror"
                                  rows="3" placeholder="admin@example.com, user@example.com" required>{{ old('recipients') }}</textarea>
                        <small class="text-muted">For email: email addresses. For Slack: channel IDs. For Telegram: chat IDs.</small>
                        @error('recipients')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Check Interval (seconds)</label>
                            <input type="number" name="check_interval" class="form-control"
                                   value="{{ old('check_interval', 300) }}" min="60">
                            <small class="text-muted">How often to check this condition (default: 300s)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cooldown Period (seconds)</label>
                            <input type="number" name="cooldown_period" class="form-control"
                                   value="{{ old('cooldown_period', 3600) }}" min="300">
                            <small class="text-muted">Minimum time between alerts (default: 3600s)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_active"
                                   id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (start monitoring immediately)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Rule
                        </button>
                        <a href="{{ route('acs.alert-rules.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Help</h6>
            </div>
            <div class="card-body">
                <h6>Condition Types:</h6>
                <ul class="small">
                    <li><strong>Device Offline:</strong> Trigger when device goes offline for specified duration</li>
                    <li><strong>Signal Low:</strong> Trigger when RX power drops below threshold</li>
                    <li><strong>Loss of Signal:</strong> Trigger when device reports LOS status</li>
                    <li><strong>No ACS Inform:</strong> Trigger when no inform received within timeframe</li>
                </ul>

                <h6 class="mt-3">Example Recipients:</h6>
                <ul class="small">
                    <li><strong>Email:</strong> admin@example.com, support@example.com</li>
                    <li><strong>Slack:</strong> #alerts, #network-ops</li>
                    <li><strong>Telegram:</strong> 123456789, 987654321</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const conditionTemplates = {
    offline: {
        duration: { type: 'number', label: 'Duration (minutes)', default: 5, min: 1 }
    },
    signal_low: {
        threshold: { type: 'number', label: 'RX Power Threshold (dBm)', default: -25, step: 0.1 },
        duration: { type: 'number', label: 'Duration (minutes)', default: 5, min: 1 }
    },
    los: {
        duration: { type: 'number', label: 'Duration (minutes)', default: 1, min: 1 }
    },
    no_inform: {
        duration: { type: 'number', label: 'Duration (minutes)', default: 30, min: 5 }
    }
};

function updateConditionFields() {
    const type = document.getElementById('conditionType').value;
    const container = document.getElementById('parametersContainer');

    if (!type || !conditionTemplates[type]) {
        container.innerHTML = '<p class="text-muted">Select a condition type to configure parameters</p>';
        return;
    }

    const template = conditionTemplates[type];
    let html = '<div class="row">';

    for (const [key, config] of Object.entries(template)) {
        html += `
            <div class="col-md-6 mb-2">
                <label class="form-label small">${config.label}</label>
                <input type="${config.type}"
                       class="form-control form-control-sm"
                       id="param_${key}"
                       value="${config.default}"
                       ${config.min ? `min="${config.min}"` : ''}
                       ${config.step ? `step="${config.step}"` : ''}>
            </div>
        `;
    }

    html += '</div>';
    container.innerHTML = html;
}

// Update hidden field before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const type = document.getElementById('conditionType').value;

    if (type && conditionTemplates[type]) {
        const params = {};
        const template = conditionTemplates[type];

        for (const key of Object.keys(template)) {
            const input = document.getElementById(`param_${key}`);
            if (input) {
                params[key] = input.type === 'number' ? parseFloat(input.value) : input.value;
            }
        }

        document.getElementById('conditionParametersJson').value = JSON.stringify(params);
    }

    // Convert recipients textarea to array
    const recipientsTextarea = document.querySelector('[name="recipients"]');
    const recipientsArray = recipientsTextarea.value.split(',').map(r => r.trim()).filter(r => r);

    // Create hidden input for array
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'recipients';
    hiddenInput.value = JSON.stringify(recipientsArray);

    recipientsTextarea.name = 'recipients_text';
    this.appendChild(hiddenInput);
});

// Initialize on load
updateConditionFields();
</script>
@endpush

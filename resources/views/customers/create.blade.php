@extends('layouts.admin')

@section('title', 'Tambah Customer')
@section('page-title', 'Tambah Customer Baru')

@section('content')
<form action="{{ route('customers.store') }}" method="POST">
    @csrf

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Informasi Personal</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">No. KTP</label>
                        <input type="text" name="id_card_number" class="form-control @error('id_card_number') is-invalid @enderror"
                               value="{{ old('id_card_number') }}">
                        @error('id_card_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                              rows="3" required>{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                               value="{{ old('latitude') }}" placeholder="-8.670458">
                        <small class="text-muted">GPS Coordinates (opsional)</small>
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                               value="{{ old('longitude') }}" placeholder="115.212629">
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Connection Configuration -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Konfigurasi Koneksi</h5>

                <div class="mb-3">
                    <label class="form-label">Tipe Koneksi <span class="text-danger">*</span></label>
                    <select name="connection_type" id="connection_type" class="form-select @error('connection_type') is-invalid @enderror" required>
                        <option value="">Pilih Tipe Koneksi</option>
                        <option value="pppoe_direct" {{ old('connection_type') == 'pppoe_direct' ? 'selected' : '' }}>PPPoE Direct</option>
                        <option value="pppoe_mikrotik" {{ old('connection_type') == 'pppoe_mikrotik' ? 'selected' : '' }}>PPPoE via Customer MikroTik</option>
                        <option value="static_ip" {{ old('connection_type') == 'static_ip' ? 'selected' : '' }}>Static IP</option>
                        <option value="hotspot" {{ old('connection_type') == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                        <option value="dhcp" {{ old('connection_type') == 'dhcp' ? 'selected' : '' }}>DHCP</option>
                    </select>
                    @error('connection_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- PPPoE Direct & PPPoE MikroTik -->
                <div id="pppoe_config" class="connection-config" style="display: none;">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Konfigurasi PPPoE
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PPPoE Username <span class="text-danger">*</span></label>
                            <input type="text" name="pppoe_username" class="form-control" value="{{ old('pppoe_username') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PPPoE Password <span class="text-danger">*</span></label>
                            <input type="text" name="pppoe_password" class="form-control" value="{{ old('pppoe_password') }}">
                        </div>
                    </div>
                </div>

                <!-- Static IP -->
                <div id="static_ip_config" class="connection-config" style="display: none;">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Konfigurasi Static IP
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">IP Address <span class="text-danger">*</span></label>
                            <input type="text" name="static_ip" class="form-control" value="{{ old('static_ip') }}" placeholder="192.168.1.100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Subnet Mask <span class="text-danger">*</span></label>
                            <input type="text" name="static_subnet" class="form-control" value="{{ old('static_subnet') }}" placeholder="255.255.255.0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gateway <span class="text-danger">*</span></label>
                            <input type="text" name="static_gateway" class="form-control" value="{{ old('static_gateway') }}" placeholder="192.168.1.1">
                        </div>
                    </div>
                </div>

                <!-- Customer MikroTik -->
                <div id="customer_mikrotik_config" class="connection-config" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="bi bi-router"></i> Customer menggunakan MikroTik sendiri
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IP MikroTik Customer</label>
                            <input type="text" name="customer_mikrotik_ip" class="form-control" value="{{ old('customer_mikrotik_ip') }}" placeholder="192.168.88.1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username MikroTik</label>
                            <input type="text" name="customer_mikrotik_username" class="form-control" value="{{ old('customer_mikrotik_username') }}" placeholder="admin">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Password MikroTik</label>
                            <input type="password" name="customer_mikrotik_password" class="form-control" value="{{ old('customer_mikrotik_password') }}">
                            <small class="text-muted">Untuk remote management (opsional)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Paket <span class="text-danger">*</span></label>
                        <select name="package_id" class="form-select @error('package_id') is-invalid @enderror" required>
                            <option value="">Pilih Paket</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} - {{ $package->getSpeedLabel() }} - {{ $package->getFormattedPrice() }}
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Router <span class="text-danger">*</span></label>
                        <select name="router_id" class="form-select @error('router_id') is-invalid @enderror">
                            <option value="">Pilih Router</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}" {{ old('router_id') == $router->id ? 'selected' : '' }}>
                                    {{ $router->name }} ({{ $router->ip_address }})
                                </option>
                            @endforeach
                        </select>
                        @error('router_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Fiber/OLT Configuration (Optional) -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Konfigurasi Fiber (Opsional)</h5>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">OLT</label>
                        <select name="olt_id" class="form-select">
                            <option value="">Tidak Pakai OLT</option>
                            @foreach($olts as $olt)
                                <option value="{{ $olt->id }}" {{ old('olt_id') == $olt->id ? 'selected' : '' }}>
                                    {{ $olt->name }} ({{ $olt->getVendorLabel() }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">ONT Serial Number</label>
                        <input type="text" name="ont_serial_number" class="form-control" value="{{ old('ont_serial_number') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">PON Port</label>
                        <input type="text" name="pon_port" class="form-control" value="{{ old('pon_port') }}" placeholder="0/1/1">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Installation Info -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Informasi Instalasi</h5>

                <div class="mb-3">
                    <label class="form-label">Tanggal Instalasi <span class="text-danger">*</span></label>
                    <input type="date" name="installation_date" class="form-control @error('installation_date') is-invalid @enderror"
                           value="{{ old('installation_date', date('Y-m-d')) }}" required>
                    @error('installation_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Assign Teknisi</label>
                    <select name="assigned_teknisi_id" class="form-select">
                        <option value="">Tidak Ada</option>
                        @foreach($teknisis as $teknisi)
                            <option value="{{ $teknisi->id }}" {{ old('assigned_teknisi_id') == $teknisi->id ? 'selected' : '' }}>
                                {{ $teknisi->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Simpan Customer
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const connectionTypeSelect = document.getElementById('connection_type');
    const pppoeConfig = document.getElementById('pppoe_config');
    const staticIpConfig = document.getElementById('static_ip_config');
    const customerMikrotikConfig = document.getElementById('customer_mikrotik_config');

    function toggleConnectionConfig() {
        const selectedType = connectionTypeSelect.value;

        // Hide all configs
        pppoeConfig.style.display = 'none';
        staticIpConfig.style.display = 'none';
        customerMikrotikConfig.style.display = 'none';

        // Show relevant config
        if (selectedType === 'pppoe_direct') {
            pppoeConfig.style.display = 'block';
        } else if (selectedType === 'pppoe_mikrotik') {
            pppoeConfig.style.display = 'block';
            customerMikrotikConfig.style.display = 'block';
        } else if (selectedType === 'static_ip') {
            staticIpConfig.style.display = 'block';
        }
    }

    connectionTypeSelect.addEventListener('change', toggleConnectionConfig);

    // Trigger on load if old value exists
    if (connectionTypeSelect.value) {
        toggleConnectionConfig();
    }
});
</script>
@endpush

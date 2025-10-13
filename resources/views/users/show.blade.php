@extends('layouts.admin')

@section('title', 'Detail User')
@section('page-title', 'Detail User')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="custom-table">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Informasi User</h5>
                <div class="d-flex gap-2">
                    @can('edit_user')
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @endcan
                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 text-center mb-4">
                    @if($user->photo)
                        <img src="{{ asset('storage/' . $user->photo) }}" alt="Photo" class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px; font-size: 3rem;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif

                    @if($user->status === 'active')
                        <span class="badge badge-active">Active</span>
                    @else
                        <span class="badge badge-inactive">Inactive</span>
                    @endif
                </div>

                <div class="col-md-9">
                    <table class="table table-borderless">
                        <tr>
                            <td width="200" class="fw-semibold">Nama Lengkap</td>
                            <td>: {{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Email</td>
                            <td>: {{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">No. Telepon</td>
                            <td>: {{ $user->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Role</td>
                            <td>: <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $user->getRoleNames()->first() ?? 'No Role')) }}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Status</td>
                            <td>:
                                @if($user->status === 'active')
                                    <span class="badge badge-active">Active</span>
                                @else
                                    <span class="badge badge-inactive">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Terdaftar Sejak</td>
                            <td>: {{ $user->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Terakhir Update</td>
                            <td>: {{ $user->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Terakhir Login</td>
                            <td>: {{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Belum pernah login' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Permissions</h6>
            <div class="row">
                @forelse($user->getAllPermissions()->chunk(3) as $chunk)
                    <div class="col-md-4 mb-2">
                        @foreach($chunk as $permission)
                            <div class="mb-1">
                                <i class="bi bi-check-circle text-success"></i>
                                <small>{{ ucfirst(str_replace('_', ' ', $permission->name)) }}</small>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted">Tidak ada permission</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

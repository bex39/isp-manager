<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | ISP-MANAGER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #1a237e, #3949ab);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
        }
        .brand-icon {
            font-size: 3rem;
            color: #1a237e;
        }
        .brand-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a237e;
        }
        .btn-primary {
            background-color: #1a237e;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #3949ab;
        }
        .form-label {
            font-weight: 600;
        }
        @media (max-width: 576px) {
            .card {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-router brand-icon"></i>
                    <div class="brand-title mt-2">ISP-MANAGER</div>
                    <p class="text-muted mt-1">Lupa password akun Anda?</p>
                </div>

                <!-- Info Text -->
                <div class="small text-muted mb-3 text-center">
                    Masukkan email Anda dan kami akan kirimkan tautan untuk mengatur ulang password.
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success small py-2 text-center mb-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input id="email" type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus
                               placeholder="Masukkan email terdaftar Anda">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Kirim Tautan Reset Password
                        </button>
                    </div>

                    <!-- Back to login -->
                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none text-secondary small">
                            <i class="bi bi-arrow-left"></i> Kembali ke login
                        </a>
                    </div>

                    <div class="text-center mt-3 text-muted small">
                        &copy; {{ date('Y') }} ISP-MANAGER. Semua hak dilindungi.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ISP-MANAGER</title>
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
                    <p class="text-muted mt-1">Masuk ke akun Anda</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success small py-2 text-center mb-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus
                               placeholder="Masukkan email Anda">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="current-password"
                               placeholder="Masukkan password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label small text-secondary" for="remember">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Masuk
                        </button>
                    </div>

                    <!-- Forgot password -->
                    <div class="text-center mt-3">
                        @if (Route::has('password.request'))
                            <a class="text-decoration-none text-secondary small" href="{{ route('password.request') }}">
                                Lupa password?
                            </a>
                        @endif
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

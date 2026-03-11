@extends('layouts.index')

@section('title', 'Reset Password Wajib')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Reset Password Wajib</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Password Anda telah expired atau terdeteksi lemah. 
                        Demi keamanan akun, silakan buat password baru dengan persyaratan berikut:
                    </div>

                    <div class="alert alert-info">
                        <strong>Persyaratan Password:</strong>
                        <ul class="mb-0">
                            <li>Minimal 12 karakter</li>
                            <li>Mengandung huruf kapital (A-Z)</li>
                            <li>Mengandung huruf kecil (a-z)</li>
                            <li>Mengandung angka (0-9)</li>
                            <li>Mengandung karakter spesial (!@#$%^&*...)</li>
                            <li>Tidak boleh sama dengan password sebelumnya</li>
                            <li>Tidak boleh ada di database password yang pernah bocor (HIBP)</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('password.reset.force') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input 
                                id="password" 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                name="password" 
                                required 
                                autocomplete="new-password"
                                placeholder="Masukkan password baru"
                            >
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">Konfirmasi Password Baru</label>
                            <input 
                                id="password-confirm" 
                                type="password" 
                                class="form-control" 
                                name="password_confirmation" 
                                required 
                                autocomplete="new-password"
                                placeholder="Konfirmasi password baru"
                            >
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

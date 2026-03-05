@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_header', 'Verifikasi Two-Factor Authentication')

@section('auth_body')
    <div class="text-center mb-3">
        <div class="mb-3">
            <i class="fas fa-shield-alt fa-3x text-primary"></i>
        </div>
        <h4 class="mb-1">🔐 Verifikasi 2FA</h4>
        <p class="text-muted">Masukkan kode verifikasi untuk melanjutkan</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <p class="text-center text-muted mb-4">
        Kode verifikasi telah dikirim ke channel terdaftar
    </p>
    
    <form id="2faChallengeForm" action="{{ route('2fa.challenge.verify') }}" method="POST">
        @csrf
        <div class="input-group mb-3">
            <input type="text" class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                name="code" id="code" placeholder="000000" maxlength="6" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-key {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>
            @error('code')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        
        <div class="text-center mb-3">
            <small class="text-muted">
                Kode berlaku selama <span id="countdown" class="font-weight-bold text-primary">5:00</span> menit
            </small>
        </div>
        
        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-check mr-2"></i>Verifikasi & Lanjutkan
                </button>
            </div>
        </div>
    </form>

    <div class="text-center mt-3">
        <button type="button" class="btn btn-link" id="resendBtn">
            <i class="fas fa-redo mr-1"></i>
            Kirim Ulang (<span id="resendCountdown">30</span>s)
        </button>
    </div>

    <div class="text-center mt-3">
        <a href="{{ $login_url }}" class="btn btn-link">
            <i class="fas fa-arrow-left mr-1"></i>
            Kembali ke Login
        </a>
    </div>
@stop

@section('auth_footer')
    <div class="text-center">
        <div class="row">
            <div class="col">
                <small class="text-muted">
                    <i class="fas fa-lock mr-1"></i>
                    Koneksi aman • 2FA
                </small>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col">
                <div class="d-flex justify-content-center align-items-center">
                    <span class="badge badge-info mr-2">
                        <i class="fas fa-envelope mr-1"></i>
                        Email
                    </span>
                    <span class="badge badge-info">
                        <i class="fab fa-telegram mr-1"></i>
                        Telegram
                    </span>
                </div>
            </div>
        </div>
    </div>
@stop

@section('adminlte_css')
   <style nonce="{{ csp_nonce() }}" >
        .auth-body {
            padding: 20px;
        }
        
        #code {
            font-family: 'Courier New', monospace;
            letter-spacing: 8px;
            font-size: 28px;
            font-weight: bold;
        }
        
        .badge {
            font-size: 0.8em;
        }
        
        .countdown-expired {
            color: #dc3545 !important;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
        }
        
        .btn-block {
            font-weight: 600;
        }
        
        .text-primary {
            color: #007bff !important;
        }
        
        .text-success {
            color: #28a745 !important;
        }
    </style>
@stop

@section('adminlte_js')
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>
<script nonce="{{ csp_nonce() }}">
document.addEventListener("DOMContentLoaded", function (event) {
    let countdownTimer;
    let resendTimer;
    
    // Auto-focus and format OTP input
    $('#code').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length === 6) {
            $('#2faChallengeForm').submit();
        }
    });
    
    // Start countdown
    startCountdown(300); // 5 minutes in seconds
    startResendCountdown(30); // 30 seconds cooldown
    $('#code').focus();
    
    // Verify form submission
    $('#2faChallengeForm').submit(function(e) {
        e.preventDefault();
        const code = $('#code').val();
        if (code.length !== 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Kode verifikasi harus 6 digit'
            });
            return false;
        }
        
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memverifikasi...');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message
                }).then(() => {
                    window.location.href = response.redirect;
                });
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message || 'Verifikasi gagal'
                });
                $('#code').val('').focus();
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Resend OTP
    $('#resendBtn').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...');
        
        $.ajax({
            url: '{{ route("2fa.resend") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message
                });
                startCountdown(300); // Reset countdown
                startResendCountdown(30); // 30 seconds cooldown
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message || 'Gagal mengirim ulang'
                });
            },
            complete: function() {
                btn.html(originalText);
            }
        });
    });
    
    function startCountdown(seconds) {
        clearInterval(countdownTimer);
        let timeLeft = seconds;
        
        countdownTimer = setInterval(function() {
            const minutes = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            $('#countdown').text(minutes + ':' + (secs < 10 ? '0' : '') + secs);
            
            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                $('#countdown').text('Kedaluwarsa').addClass('countdown-expired');
            }
            timeLeft--;
        }, 1000);
    }
    
    function startResendCountdown(seconds) {
        clearInterval(resendTimer);
        let timeLeft = seconds;
        $('#resendBtn').prop('disabled', true);
        
        resendTimer = setInterval(function() {
            $('#resendCountdown').text(timeLeft);
            
            if (timeLeft <= 0) {
                clearInterval(resendTimer);
                $('#resendBtn').prop('disabled', false);
                $('#resendBtn').html('<i class="fas fa-redo mr-1"></i>Kirim Ulang');
            }
            timeLeft--;
        }, 1000);
    }
});
</script>
@stop
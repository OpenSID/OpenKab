@extends('layouts.index')

@section('title', 'Pengaturan OTP & 2FA')

@section('content_header')
    <h1>Pengaturan OTP & 2FA Otentikasi</h1>
@stop

@section('content')

    <div class="row">
        <div class="col-8" data-testid="panel-otp-2fa">
            @include('admin.pengaturan.otp.partials.status-cards')                     
        </div>
        
        <div class="col-4" data-testid="panel-info-sidebar">
            @include('admin.pengaturan.otp.partials.info-sidebar')
        </div>
    </div>

@stop

@section('css')
    @include('admin.pengaturan.otp.partials._styles')    
@stop

@section('js')
    <script nonce="{{  csp_nonce() }}">        
        document.addEventListener("DOMContentLoaded", function (event) {        

            $('#enableOtpBtn').click(function(){
                window.location.href = '{{ route('otp.activate') }}'
            })
            $('#enable2faBtn').click(function(){
                window.location.href = '{{ route('2fa.activate') }}'
            })
            // Disable OTP
            $('#disableOtpBtn').click(function() {
                Swal.fire({
                    title: 'Nonaktifkan OTP?',
                    html: `
                        <div class="text-left">
                            <p><strong>Anda akan kehilangan:</strong></p>
                            <ul class="text-sm">
                                <li>Lapisan keamanan tambahan</li>
                                <li>Akses cepat tanpa password</li>
                                <li>Perlindungan dari serangan phishing</li>
                            </ul>
                            <p class="text-warning mt-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Apakah Anda yakin ingin melanjutkan?</strong>
                            </p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-times mr-2"></i>Ya, Nonaktifkan',
                    cancelButtonText: '<i class="fas fa-arrow-left mr-2"></i>Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = $(this);
                        const originalText = btn.html();
                        
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menonaktifkan...');

                        $.ajax({
                            url: '{{ route("otp.disable") }}',
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire({
                                    title: 'OTP Dinonaktifkan!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON;
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message || 'Gagal menonaktifkan OTP',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                btn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });

            // Disable 2FA
            $('#disable2faBtn').click(function() {
                Swal.fire({
                    title: 'Nonaktifkan 2FA?',
                    html: `
                        <div class="text-left">
                            <p><strong>Anda akan kehilangan:</strong></p>
                            <ul class="text-sm">
                                <li>Lapisan keamanan tambahan (2FA)</li>
                                <li>Verifikasi identifikasi ganda</li>
                                <li>Perlindungan dari akses tidak sah</li>
                            </ul>
                            <p class="text-warning mt-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Apakah Anda yakin ingin melanjutkan?</strong>
                            </p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-times mr-2"></i>Ya, Nonaktifkan',
                    cancelButtonText: '<i class="fas fa-arrow-left mr-2"></i>Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = $(this);
                        const originalText = btn.html();
                        
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menonaktifkan...');

                        $.ajax({
                            url: '{{ route("2fa.disable") }}',
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire({
                                    title: '2FA Dinonaktifkan!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON;
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message || 'Gagal menonaktifkan 2FA',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                btn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });

           // Enhanced UI interactions for active state
            $('.info-box').hover(
                function() {
                    $(this).addClass('shadow');
                }, 
                function() {
                    $(this).removeClass('shadow');
                }
            );     
        });
    </script>
@endsection
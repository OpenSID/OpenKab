<!-- Status Cards -->
<div class="row mb-4">
    <!-- OTP Status Card -->
    <div class="col-md-6 mb-3" data-testid="panel-otp">
        <div class="card {{ $user->hasOtpEnabled() ? 'border-success' : 'border-secondary' }}">
            <div class="card-header {{ $user->hasOtpEnabled() ? 'bg-success' : 'bg-secondary' }} text-white">
                <h3 class="card-title">
                    <i class="fas {{ $user->hasOtpEnabled() ? 'fa-shield-check' : 'fa-shield-alt' }} mr-2"></i>
                    OTP: <span class="badge badge-light text-{{ $user->hasOtpEnabled() ? 'success' : 'secondary' }} ml-2">{{ $user->hasOtpEnabled() ? 'Aktif' : 'Tidak Aktif' }}</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary"><i class="fas fa-info-circle mr-2"></i>Status Saat Ini</h6>
                    <p class="mb-0">
                        @if($user->hasOtpEnabled())
                        <span class="text-success"><i class="fas fa-check-circle mr-1"></i> OTP sudah aktif sejak</span>
                        @else
                        <span class="text-muted">OTP belum diaktifkan</span>
                        @endif
                    </p>
                </div>

                @if($user->hasOtpEnabled())
                <div class="mb-3">
                    <h6 class="text-primary"><i class="fas fa-broadcast-tower mr-2"></i>Channel</h6>
                    <div>
                        @foreach($user->getOtpChannels() as $channel)
                        <span class="badge badge-primary badge-lg mr-2 mb-1">
                            @if($channel === 'email')
                            <i class="fas fa-envelope mr-1"></i> Email
                            @else
                            <i class="fab fa-telegram mr-1"></i> Telegram
                            @endif
                        </span>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <h6 class="text-primary"><i class="fas fa-id-card mr-2"></i>Identifier</h6>
                        <code class="bg-light p-2 rounded">{{ $user->otp_identifier }}</code>
                    </div>
                </div>
                @endif

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if($user->hasOtpEnabled())
                        <button type="button" class="btn btn-outline-warning" id="disableOtpBtn" data-testid="bt-disable-otp">
                            <i class="fas fa-times-circle mr-2"></i>Non Aktifkan
                        </button>
                        @else
                        <button type="button" class="btn btn-outline-primary" id="enableOtpBtn" data-testid="bt-enable-otp">
                            <i class="fas fa-plus-circle mr-2"></i>Aktifkan OTP
                        </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- 2FA Status Card -->
    <div class="col-md-6 mb-3" data-testid="panel-2fa">
        <div class="card {{ $twoFactorStatus['enabled'] ? 'border-success' : 'border-secondary' }}">
            <div class="card-header {{ $twoFactorStatus['enabled'] ? 'bg-success' : 'bg-secondary' }} text-white">
                <h3 class="card-title">
                    <i class="fas {{ $twoFactorStatus['enabled'] ? 'fa-shield-check' : 'fa-shield-alt' }} mr-2"></i>
                    2FA: <span class="badge badge-light text-{{ $twoFactorStatus['enabled'] ? 'success' : 'secondary' }} ml-2">{{ $twoFactorStatus['enabled'] ? 'Aktif' : 'Tidak Aktif' }}</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary"><i class="fas fa-info-circle mr-2"></i>Status Saat Ini</h6>
                    <p class="mb-0">
                        @if($twoFactorStatus['enabled'])
                        <span class="text-success"><i class="fas fa-check-circle mr-1"></i> 2FA aktif</span>
                        @else
                        <span class="text-muted">2FA belum diaktifkan</span>
                        @endif
                    </p>
                </div>

                @if($twoFactorStatus['enabled'])
                <div class="mb-3">
                    <h6 class="text-primary"><i class="fas fa-broadcast-tower mr-2"></i>Channel</h6>
                    <div>
                        @foreach($twoFactorStatus['channel'] as $channel)
                        <span class="badge badge-primary badge-lg mr-2 mb-1">
                            @if($channel === 'email')
                            <i class="fas fa-envelope mr-1"></i> Email
                            @else
                            <i class="fab fa-telegram mr-1"></i> Telegram
                            @endif
                        </span>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <h6 class="text-primary"><i class="fas fa-id-card mr-2"></i>Identifier</h6>
                        <code class="bg-light p-2 rounded">{{ $twoFactorStatus['identifier'] }}</code>
                    </div>
                </div>
                @endif

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if($twoFactorStatus['enabled'])
                        <button type="button" class="btn btn-outline-warning" id="disable2faBtn" data-testid="bt-disable-2fa">
                            <i class="fas fa-times-circle mr-2"></i>Non Aktifkan
                        </button>
                        @else
                        <button type="button" class="btn btn-outline-primary" id="enable2faBtn" data-testid="bt-enable-2fa">
                            <i class="fas fa-plus-circle mr-2"></i>Aktifkan 2FA
                        </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
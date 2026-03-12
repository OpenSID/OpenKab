<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\RefreshToken;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use LogsActivity;

    protected $guard = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'active',
        'name',
        'company',
        'phone',
        'foto',
        'kode_kabupaten',
        'otp_enabled',
        'otp_channel',
        'otp_identifier',
        'telegram_chat_id',
        '2fa_enabled',
        '2fa_channel',
        '2fa_identifier',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'last_login' => 'datetime',
        'email_verified_at' => 'datetime',
        'tempat_dilahirkan' => Enums\StatusEnum::class,
        '2fa_enabled' => 'boolean',
        'otp_enabled' => 'boolean',
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'user_team', 'id_user', 'id_team');
    }

    public function scopeVisibleTo($query, $user)
    {
        if (! $user->hasRole('administrator')) {
            $query->where('kode_kabupaten', $user->kode_kabupaten)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'administrator');
                });
        }

        return $query;
    }

    /**
     * Get the user's password.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value),
        );
    }

    public function adminlte_image()
    {
        return $this->foto ? Storage::url($this->foto) : asset('assets/img/avatar.png');
    }

    /**
     * super admin ditandakan dengan id pertama yang dibuat karena belum ada grup/role.
     */
    public static function superAdmin()
    {
        return self::first()->id;
    }

    // selft::delete agar tidak bisa menghapus superadmin
    // return Exception gagal hapus
    public function delete()
    {
        if ($this->id == self::superAdmin()) {
            throw new \Exception('Tidak bisa menghapus superadmin');
        }

        return parent::delete();
    }

    public function team()
    {
        return $this->belongsToMany(
            Team::class,
            'user_team',
            'id_user',
            'id_team',
        );
        // return $this->hasOne(UserTeam::class, 'id_user', 'id');
    }

    public function getTeamId()
    {
        if ($this->relationLoaded('team') && $this->team !== null) {
            return $this->team->first()?->id;
        }

        return $this->team()->first()?->id;
    }

    public function adminlte_profile_url()
    {
        return route('profile.edit', $this->id);
    }

    public function adminlte_desc()
    {
        if ($this->relationLoaded('team') && $this->team !== null) {
            return $this->team->first()?->name ?? '-';
        }

        return $this->team()->first()?->name ?? '-';
    }

    public function isSuperAdmin()
    {
        return $this->id == self::superAdmin();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->useLogName('user-log');
        // Chain fluent methods for configuration options
    }

    public function getEffectiveKodeKabupaten($input = null)
    {
        return $this->hasRole('administrator') && $input
            ? $input
            : $this->kode_kabupaten;
    }

    public function scopeVisibleForAuthenticatedUser($query)
    {
        $authUser = auth()->user();

        if ($authUser->hasRole('administrator')) {
            return $query;
        }

        // Jika superadmin_daerah & kode_kabupaten NULL
        if (
            $authUser->hasRole('superadmin_daerah') &&
            is_null($authUser->kode_kabupaten)
        ) {
            // Hanya tampilkan user itu sendiri
            return $query->where('id', $authUser->id);
        }

        // Jika superadmin_daerah biasa
        if (
            $authUser->hasAnyRole(['superadmin_daerah', 'kabupaten']) &&
            $authUser->kode_kabupaten
        ) {
            return $query
                ->where('kode_kabupaten', $authUser->kode_kabupaten)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'administrator');
                });
        }

        // Fallback default
        return $query->whereRaw('1 = 0');
    }

    /**
     * Relasi ke OTP Tokens
     */
    public function otpTokens()
    {
        return $this->hasMany(OtpToken::class);
    }

    /**
     * Relasi ke Refresh Tokens
     */
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    /**
     * Cek apakah user memiliki OTP aktif
     */
    public function hasOtpEnabled()
    {
        return $this->otp_enabled;
    }

    /**
     * Get channel OTP yang aktif
     */
    public function getOtpChannels()
    {
        return $this->otp_channel ? json_decode($this->otp_channel, true) : [];
    }
}

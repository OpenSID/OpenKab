<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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
        'tempat_dilahirkan' => \App\Models\Enums\StatusEnum::class,
    ];

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
        return $this->team()->first()?->id;
    }

    public function adminlte_profile_url(){
        return route('profile.edit', $this->id);
    }

    public function adminlte_desc(){
        return $this->team()->first()->name;
    }

    public function isSuperAdmin(){
        return $this->id == self::superAdmin();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->useLogName('user-log');;
        // Chain fluent methods for configuration options
    }
}

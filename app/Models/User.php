<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;

    protected $guard  = 'web';

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

     /** {@inheritdoc} */
     protected $appends = [
        'team',
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
        $email = md5($this->email);

        return "https://www.gravatar.com/avatar/{$email}?s=32&d=https://www.gravatar.com/avatar/00000000000000000000000000000000?s=32";
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
        return $this->hasOne(Team::class, 'id', 'id');
    }

    public function getTeamIdFromToken()
    {
        return $this->team->id;

    }
}

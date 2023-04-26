<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

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
        $email = md5($this->email);

        return "https://www.gravatar.com/avatar/{$email}?s=32&d=https://www.gravatar.com/avatar/00000000000000000000000000000000?s=32";
    }
}

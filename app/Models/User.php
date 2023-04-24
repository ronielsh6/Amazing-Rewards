<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\GiftCard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const USER_STATUS = ['active', 'blocked'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'admin',
        'fcm_token',
        'country',
        'ip_address',
        'status',
        'device_id'
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getGiftCards()
    {
        return $this->hasMany(GiftCard::class,'owner','id');
    }

    public function isAdmin()
    {
        return $this->attributes['admin'] === 1;
    }

    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        User::factory()
            ->count(50)
            ->create();
    }
}

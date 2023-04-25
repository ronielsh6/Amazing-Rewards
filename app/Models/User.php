<?php

namespace App\Models;

use App\GiftCard;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

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
        'device_id',
        'points',
        'referral_code',
        'email_verified_at',
        'email_verification_code',
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
        return $this->hasMany(GiftCard::class, 'owner', 'id');
    }

    public function getPoints()
    {
        return \Auth::user()->points;
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
        self::factory()
            ->count(50)
            ->create();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}

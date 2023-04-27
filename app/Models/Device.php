<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'device';

    protected $fillable = [
        'device_id',
        'status',
    ];

    public function getUsers()
    {
        return $this->belongsToMany(User::class, 'user_device', 'device_id', 'user_id');
    }
}

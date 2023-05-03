<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedLog extends Model
{
    use HasFactory;

    protected $table = 'blocked_logs';

    protected $fillable = [
        'user_id',
        'ip_address',
        'country',
        'reason',
    ];

    public function getUser()
    {
        return $this->belongsTo(User::class);
    }
}

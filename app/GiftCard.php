<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class GiftCard extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'status', 'claim_link', 'egifter_id', 'pending', 'owner',
    ];


    public function getOwner()
    {
        return $this->hasOne(User::class,'id','owner');
    }

}

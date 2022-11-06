<?php

namespace App;
use Illuminate\Database\Eloquent\Model;


class GiftCard extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gift_card';

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

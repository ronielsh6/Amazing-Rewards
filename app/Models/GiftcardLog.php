<?php

namespace App\Models;

use App\GiftCard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftcardLog extends Model
{
    use HasFactory;

    protected $table = 'giftcards_logs';

    protected $fillable = ['reason'];

    public function getGiftcard()
    {
        return $this->belongsTo(GiftCard::class, 'gift_card_id');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Promotion;
use App\Recargas;
use App\GiftCard;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{

    public function getGiftCards(Request $request){
        $giftCards = $request->user()->getGiftCards()->get();
        return response()->json([
            'data' => $giftCards]);
    }

    public function createGiftCard(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'status' => 'required',
            'claim_link' => 'required',
            'egifter_id' => 'required',
        ]);
        $request['pending'] = true;
        $request['owner'] = $request->user()->id;
        GiftCard::create($request->toArray());
        $giftCards = $request->user()->getGiftCards()->get();
        return response()->json($giftCards, 200);
    }

}

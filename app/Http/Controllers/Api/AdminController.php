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
        $user = User::find($request->user()->id);
        $user->points -= $request->amount * 1000;
        $user->save();
        $giftCards = $request->user()->getGiftCards()->get();
        return response()->json([
            'data' => $giftCards]);
    }


    public function addPoints(Request $request)
    {
        $request->validate([
            'points' => 'required',
        ]);
        $user = User::find($request->user()->id);
        $user->points += $request->points;
        $user->save();
        return response()->json($user);
    }


    public function inBrainsCallback(Request $request)
    {
        $localSig = md5(("".$request->PanelistId .$request->RewardId."MDU3YmQzMjUtODhmMi00M2I5LWI2OTEtNGJmNDUyMzkzMmE0"));
        if($localSig == $request->Sig){
            $user = User::where('id', $request->PanelistId)->first();
            $user->points += $request->Reward;
            $user->save();
            return response()->json(null,200);
        }else{
            return response()->json(null,403);
        }


    }

}

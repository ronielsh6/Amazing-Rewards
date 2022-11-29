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
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{

    public function getGiftCards(Request $request)
    {
        $giftCards = $request->user()->getGiftCards()->get();
        return response()->json([
            'data' => $giftCards]);
    }

    private function getAuthToken()
    {
        $response = Http::withHeaders([
            'AccessToken' => '03b9nsl27htc939st11nt0sh1r080rtfr930th6d9d02n16381cl1tt29rk89t2s',
            'Email' => 'info@myamazingrewards.com'
        ])->post('https://stage-rewards-api.egifter.com/v1/Tokens');

        return $response->json("value");
    }

    public function generateEgifterCard(Request $request)
    {
        $request->validate([
            'value' => 'required',
            'email' => 'required',
            'name' => 'required',
            'poNumber' => 'required',
            'note' => 'required',
        ]);


        $token = $this->getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token
        ])->post('https://stage-rewards-api.egifter.com/v1/Orders',
            ['lineItems' => [[
                'productId' => 'AMAZON',
                'quantity' => 1,
                'value' => $request->value,
                'digitalDeliveryAddress' => [
                    'email' => $request->email
                ],
                'personalization' => [
                    'fromName' => 'Amazing Rewards',
                    'to' => $request->name
                ]
            ]],
                'poNumber' => $request->poNumber,
                'type' => 'Links',
                'note' => $request->note
            ]);

        dd($response->body());

        return $response;
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
        $localSig = md5(("" . $request->PanelistId . $request->RewardId . "MDU3YmQzMjUtODhmMi00M2I5LWI2OTEtNGJmNDUyMzkzMmE0"));
        if ($localSig == $request->Sig) {
            $user = User::where('id', $request->PanelistId)->first();
            $user->points += $request->Reward;
            $user->save();
            return response()->json(null, 200);
        } else {
            return response()->json(null, 403);
        }
    }

    public function adJoeCallback(Request $request)
    {
        dd($request->sid);
        $s2sToken = "BXK3N6hXgY1I3jBHm2sm56lYFbpXnDUp";
        $localSig = sha1(($request->transId . $request->userId . $request->currency . $request->coinAmount . $request->deviceId . $request->sdkAppId . $request->s2sToken));
        if ($localSig == $sid) {
            $user = User::where('id', $userId)->first();
            $user->points += $coinAmount;
            $user->save();
            return response()->json(null, 200);
        } else {
            return response()->json(null, 403);
        }


    }

}

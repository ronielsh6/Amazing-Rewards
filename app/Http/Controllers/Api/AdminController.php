<?php

namespace App\Http\Controllers\Api;

use App\Mail\BuildMail;
use App\Promotion;
use App\Recargas;
use App\GiftCard;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

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
            'AccessToken' => env('EGITFTER_ACCESS_TOKEN'),
//            'AccessToken' => 'b9wh1nc1br1nt9nc9r69k16br9t2d710l9t11v1981nt989l16nd2v0nd0nh9r0j', PROD
            'Email' => 'info@myamazingrewards.com'
        ])->post(env('EGITFTER_URL').'/v1/Tokens');

        return $response->json("value");
    }

//    public function generateEgifterCard(Request $request)
//    {
//        $request->validate([
//            'value' => 'required',
//            'email' => 'required',
//            'poNumber' => 'required',
//            'note' => 'required',
//        ]);
//        $name = $request->name;
//        if($name == null){
//           $name = $request->email;
//        }
//
//        $token = $this->getAuthToken();
//
//        $response = Http::withHeaders([
//            'Authorization' => 'Bearer '.$token
//        ])->post(env('EGITFTER_URL').'/v1/Orders',
//            ['lineItems' => [[
//                'productId' => 'AMAZON',
//                'quantity' => 1,
//                'value' => $request->value,
//                'digitalDeliveryAddress' => [
//                    'email' => $request->email
//                ],
//                'personalization' => [
//                    'fromName' => 'Amazing Rewards',
//                    'to' => $name
//                ]
//            ]],
//                'poNumber' => $request->poNumber,
//                'type' => 'Links',
//                'note' => $request->note
//            ]);
//
//
//        return $response->json();
//    }

    public function createGiftCard(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'status' => 'required',
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
        Log::info($user->email. ' earned '.  $request->points);
        return response()->json($user);
    }

    public function getUserReferralCode(Request $request){

        $user = User::find($request->user()->id);
        $code = $user->referral_code;
        if ($code == null){
            $user->referral_code = $this->generateUniqueCode();
            $user->save();
            return response()->json([
                'data' => $user->referral_code]);
        }
        $user->points += $request->points;

        return response()->json($user);
    }

    public function updateFcmToken(Request $request){
        $user = User::find($request->user()->id);
        if($user->fcm_token != $request->fcm_token){
            $user->fcm_token = $request->fcm_token;
        }
        if($user->advertising_id == null){
            $user->advertising_id = $request->advertising_id;
        }
        if($request->app_version != null){
            $user->app_version = $request->app_version;
        }
            $user->touch();
            $user->save();
            return response()->json([
                'data' => $user->updated_at]);
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

    public function generateUniqueCode()
    {

        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);
        $codeLength = 6;

        $code = "";

        while (strlen($code) < 6) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $code.$character;
        }

        if (User::where('referral_code', $code)->exists()) {
            $this->generateUniqueCode();
        }

        return $code;
    }


    public function adJoeCallback(Request $request)
    {
        $s2sToken = "BXK3N6hXgY1I3jBHm2sm56lYFbpXnDUp";
        $localSig = sha1(($request->transId . $request->userId . $request->currency . $request->coinAmount . $request->deviceId . $request->sdkAppId . $s2sToken));
        if ($localSig == $request->sid) {
            $user = User::where('id', $request->userId)->first();
            $user->points += $request->coinAmount;
            $user->save();
            Log::info($user->email. ' earned '.  $request->coinAmount. 'points from AdJoe');
            return response()->json(null, 200);
        } else {
            return response()->json(null, 403);
        }


    }

    public function getEgifterOrders(Request $request)
    {

        $token = $this->getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token
        ])->get('https://rewards-api.egifter.com/v1/Orders?pageSize=20');


        return $response->json();
    }

    public function sendCustomNotification(Request $request)
    {
        $messaging = app('firebase.messaging');
        $deviceToken = "fnB4BluDTuyi65rwDyLNud:APA91bE_J_s7RX2taCYpfLnAoQf-PtJLVQA7enl5R7DNXkvVLB43I-5TDjNkV_x4RL5i0i0H2au7_gDHl2GQUxjnSTLFG60dNZvpYADGHu_6TAWDFcqlv0BDL7bVPtfW9Bb90uGDvRK1";

        $message = CloudMessage::withTarget('token', $deviceToken)
             ->withNotification(Notification::create($request->title, $request->body))
            ->withData(['key' => 'value']);

        $messaging->send($message);
    }

    public function sendVerificationCode(Request $request)
    {
        $user = User::find($request->user()->id);
        $characters = '0123456789';
        $charactersNumber = strlen($characters);
        $codeLength = 6;
        $code = "";
        while (strlen($code) < 6) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $code.$character;
        }
        $user->email_verification_code = $code;
        $user->save();
        Mail::to($user->email)->send(new BuildMail($user));

        return response()->json(null, 200);
    }


    public function verifyEmail(Request $request)
    {
        $user = User::find($request->user()->id);

        if($request->code == $user->email_verification_code){
            $user->email_verified_at = Carbon::now();
            $user->save();
            return response()->json([
                'message' => "Your email was verified successful."], 200);
        } else{
            return response()->json([
                'message' => "Wrong Code "], 403);
        }

    }


    public function updateLockScreenPermission(Request $request){
        $user = User::find($request->user()->id);
        $user->lock_screen = $request->lock_screen;
        $user->touch();
        $user->save();
        return response()->json([
            'data' => $user->updated_at]);
    }


}

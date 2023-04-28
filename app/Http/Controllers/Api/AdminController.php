<?php

namespace App\Http\Controllers\Api;

use App\GiftCard;
use App\Http\Controllers\Controller;
use App\Mail\BuildMail;
use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;

class AdminController extends Controller
{
    private const ALLOWED_COUNTRIES = ['Spain', 'Germany', 'United States'];

    public function getGiftCards(Request $request)
    {
        $giftCards = $request->user()->getGiftCards()->get();

        return response()->json([
            'data' => $giftCards, ]);
    }

    private function getAuthToken()
    {
        $response = Http::withHeaders([
            'AccessToken' => env('EGITFTER_ACCESS_TOKEN'),
            //            'AccessToken' => 'b9wh1nc1br1nt9nc9r69k16br9t2d710l9t11v1981nt989l16nd2v0nd0nh9r0j', PROD
            'Email' => 'info@myamazingrewards.com',
        ])->post(env('EGITFTER_URL').'/v1/Tokens');

        return $response->json('value');
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
        $user = User::find($request->user()->id);
        if ($user->points >= $request->amount * 1000) {
            $ip = $request->ip();
            $ip_data = @json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='.$ip), true, 512, JSON_THROW_ON_ERROR);
            $denied = false;

            if (! \in_array($ip_data['geoplugin_countryName'], [$request->country, $user->country], true) or ! \in_array($request->country, self::ALLOWED_COUNTRIES, true)) {
                $user->status = 'blocked';
                $user->getDevices()->update(['status' => 'blocked']);
                $user->touch();
                $user->save();

                return response()->json(
                    ['message' => 'You`re forbidden to use this app'],
                    409
                );
            }

            GiftCard::create($request->toArray());
            $user->points -= $request->amount * 1000;
            $user->save();
            if (! empty($user->referred_by) && User::find($user->referred_by)->exists()) {
                $userReferrer = User::find($user->referred_by);
                $userReferrer->points = $userReferrer->points + 500;
                $user->points = $user->points + 500;
                $user->referred_by = null;
                $userReferrer->save();
                $user->save();
                Log::info($user->email.' earned 500 points referred by '.$userReferrer->email);
                Log::info($userReferrer->email.' earned 500 points for referring '.$user->email);
            }
            $giftCards = $request->user()->getGiftCards()->get();

            return response()->json([
                'data' => $giftCards, ]);
        }

        return response()->json(
            null, 402
        );
    }

    public function addPoints(Request $request)
    {
        $request->validate([
            'points' => 'required',
        ]);
        $user = User::find($request->user()->id);
        $user->points += $request->points;
        $user->save();
        Log::info($user->email.' earned '.$request->points);

        return response()->json($user);
    }

    public function getUserReferralCode(Request $request)
    {
        $user = User::find($request->user()->id);
        $code = $user->referral_code;
        if ($code == null) {
            $user->referral_code = $this->generateUniqueCode();
            $user->save();

            return response()->json([
                'data' => $user->referral_code, ]);
        }
        $user->points += $request->points;

        return response()->json($user);
    }

    public function getPointsLogs(Request $request)
    {
        $user = User::find($request->user()->id);
        $log_viewer = new LaravelLogViewer();
        $result = [];
        $count = 0;
        $data = [
            'logs' => $log_viewer->all(),
        ];
        foreach ($data['logs'] as $datum) {
            if ($datum['level'] === 'info') {
                list($email, $action, $points, , $source) = explode(' ', $datum['text']);
                $date = $datum['date'];
//                if ($user->email == $email){
                $result[$count] = [
                    'email' => $email,
                    'points' => $points,
                    'date' => $date,
                    'action' =>$action,
                    'source'=> $source,
                ];
                $count++;
//                }
            }
        }

        return response()->json([
            'data' => $result, ]);
    }

    /**
     * @throws \JsonException
     */
    public function updateFcmToken(Request $request)
    {
        $user = User::find($request->user()->id);

        if ($user->fcm_token != $request->fcm_token) {
            $user->fcm_token = $request->fcm_token;
        }

        if ($user->advertising_id === null) {
            $user->advertising_id = $request->advertising_id;
        }

        if ($request->app_version !== null) {
            $user->app_version = $request->app_version;
        }

        if ($request->device_id !== null) {
            $device = $user->getDevices()->where('device.device_id', $request->device_id)->get()->count();
            if ($device < 1) {
                $newDevice = new Device([
                    'device_id' => $request->device_id,
                    'status' => 'active',
                ]);
                $newDevice->save();
                $user->getDevices()->attach($newDevice);
            }
        }

        if ($request->country !== null and $user->country === null) {
            $user->country = $request->country;
        }

        $ip = $request->ip();
        $ip_data = @json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='.$ip), true, 512, JSON_THROW_ON_ERROR);

        if (! \in_array($ip_data['geoplugin_countryName'], [$request->country, $user->country], true) or ! \in_array($request->country, self::ALLOWED_COUNTRIES, true)) {
            $user->status = 'blocked';
            $user->getDevices()->update(['status' => 'blocked']);
            $user->touch();
            $user->save();
        }

        $user->touch();
        $user->save();

        return response()->json([
            'data' => $user->updated_at, ]);
    }

    public function inBrainsCallback(Request $request)
    {
        $localSig = md5((''.$request->PanelistId.$request->RewardId.'MDU3YmQzMjUtODhmMi00M2I5LWI2OTEtNGJmNDUyMzkzMmE0'));
        if ($localSig == $request->Sig) {
            $user = User::where('id', $request->PanelistId)->first();
            $user->points += $request->Reward;
            $user->save();

            return response()->json(null, 200);
        }

        return response()->json(null, 403);
    }

    public function pollfishCallback(Request $request)
    {
        $secret_key = '6d2a7c79-6fac-4bd6-89fc-565eb66a48b7';
        $cpa = rawurldecode($_GET['cpa']);
        $device_id = rawurldecode($_GET['device_id']);
        $request_uuid = rawurldecode($_GET['request_uuid']);
        $reward_name = rawurldecode($_GET['reward_name']);
        $reward_value = rawurldecode($_GET['reward_value']);
        $timestamp = rawurldecode($_GET['timestamp']);
        $tx_id = rawurldecode($_GET['tx_id']);
        $url_signature = rawurldecode($_GET['signature']);

        $data = $cpa.':'.$device_id;
        if (! empty($request_uuid)) { // only added when non-empty
            $data = $data.':'.$request_uuid;
        }
        $data = $data.':'.$reward_name.':'.$reward_value.':'.$timestamp.':'.$tx_id;

        $computed_signature = base64_encode(hash_hmac('sha1', $data, $secret_key, true));
        $is_valid = $url_signature == $computed_signature;

        if ($is_valid) {
            $user = User::where('id', $request_uuid)->first();
            $user->points .= $reward_value;
            $user->save();
            Log::info($user->email.' earned '.$reward_value.'points from Pollfish');
        }
    }

    public function ayetCallback(Request $request)
    {
        $secret_key = '32412ef601bb6f918402d3cd1ca4ab10';
        $payout = rawurldecode($_GET['payout_usd']);
        $placement_identifier = rawurldecode($_GET['placement_identifier']);
        $adslot_id = rawurldecode($_GET['adslot_id']);
        $sub_id = rawurldecode($_GET['sub_id']);
        $url_signature = $request->header('X-Ayetstudios-Security-Hash');

        $data = $adslot_id.$payout.$placement_identifier.$sub_id;

        $computed_signature = base64_encode(hash_hmac('sha256', $data, $secret_key, true));
        $is_valid = $url_signature == $computed_signature;

        if ($is_valid) {
            $user = User::where('id', $sub_id)->first();
            $user->points += $payout * 1000;
            $user->save();
            Log::info($user->email.' earned '.$payout * 300 .'points from Ayet');
        }
    }

    /**
     * @throws \Exception
     */
    public function generateUniqueCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);
        $codeLength = 6;

        $code = '';

        while (strlen($code) < 6) {
            $position = random_int(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code .= $character;
        }

        if (User::where('referral_code', $code)->exists()) {
            $this->generateUniqueCode();
        }

        return $code;
    }

    public function adJoeCallback(Request $request)
    {
        $s2sToken = 'BXK3N6hXgY1I3jBHm2sm56lYFbpXnDUp';
        $localSig = sha1(($request->transId.$request->userId.$request->currency.$request->coinAmount.$request->deviceId.$request->sdkAppId.$s2sToken));
        if ($localSig == $request->sid) {
            $user = User::where('id', $request->userId)->first();
            if ($user != null) {
                $user->points += $request->coinAmount;
                $user->save();
                Log::info($user->email.' earned '.$request->coinAmount.'points from AdJoe');
            } else {
                $user = User::where('advertising_id', $request->deviceId)->first();
                if ($user != null) {
                    $user->points += $request->coinAmount;
                    $user->save();
                    Log::info($user->email.' earned '.$request->coinAmount.'points from AdJoe');
                } else {
                    $requestLog = str_replace("'", "\'", json_encode($request->all()));
                    Log::info('Incorrect userId from AdJoe'.$requestLog);
                }
            }

            return response()->json(null, 200);
        }

        return response()->json(null, 403);
    }

    public function getEgifterOrders(Request $request)
    {
        $token = $this->getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get('https://rewards-api.egifter.com/v1/Orders?pageSize=20');

        return $response->json();
    }

    public function sendCustomNotification(Request $request)
    {
        $messaging = app('firebase.messaging');
        $deviceToken = 'fnB4BluDTuyi65rwDyLNud:APA91bE_J_s7RX2taCYpfLnAoQf-PtJLVQA7enl5R7DNXkvVLB43I-5TDjNkV_x4RL5i0i0H2au7_gDHl2GQUxjnSTLFG60dNZvpYADGHu_6TAWDFcqlv0BDL7bVPtfW9Bb90uGDvRK1';

        $message = CloudMessage::withTarget('token', $deviceToken)
             ->withNotification(Notification::create($request->title, $request->body))
            ->withData(['key' => 'value']);

        $messaging->send($message);
    }

    /**
     * @throws \Exception
     */
    public function sendVerificationCode(Request $request)
    {
        $user = User::find($request->user()->id);
        $characters = '0123456789';
        $charactersNumber = strlen($characters);
        $codeLength = 6;
        $code = '';
        while (strlen($code) < 6) {
            $position = random_int(0, $charactersNumber - 1);
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

        if ($request->code == $user->email_verification_code) {
            $user->email_verified_at = Carbon::now();
            $user->save();

            return response()->json([
                'message' => 'Your email was verified successful.', ], 200);
        }

        return response()->json([
            'message' => 'Wrong Code ', ], 403);
    }

    public function updateLockScreenPermission(Request $request)
    {
        $user = User::find($request->user()->id);
        $user->lock_screen = $request->lock_screen;
        $user->touch();
        $user->save();

        return response()->json([
            'data' => $user->updated_at, ]);
    }
}

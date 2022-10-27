<?php

namespace App\Http\Controllers\Api;

use App\Promotion;
use App\Recargas;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Charge;
use Stripe\Stripe;

class RechargeApiController extends Controller
{
    public function getSaldo(Request $request)
    {
        return response()->json(['saldo' =>
            $request->user()->saldo], 200);
    }

    public function makeRecharge(Request $request)
    {


        $request->validate([
            'number' => 'required',
            'amount' => 'required'
        ]);



        $login = "cubaphone";
        $token = "658987837246";
        $key = time();
        $md5 = md5($login . $token . $key);
        $url = "https://airtime-api.dtone.com/cgi-bin/shop/topup";
        $client = new Client(['verify' => false, 'decode_content' => false]);
        $count = 0;
        $numbers = array();
        $success_numbers = array();
        $amounts_successful = array();
        $error = false;
        $numbers_to_recharge = $request->get('number');
        $amounts = $request->get('amount');
        $sum = 0;
        $list = '';
        $success = '[';
        $issuccess = false;
        $parent = User::find(\Auth::user()->parent);
        foreach ($amounts as $amount) {
            $sum += $amount;
        }


        if (\Auth::user()->saldo - $sum < 0) {
            return response()->json([
                'message' => 'Usted no cuenta con saldo suficiente']);
        }
        $sum = 0;
        $promotion = Promotion::first();

        foreach ($numbers_to_recharge as $number) {
            $key += $count + Auth::user()->id;
            $md5 = md5($login . $token . $key);
            $amount = $amounts[$count] == \Auth::user()->coefficient ? 20 : $amounts[$count];


            if ($promotion->start_date > Carbon::now()->toDateString() && $amount >= 20) {
                $recarga = new Recargas();
                $recarga->owner = Auth::user()->id;
                $recarga->receipt = $number;
                if ($amount == 20) {
                    $recarga->amount = Auth::user()->coefficient;
                } else {
                    $recarga->amount = $amount;
                }
                $recarga->pending = true;

                $recarga->type = 'mobile';
                $recarga->save();
                if (\Auth::user()->saldo - $sum < 0) {
                    return response()->json([
                        'message' => 'Su recarga han sido planificadas para' . $promotion->start_date]);
                }

            } else {
                $response = $client->request('GET', $url, [
                    'query' => [
                        "login" => $login,
                        'key' => $key,
                        'md5' => $md5,
                        'action' => 'simulation',
                        'destination_msisdn' => '53' . $number,
                        'msisdn' => '+17865020974',
                        'product' => $amount,
                        'operatorid' => 161,
                        'currency' => 'USD',
                        'sender_sms' => 'no'
                    ]
                ]);

                $responseJson = $response->getBody()->getContents();


                if ($response->getStatusCode() != 200 || !Str::contains($responseJson, 'error_code=0')) {
                    array_push($numbers, $number);
                    $error = true;
                    $count++;
                    continue;

                } else {
                    $recarga = new Recargas();
                    $recarga->owner = Auth::user()->id;
                    $recarga->receipt = $number;
                    if ($amount == 20) {
                        $recarga->amount = Auth::user()->coefficient;
                    } else {
                        $recarga->amount = $amount;
                    }
                    $recarga->pending = false;

                    $recarga->type = 'mobile';
                    $recarga->save();
                    $issuccess = true;
                    array_push($success_numbers, $number);
                    $sum += $amounts[$count];
                    $parent->saldo -= $parent->coefficient;
                    $count++;

                }
            }
            $count++;
        }


        $user = User::find(\Auth::user()->id);
        $user->saldo -= $sum;
        $user->save();
        $parent->save();




        if ($error) {
            $list = ' ';
            foreach ($numbers as $number) {
                $list .= $number . ' ';
            }

            return response()->json([
                'message' => 'Algo salio mal, solo se pudo recargar el número' . $list]);
        }


        if($issuccess) {
            foreach ($success_numbers as $number) {
                $success .= $number . ',';
            }
            $success .= "]";
            Log::info('Se ha realizado correctamente la recarga de los números ' . $success . ' por el usuario ' . \Auth::user()->username);
            return response()->json([
                'message' => 'Se ha realizado correctamente la recarga de los números ' . $success]);
        }
    }


    public function rechargeNauta(Request $request)
    {
        $emails = $request->emails;
        $products = $request->amount;
        $count = 0;
        $numbers = array();
        $success = '[';
        $executed = false;

        if (\Auth::user()->saldo - 10 < 0) {
            return response()->json([
                'message' => 'Usted no cuenta con saldo suficiente']);
        }


        $successfull = 0;
        foreach ($emails as $email) {
            $account_number = $email;
            $external_id = time(); # Your company unique Transaction ID.
            $simulation = '0';     # 1 = This is a Simulation, 0 =  This is a Real Transaction
            $sender_sms_notification = '1'; # The sender will receive a notification
            $recipient_sms_notification = '0'; # The recipient will receive a notification
            $sender_email = 'ronielsh6@gmail.com';
            $sender_mobile = "17865020974";   # Mobile number for Sender Notification
            $recipient_email = $email;
            $data = array(
                'account_number' => $account_number,
                'product_id' => $products[$count],
                'external_id' => $external_id,
                'simulation' => $simulation,
                'sender_sms_notification' => $sender_sms_notification,
                'recipient_sms_notification' => $recipient_sms_notification,
                'sender' => array(
                    'email' => $sender_email,
                    'mobile' => $sender_mobile
                ),
                'recipient' => array(
                    'email' => $recipient_email
                )
            );
            $data_json = json_encode($data);
            $api_key = '1d4289dd-9507-4f69-a3a1-df04a344ec13';
            $api_secret = 'ec11cf23-07a5-457f-a1cf-80057555227a';
            $nonce = time();
            $host = 'https://gs-api.dtone.com/v1.1';
            $resource = 'transactions/fixed_value_recharges';
            $hmac = base64_encode(hash_hmac('sha256', $api_key . $nonce, $api_secret, true));
            $ch = curl_init();
            $url = "$host/$resource";
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X-TransferTo-apikey: $api_key",
                "X-TransferTo-nonce: $nonce",
                "X-TransferTo-hmac: $hmac",
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_json)
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            $output = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($status == 0 || $status == 201)
                $successfull++;
            else
                $numbers[$count] = $email;


            $count++;
        }


        if (count($emails) == $successfull) {
            foreach ($emails as $number) {
                $success .= $number . ',';
            }
            $success .= "]";
            Log::info('Se ha realizado correctamente la recarga de los emails ' . $success . ' por el usuario ' . \Auth::user()->username);
            return response()->json([
                'message' => 'Se ha realizado correctamente la recarga de forma exitosa ']);
        } else {
            $list = ' ';
            foreach ($emails as $number) {
                $list .= $number . ' ';
            }
            return response()->json([
                'message' => 'Algo ha salido mal ']);
        }


    }

}

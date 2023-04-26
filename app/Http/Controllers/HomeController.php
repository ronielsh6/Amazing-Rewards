<?php

namespace App\Http\Controllers;

use App\GiftCard;
use App\Models\User;
use App\Services\CloudMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function blacklist()
    {
        return view('blacklist');
    }

    public function getUsers(Request $request)
    {
        $start = $request->get('start');
        $page = $request->get('length');
        $username = $request->get('username');
        $relative = $request->get('relative');
        $points = $request->get('points');
        $orderElement = $request->get('order')[0];
        $orderDir = $orderElement['dir'];
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $usersQuery = DB::table('users')->where('status', '=', 'active');

        if (! empty($username)) {
            $usersQuery->where(function ($query) use ($username) {
                $query->where('email', 'like', '%'.$username.'%');
            });
        }

        if (! empty($points)) {
            $between = \explode(',', $points);
            if (\count($between) > 1) {
                $usersQuery->whereBetween('points', $between);
            }

            if (\count($between) === 1) {
                $usersQuery->where('points', $relative, $points);
            }
        }
        $usersQuery->orderBy($column, $orderDir);
        $totalRecordsFiltered = $usersQuery->get()->count();
        if ($start > 0) {
            $offset = ($start / $page);
            $usersQuery->offset($offset * $page);
        }
        $usersQuery->limit($page);
        $users = $usersQuery->get()->toArray();

        return response()->json([
            'data' => $users,
            'recordsTotal' => $totalRecordsFiltered,
            'recordsFiltered' => $totalRecordsFiltered,
        ]);
    }

    public function blockedUsers(Request $request)
    {
        $start = $request->get('start');
        $page = $request->get('length');
        $username = $request->get('username');
        $relative = $request->get('relative');
        $points = $request->get('points');
        $orderElement = $request->get('order')[0];
        $orderDir = $orderElement['dir'];
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $usersQuery = DB::table('users')->where('status', '=', 'blocked');

        if (! empty($username)) {
            $usersQuery->where(function ($query) use ($username) {
                $query->where('email', 'like', '%'.$username.'%');
            });
        }

        if (! empty($points)) {
            $between = \explode(',', $points);
            if (\count($between) > 1) {
                $usersQuery->whereBetween('points', $between);
            }

            if (\count($between) === 1) {
                $usersQuery->where('points', $relative, $points);
            }
        }
        $usersQuery->orderBy($column, $orderDir);
        $totalRecordsFiltered = $usersQuery->get()->count();
        if ($start > 0) {
            $offset = ($start / $page);
            $usersQuery->offset($offset * $page);
        }
        $usersQuery->limit($page);
        $users = $usersQuery->get()->toArray();

        return response()->json([
            'data' => $users,
            'recordsTotal' => $totalRecordsFiltered,
            'recordsFiltered' => $totalRecordsFiltered,
        ]);
    }

    public function deleteUsers(Request $request)
    {
        $userId = $request->get('user');
        $user = User::find($userId);

        $destinationStatus = \array_diff(User::USER_STATUS, [$user->status]);
        $status = \array_values($destinationStatus)[0];
        $user->status = $status;

        if ($status === 'blocked') {
            GiftCard::with('getOwner')
                ->where('owner', $user->id)
                ->where('pending', 1)
                ->delete();
        }

        $user->save();

        return response()->json([
            'code' => 200,
            'message' => 'User deleted sucessfully',
        ]);
    }

    public function getUserGiftCards(Request $request)
    {
        $userId = $request->get('user-id');

        return view('giftCards', [
            'userId' => $userId,
        ]);
    }

    public function getGiftCards(Request $request)
    {
        $userId = $request->get('userId');
        $start = $request->get('start');
        $page = $request->get('length');
        $orderElement = $request->get('order')[0];
        $orderDir = $orderElement['dir'];
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $giftCardsQuery = GiftCard::with(['getOwner']);
        if (! empty($userId) && $userId !== 0) {
            $giftCardsQuery->where('owner', $userId);
        }
        $totalRecordsFiltered = $giftCardsQuery->get()->count();

        if ($start > 0) {
            $offset = ($start / $page);
            $giftCardsQuery->offset($offset * $page);
        }

        $giftCardsQuery->orderBy($column, $orderDir);
        $giftCardsQuery->limit($page);
        $giftcards = $giftCardsQuery->get()->toArray();

        return response()->json([
            'data' => $giftcards,
            'recordsTotal' => $totalRecordsFiltered,
            'recordsFiltered' => $totalRecordsFiltered,
        ]);
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

    public function getEnabledGiftCard(Request $request)
    {
        $giftcard = $request->get('card');
        $user = $request->get('userId');

        $userObj = DB::table('users')->where('id', $user)->first();

        $giftCardItem = DB::table('gift_card')->where('id', $giftcard)
            ->where('owner', $user)->first();

        if (! $giftCardItem) {
            return response()->json([
                'code' => 400,
                'message' => 'Data error',
            ]);
        }

        $eGifterResponse = $this->generateEgifterCard($giftCardItem, $userObj);

        if (array_key_exists('previousOrderIds', $eGifterResponse)) {
            return response()->json([
                'code' => 400,
                'message' => 'Gift Card Id Already exist in eGifter.',
            ]);
        }
        $giftCard = GiftCard::find($giftcard);
        $giftCard->claim_link = $eGifterResponse['lineItems'][0]['claimData'][0]['claimLink'];
        $giftCard->challenge_code = $eGifterResponse['lineItems'][0]['claimData'][0]['claimLinkChallengeAnswer'];
        $giftCard->egifter_id = $eGifterResponse['id'];
        $giftCard->touch();
        $giftCard->save();

        $affectedRows = DB::table('gift_card')->where('id', $giftcard)
            ->where('owner', $user)
            ->update(['pending' => 0]);

        if ($affectedRows > 0) {
            return response()->json([
                'code' => 200,
                'message' => 'Gift Card was activated successfully.',
            ]);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Data error',
        ]);
    }

    public function sendMessages(Request $request)
    {
        $title = $request->get('title');
        $body = $request->get('body');
        $deepLink = $request->get('deepLink');
        $ids = $request->get('users');
        $errors = false;
        foreach ($ids as $id) {
            $user = User::find($id);
            $response = (new CloudMessages())->sendMessage($title, $body, $user, ['deep_link' => $deepLink]);
            if (! $response) {
                $errors = true;
            }
        }

        return response()->json([
            'code' => 200,
            'errors' => $errors,
        ]);
    }

    private function generateEgifterCard($card, $user)
    {
        $name = $user->name;
        if ($name == null) {
            $name = $user->email;
        }
        $token = $this->getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(env('EGITFTER_URL').'/v1/Orders',
            ['lineItems' => [[
                'productId' => 'AMAZON',
                'quantity' => 1,
                'value' => $card->amount,
                'digitalDeliveryAddress' => [
                    'email' => $user->email,
                ],
                'personalization' => [
                    'fromName' => 'Amazing Rewards',
                    'to' => $name,
                ],
            ]],
                'poNumber' => $user->email.''.$card->id,
                'type' => 'Links',
                'note' => $user->email,
            ]);

        return $response->json();
    }
}

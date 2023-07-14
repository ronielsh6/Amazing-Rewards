<?php

namespace App\Http\Controllers;

use App\GiftCard;
use App\Models\BlockedLog;
use App\Models\Campaign;
use App\Models\Device;
use App\Models\GiftcardLog;
use App\Models\PromoCodes;
use App\Models\User;
use App\Services\CloudMessages;
use Google\CRC32\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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
        $usersQuery = User::where('status', '=', 'active');

        if (!empty($username)) {
            $usersQuery->where('email', 'like', '%' . $username . '%');
        }

        if (!empty($points)) {
            $between = \explode(',', $points);
            if (\count($between) > 1) {
                $usersQuery->whereBetween('points', $between);
            }

            if (\count($between) === 1) {
                $usersQuery->where('points', $relative, $points);
            }
        }

        return $this->extracted($usersQuery, $column, $orderDir, $start, $page);
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
        $usersQuery = User::where('status', '=', 'blocked');

        if (!empty($username)) {
            $usersQuery->where('email', 'like', '%' . $username . '%');
        }

        if (!empty($points)) {
            $between = \explode(',', $points);
            if (\count($between) > 1) {
                $usersQuery->whereBetween('points', $between);
            }

            if (\count($between) === 1) {
                $usersQuery->where('points', $relative, $points);
            }
        }

        return $this->extracted($usersQuery, $column, $orderDir, $start, $page);
    }

    public function deleteUsers(Request $request)
    {
        $userId = $request->get('user');
        $user = User::find($userId);

        $destinationStatus = \array_diff(User::USER_STATUS, [$user->status]);
        $status = \array_values($destinationStatus)[0];
        $user->status = $status;
        $user->getDevices()->update(['status' => $status]);
        $devices = $user->getDevices()->get();
        foreach ($devices as $device) {
            $device->getUsers()->update(['status' => $status]);
        }

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
        $active = $request->get('active');
        $username = $request->get('username');
        $relative = $request->get('relative');
        $points = $request->get('points');
        $orderDir = $orderElement['dir'];
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $giftCardsQuery = GiftCard::with(['getOwner']);
        if (!empty($userId) && $userId !== 0) {
            $giftCardsQuery->where('owner', $userId);
        }

        if (!empty($username)) {
            $giftCardsQuery->whereHas('getOwner', function ($query) use ($username) {
                $query->where('email', 'like', '%' . $username . '%');
            });
        }

        if (!empty($points)) {
            $between = \explode(',', $points);
            if (\count($between) > 1) {
                $giftCardsQuery->whereBetween('amount', $between);
            }

            if (\count($between) === 1) {
                $giftCardsQuery->where('amount', $relative, $points);
            }
        }

        $giftCardsQuery->where('pending', $active === 'true' ? 0 : 1);
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

    public function getGiftCardsLogs(Request $request)
    {
        $start = $request->get('start');
        $page = $request->get('length');
        $orderElement = $request->get('order')[0];
        $username = $request->get('username');
        $orderDir = $orderElement['dir'];
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $giftCardsLogsQuery = GiftcardLog::with(['getGiftcard', 'getGiftcard.getOwner']);

        if (!empty($username)) {
            $giftCardsLogsQuery->whereHas('getGiftcard', function ($query) use ($username) {
                $query->whereHas('getOwner', function ($subquery) use ($username) {
                    $subquery->where('email', 'like', '%' . $username . '%');
                });

                $query->where('pending', true);
            });
        }

        $totalRecordsFiltered = $giftCardsLogsQuery->get()->count();

        if ($start > 0) {
            $offset = ($start / $page);
            $giftCardsLogsQuery->offset($offset * $page);
        }

        $giftCardsLogsQuery->orderBy($column, $orderDir);
        $giftCardsLogsQuery->limit($page);
        $giftcards = $giftCardsLogsQuery->get()->toArray();

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
        ])->post(env('EGITFTER_URL') . '/v1/Tokens');

        return $response->json('value');
    }

    public function getEnabledGiftCard(Request $request)
    {
        $giftcard = $request->get('card');
        $user = $request->get('userId');
        $deleteCard = $request->get('deleteCard');

        $userObj = DB::table('users')->where('id', $user)->first();

        $giftCardItem = GiftCard::where('id', $giftcard)
            ->where('owner', $user)->first();

        if (!$giftCardItem) {
            return response()->json([
                'code' => 400,
                'message' => 'Data error',
            ]);
        }

        if ($deleteCard === 'true') {
            $giftCardItem->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Gift Card was deleted successfully.',
            ]);
        }

        try {
            $eGifterResponse = $this->generateEgifterCard($giftCardItem, $userObj);
        } catch (\Exception $exception) {
            $giftcardLog = new GiftcardLog([
                'reason' => $exception->getMessage(),
            ]);

            $giftCardItem->getLogs()->save($giftcardLog);

            return response()->json([
                'code' => 404,
                'message' => 'It wasn`t possible to enable the gift card. ' . $exception->getMessage(),
            ]);
        }

        if (array_key_exists('previousOrderIds', $eGifterResponse)) {
            $giftcardLog = new GiftcardLog([
                'reason' => 'Gift Card Id Already exist in eGifter.',
            ]);

            $giftCardItem->getLogs()->save($giftcardLog);

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
            $response = (new CloudMessages())->sendMessage($title, $body, $user, ['deep_link' => $deepLink], true);
            if (!$response) {
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
            'Authorization' => 'Bearer ' . $token,
        ])->post(env('EGITFTER_URL') . '/v1/Orders',
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
                'poNumber' => $user->email . '' . $card->id,
                'type' => 'Links',
                'note' => $user->email,
            ]);

        return $response->json();
    }

    public function getBlockedLogs(Request $request)
    {
        return view('blockedLogs');
    }

    public function getBlockedLogsList(Request $request)
    {
        $start = $request->get('start');
        $page = $request->get('length');
        $username = $request->get('username');
        $orderElement = $request->get('order')[0];
        $orderDir = $orderElement['dir'];
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $blockedLogsQuery = BlockedLog::with('getUser');

        if (!empty($username)) {
            $blockedLogsQuery->whereHas('getUser', function ($query) use ($username) {
                $query->where('email', 'like', '%' . $username . '%');
            });
        }

        return $this->extracted($blockedLogsQuery, 'created_at', $orderDir, $start, $page);
    }

    public function getPromoCodes(Request $request)
    {
        $users = User::query()->get();
        return view('promoCodes', ['users' => $users]);
    }

    public function getPromoCodesList(Request $request)
    {
        $start = $request->get('start');
        $page = $request->get('length');
        $username = $request->get('username');
        $orderElement = $request->get('order')[0];
        $orderDir = $orderElement['dir'];
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $promoCodesQuery = PromoCodes::query();

        return $this->extracted($promoCodesQuery, 'created_at', $orderDir, $start, $page);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $blockedLogsQuery
     * @param mixed $column
     * @param mixed $orderDir
     * @param mixed $start
     * @param mixed $page
     * @return \Illuminate\Http\JsonResponse
     */
    private function extracted(\Illuminate\Database\Eloquent\Builder $blockedLogsQuery, mixed $column, mixed $orderDir, mixed $start, mixed $page): \Illuminate\Http\JsonResponse
    {
        $blockedLogsQuery->orderBy($column, $orderDir);
        $totalRecordsFiltered = $blockedLogsQuery->get()->count();
        if ($start > 0) {
            $offset = ($start / $page);
            $blockedLogsQuery->offset($offset * $page);
        }
        $blockedLogsQuery->limit($page);
        $users = $blockedLogsQuery->get()->toArray();

        return response()->json([
            'data' => $users,
            'recordsTotal' => $totalRecordsFiltered,
            'recordsFiltered' => $totalRecordsFiltered,
        ]);
    }

    public function deletePromoCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $promoId = $request->get('id');
        $promoCode = PromoCodes::find($promoId)->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Promo Code deleted sucessfully',
        ]);
    }

    public function generatePromoCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);
        $codeLength = 6;

        $code = '';

        while (strlen($code) < 9) {
            $position = random_int(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code .= $character;
        }

        if (PromoCodes::where('code', $code)->exists()) {
            $this->generatePromoCode();
        }

        return $code;
    }

    public function createPromoCode(Request $request)
    {
        $jsonTargets = json_encode(response()->json(
            ["data" => $request->targets]
        )->getData());
        $promoCode = new PromoCodes();
        $promoCode->amount = $request->amount;
        $promoCode->expiration_date = $request->expiration_date;
        if ($request->code != null){
            if (PromoCodes::where('code', $request->code)->exists()) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Code already exist.',
                ]);
            } else {
                $promoCode->code = $request->code;
            }
        } else {
            $promoCode->code = $this->generatePromoCode();
        }
        $promoCode->targets = $jsonTargets;
        $promoCode->save();
        if ($promoCode) {
            return response()->json([
                'code' => 200,
                'message' => 'Campaign created successfully',
            ]);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Error creating promo code',
        ]);
    }

    public function updatePromoCode(Request $request)
    {
        $promoCode = new PromoCodes($request->all());
        $promoCode->save();
        if ($promoCode) {
            return response()->json([
                'code' => 200,
                'message' => 'Campaign created successfully',
            ]);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Error creating promo code',
        ]);
    }

    public function filterPromoTarget(Request $request)
    {
        $start = $request->get('start');
        $page = $request->get('length');
        $orderElement = $request->get('order')[0];
        $orderDir = $orderElement['dir'];
        $q = $request->get('q');
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $usersQuery = User::where('email', 'like', '%' . $q . '%');


        return $this->extracted($usersQuery, $column, $orderDir, $start, $page);
    }
}

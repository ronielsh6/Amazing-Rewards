<?php

namespace App\Http\Controllers;

use App\GiftCard;
use App\Models\User;
use App\Services\CloudMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $usersQuery = DB::table('users')->limit($page);
        if($page >= 0) {
            $usersQuery->offset($start*$page);
        }

        if(!empty($username)) {
            $usersQuery->where(function($query) use ($username){
                $query->where('name', 'like', '%'. $username .'%')
                ->orWhere('email', 'like', '%'. $username .'%');
            });
        }

        if(!empty($points)) {
            $between = \explode(',', $points);
            if(\count($between) > 1) {
                $usersQuery->whereBetween('points', $between);
            }

            if(\count($between) === 1) {
                $usersQuery->where('points', $relative, $points);
            }
        }
        $usersQuery->orderBy($column, $orderDir);
        $users = $usersQuery->get()->toArray();
        $total = DB::table('users')->get()->count();
        return response()->json([
            'data' => $users,
            'recordsTotal'=> $total,
            'recordsFiltered' => \count($users)
        ]);
    }

    public function deleteUsers(Request $request)
    {
        $userId = $request->get('user');
        $user = User::find($userId);
        $user->delete();

        return response()->json([
            'code' => 200,
            'message' => 'User deleted sucessfully'
        ]);
    }

    public function getUserGiftCards(Request $request)
    {
        $userId = $request->get('user-id');
        return view('giftCards', [
            'userId' => $userId
        ]);
    }

    public function getGiftCards(Request $request)
    {
        $userId = $request->get('userId');
        $start = $request->get('start');
        $page = $request->get('length');
        $giftCardsQuery = GiftCard::with(['getOwner']);
        if(!empty($userId) && $userId !==0 ){
            $giftCardsQuery->where('owner', $userId);
        }

        if($page >= 0) {
            $giftCardsQuery->offset($start*$page);
        }
        $giftCardsQuery->limit($page);
        $giftcards = $giftCardsQuery->get()->toArray();
        $totalGiftCardsForUser = DB::table('gift_card')->where('owner', $userId)->get()->count();
        return response()->json([
            'data' => $giftcards,
            'recordsTotal'=> $totalGiftCardsForUser,
            'recordsFiltered' => \count($giftcards)
        ]);
    }

    public function getEnabledGiftCard(Request $request)
    {
        $giftcard = $request->get('card');
        $user = $request->get('userId');

        $giftCardItem = DB::table('gift_card')->where('id', $giftcard)
                ->where('owner', $user)->get();

        if(!$giftCardItem) {
            return response()->json([
                'code' => 400,
                'message' => 'Data error'
            ]);
        }

        $affectedRows = DB::table('gift_card')->where('id', $giftcard)
                ->where('owner', $user)
                ->update(['pending' => 0]);

        if($affectedRows > 0) {
            return response()->json([
                'code' => 200,
                'message' => 'Gift Card was activated sucessfully.'
            ]);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Data error'
        ]);
    }

    public function sendMessages(Request $request)
    {
        $title = $request->get('title');
        $body = $request->get('body');
        $ids = $request->get('users');
        foreach ($ids as $id) {
            $user = User::find($id);
            (new CloudMessages())->sendMessage($title, $body, $user);
        }
    }
}

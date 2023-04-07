<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $request['password']=Hash::make($request['password']);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        $user->referral_code = $this->generateUniqueCode();
        $user->save();
        if(!empty($request->referrer_code) && User::where('referral_code', $request->referrer_code)->exists()){
            $userReferrer = User::where('referral_code', $request->referrer_code)->first();
            $user->referred_by = $userReferrer->id;
            $user->save();
        }
        return response($response, 200);
    }

    private function generateUniqueCode()
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

    public function googleAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
        } else {
            $request['password'] = Hash::make($request['password']);
            $user = User::create($request->toArray());
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['token' => $token];
        }

        return response($response, 200);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                return response($response, 200);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" =>'User does not exist'];
            return response($response, 422);
        }

    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' =>
            'Successfully logged out']);
    }


    public function user(Request $request)
    {
        $user = User::find($request->user()->id);
        if (empty($user->referral_code)){
            $user->referral_code = $this->generateUniqueCode();
            $user->save();
        }
        return response()->json($request->user());
    }

//     public function isLoggedOut(): Response
//     {
//         return response()->json([
//             'message' => 'Successfully legged out'],201);
//     }
}

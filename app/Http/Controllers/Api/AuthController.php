<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $request['password'] = Hash::make($request['password']);

//      TODO: This is query blocking users
//        $device = Device::where('device_id', $request->device_id)->where('status', 'blocked')->get();
//        if ($device->count() > 0) {
//            return response()->json(
//                ['message' => 'deviceIdValidationForbidden'],
//                409
//            );
//        }

        $existDevice = Device::where('device_id', $request->device_id)->first();
        $user = User::create($request->toArray());

        if ($existDevice) {
            $user->getDevices()->attach($existDevice);
        } else {
            $newDevice = new Device([
                'device_id' => $request->device_id,
                'status' => 'active',
            ]);
            $newDevice->save();
            $user->getDevices()->attach($newDevice);
        }

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        $user->referral_code = $this->generateUniqueCode();
        $user->save();
        if (! empty($request->referrer_code) && User::where('referral_code', $request->referrer_code)->exists()) {
            $userReferrer = User::where('referral_code', $request->referrer_code)->first();
            $user->referred_by = $userReferrer->id;
            $user->save();
        }
        Log::info($user->email . ' earned 1000 points from SignUp');
        return response($response, 200);
    }

    /**
     * @throws Exception
     */
    private function generateUniqueCode(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);
        $codeLength = 6;

        $code = '';

        while (strlen($code) < 6) {
            $position = random_int(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $code.$character;
        }

        if (User::where('referral_code', $code)->exists()) {
            $this->generateUniqueCode();
        }

        return $code;
    }

    public function googleAuth(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

//        TODO: This is query blocking users
//        $device = Device::where('device_id', $request->device_id)->where('status', 'blocked')->get();
//        if ($device->count() > 0) {
//            return response()->json(
//                ['message' => 'deviceIdValidationForbidden'],
//                409
//            );
//        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user->status === 'blocked') {
                return response()->json(
                    ['message' => 'userForbidden'],
                    403
                );
            }

            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['token' => $token];
        } else {
            $request['password'] = Hash::make($request['password']);
            $user = User::create($request->toArray());
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['token' => $token];
            Log::info($user->email . ' earned 1000 points SignUp');
        }

        $deviceExist = Device::where('device_id', $request->device_id)->first();
        if (! $deviceExist) {
            $device_id = $request->device_id;
            $device = new Device([
                'device_id' => $device_id,
                'status' => 'active',
            ]);
            $device->save();
            $user->getDevices()->attach($device);
        } else {
            $user->getDevices()->attach($deviceExist);
        }

        return response()->json($response, 200);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->status === 'blocked') {
                return response()->json([
                    'message'=> 'userForbidden',
                ], 403);
            }

            $device_id = $request->device_id;

//            if (Device::where('device_id', $device_id)->where('status', 'blocked')->count() > 0) {
//                return response()->json([
//                    'message'=> 'deviceIdValidationError',
//                ], 403);
//            }

            $existDevice = Device::where('device_id', $device_id)->first();

            if (! $existDevice) {
                $device = new Device([
                    'device_id' => $device_id,
                    'status' => 'active',
                ]);

                $device->save();
                $user->getDevices()->attach($device);
            } else {
                $user->getDevices()->attach($existDevice);
            }

            $user->touch();
            $user->save();

            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];

                return response()->json($response, 200);
            }

            $response = ['message' => 'Password mismatch'];

            return response()->json($response, 422);
        }

        $response = ['message' =>'User does not exist'];

        return response()->json($response, 422);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user(Request $request)
    {
        $user = User::find($request->user()->id);
        if (empty($user->referral_code)) {
            $user->referral_code = $this->generateUniqueCode();
            $user->save();
        }

        return response()->json($request->user());
    }

    public function forgot(Request $request, ForgotPasswordController $forgotPasswordController)
    {
        $forgotPasswordController->sendResetLinkEmail($request);
    }
}

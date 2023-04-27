<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        try {
            $device = Device::where('device_id', $request->device_id)->where('status', 'blocked')->get();
            if ($device->count() > 0) {
                return response()->json(
                    ['message' => 'deviceIdValidationForbidden'],
                    409
                );
            }
            $newDevice = new Device([
                'device_id' => $request->device_id,
                'status' => 'active',
            ]);
            $newDevice->save();
            $user = User::create($request->toArray());
            $user->getDevices()->attach($newDevice);
        } catch (QueryException $queryException) {
            $errorCode = $queryException->getCode();
            if ((int) $errorCode === 23000) {
                return response()->json(
                    ['message' => 'deviceIdValidationError'],
                    409
                );
            }
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

        $device = Device::where('device_id', $request->device_id)->where('status', 'blocked')->get();
        if ($device->count() > 0) {
            return response()->json(
                ['message' => 'deviceIdValidationForbidden'],
                409
            );
        }
        $user = User::where('email', $request->email)->where('status', 'active')->first();
        if ($user) {
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['token' => $token];
            if ($user->getDevices()->where('device_id', $request->device_id)->count() < 1) {
                $device = new Device([
                    'device_id' => $request->device_id,
                    'status' => 'active',
                ]);
                $user->getDevices()->attach($device);
            }
        } else {
            $request['password'] = Hash::make($request['password']);
            try {
                $user = User::create($request->toArray());
                $device = new Device([
                    'device_id' => $request->device_id,
                    'status' => 'active',
                ]);
                $user->getDevices()->attach($device);
            } catch (QueryException $queryException) {
                $errorCode = $queryException->getCode();
                if ((int) $errorCode === 23000) {
                    return response()->json(
                        ['message' => 'deviceIdValidationError'],
                        409
                    );
                }
            }
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['token' => $token];
        }

        if ($user->device_id !== $request->device_id) {
            return response()->json(
                ['message' => 'deviceIdValidationError'],
                409
            );
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
            $isBlocked = $user->getDevices()->where('status', 'blocked')->get();
            if ($isBlocked->count() > 0 or $user->status === 'blocked') {
                return response()->json(
                    ['message' => 'userBlocked'],
                    403
                );
            }

            $device_id = $request->device_id;

            $existDevice = $user->getDevices()->find($device_id);

            if ($existDevice === null) {
                $device = new Device([
                    'device_id' => $device_id,
                    'status' => 'active',
                ]);

                $device->save();
                $user->getDevices()->attach($device);
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
}

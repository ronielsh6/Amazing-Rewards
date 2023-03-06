<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class CloudMessages
{
    public function sendMessage(string $title, string $body, User $user, array $data = [], bool $log = false) : bool
    {
        $messaging = app('firebase.messaging');

        if (!empty($user->fcm_token)) {
            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            try {
                $result = $messaging->send($message);
            }catch (FirebaseException $exception) {
                if ($log) {
                    Log::info($exception->getMessage() . ' Exception generated for user ' . $user->email);
                }
                return false;
            }

            if (is_array($result)) {
                return true;
            }
        }

        if ($log) {
            Log::info('User ' . $user->email . ' has not FCM token.');
        }

        return false;
    }
}

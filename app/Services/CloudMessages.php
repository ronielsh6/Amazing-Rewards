<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class CloudMessages
{
    public function sendMessage(string $title, string $body, User $user) : bool
    {
        $messaging = app('firebase.messaging');
//        $deviceToken = "fnB4BluDTuyi65rwDyLNud:APA91bE_J_s7RX2taCYpfLnAoQf-PtJLVQA7enl5R7DNXkvVLB43I-5TDjNkV_x4RL5i0i0H2au7_gDHl2GQUxjnSTLFG60dNZvpYADGHu_6TAWDFcqlv0BDL7bVPtfW9Bb90uGDvRK1";

        if ($user->fcm_token) {
            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withData(['key' => 'value']);

            $messaging->send($message);

            return true;
        }

        return false;
    }
}

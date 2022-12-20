<?php

namespace App\Listeners;

use App\Events\SubConsoleOrder;
use App\FirebaseToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendSubConsoleNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    protected $messaging;
    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Handle the event.
     *
     * @param  SubConsoleOrder  $event
     * @return void
     */
    public function handle(SubConsoleOrder $event)
    {
        $userId = $event->order->user_id;
        //20210308 - TID : qyMBSbR5 - START
        // $fcmToken = FirebaseToken::where('user_id', $userId)->orderBy('updated_at','desc')->first();
        // $fcmToken = FirebaseToken::where('user_id', $userId)->orderBy('created_at','desc')->first();
        $fcmToken = FirebaseToken::where('user_id', $userId)->orderBy('created_at','desc')->get();
        if (sizeof($fcmToken) > 0) {
            $arDeviceToken = array();
            foreach($fcmToken as $fcm){
                $arDeviceToken[] = $fcm->token;
            }

            $message = CloudMessage::new()
                ->withNotification(Notification::create("Hai, 1 Pesanan Masuk! 🎉", "Cek Kiriman Saya dan segera verifikasi ya!"))
                ->withData([
                    'window' => 'subconsole',
                    'id' => $event->order->id,
                ]
            );

            $sendReport = $this->messaging->sendMulticast($message, $arDeviceToken);
            // // $message = CloudMessage::withTarget('token', $fcm['token'])
            // //     ->withNotification(Notification::create("Hai, 1 Pesanan Masuk! 🎉", "Cek Kiriman Saya dan segera verifikasi ya!"))
            // //     ->withData([
            // //         'window' => 'subconsole',
            // //         'id' => $event->order->id,
            // //     ]);
            //
            // $this->messaging->send($message);
            // $checkUser = FirebaseToken::where('token', $fcmToken->token)->orderBy('created_at','desc')->first();
            // if($checkUser->user_id == $userId){
            //     // echo $userId;
            //     $message = CloudMessage::withTarget('token', $fcmToken->token)
            //             ->withNotification(Notification::create("Hai, 1 Pesanan Masuk! 🎉", "Cek Kiriman Saya dan segera verifikasi ya!"))
            //             ->withData([
            //                 'window' => 'subconsole',
            //                 'id' => $event->order->id,
            //             ]);
            //
            //     $this->messaging->send($message);
            // }
        }
        //20210308 - TID : qyMBSbR5 - END
    }
}

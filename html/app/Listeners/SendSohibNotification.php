<?php

namespace App\Listeners;

use App\Events\SohibNotificationFromSubConsole;
use App\FirebaseToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
// implements ShouldQueue
class SendSohibNotification
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
     * @param  SohibNotificationFromSubConsole  $event
     * @return void
     */
    public function handle(SohibNotificationFromSubConsole $event)
    {
        $userId = $event->transanction->booking->user_id;
        // $fcmToken = FirebaseToken::where('user_id', $userId)->orderBy('updated_at','desc')->first();
        //
        // //20210215 - TID: 5PwoD6Je - START
        // if($fcmToken ==""){
        //     return;
        // }
        // //20210215 - TID: 5PwoD6Je - END
        //
        // $title = '';
        // $notificationMessage = '';
        //
        // if ($event->transanction->valid_status == 'accepted') {
        //     $title = 'Hai, Pesananmu sudah diterima!';
        //     $notificationMessage = 'Segera antarkan paket kamu ya!';
        // } elseif ($event->transanction->valid_status == 'rejected') {
        //     $title = 'Mohon maaf, Pesananmu ditolak! 🙁';
        //     $notificationMessage = 'Perbarui pesananmu ya!';
        // } else {
        //     $title = 'Hai, Pesananmu sudah diverifikasi! 📦';
        //     $notificationMessage = 'Paketmu akan segera diantarkan ke Drop Point dan kamu akan mendapatkan nomor resi';
        // }
        //
        // $message = CloudMessage::withTarget('token', $fcmToken->token)
        //     ->withNotification(Notification::create($title, $notificationMessage))
        //     ->withData([
        //         'window' => 'my-booking',
        //         'id' => $event->transanction->booking->id,
        //     ]);
        //
        // $this->messaging->send($message);

        $fcmToken = FirebaseToken::where('user_id', $userId)->orderBy('created_at','desc')->get();

        if(sizeof($fcmToken) > 0){
            $title = '';
            $notificationMessage = '';

            if ($event->transanction->valid_status == 'accepted') {
                $title = 'Hai, Pesananmu sudah diterima!';
                $notificationMessage = 'Segera antarkan paket kamu ya!';
            } elseif ($event->transanction->valid_status == 'rejected') {
                $title = 'Mohon maaf, Pesananmu ditolak! 🙁';
                $notificationMessage = 'Perbarui pesananmu ya!';
            } else {
                $title = 'Hai, Pesananmu sudah diverifikasi! 📦';
                $notificationMessage = 'Paketmu akan segera diantarkan ke Drop Point dan kamu akan mendapatkan nomor resi';
            }

            $arDeviceToken = array();
            foreach($fcmToken as $fcm){
                $arDeviceToken[] = $fcm->token;
            }

            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $notificationMessage))
                ->withData([
                    'window' => 'subconsole',
                    'id' => $event->transanction->booking->id,
                ]
            );

            $sendReport = $this->messaging->sendMulticast($message, $arDeviceToken);
        }
    }
}

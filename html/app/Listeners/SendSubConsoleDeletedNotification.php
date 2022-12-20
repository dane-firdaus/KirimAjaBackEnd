<?php

namespace App\Listeners;

use App\Events\SubConsoleOrderDeleted;
use App\FirebaseToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendSubConsoleDeletedNotification
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
     * @param  SubConsoleOrderDeleted  $event
     * @return void
     */
    public function handle(SubConsoleOrderDeleted $event)
    {
        // REMARK - BAYU - FIX BUG EDIT DELIVERY POINT
        $userId = $event->order->booking->user_id;
        // REMARK - BAYU - FIX BUG EDIT DELIVERY POINT
        $fcmToken = FirebaseToken::where('user_id', $userId)->orderBy('updated_at','desc')->first();

        // REMARK - BAYU - FIX BUG EDIT DELIVERY POINT
        Log::info('subconsole canceled '.$event->order->booking->id);
        // REMARK - BAYU - FIX BUG EDIT DELIVERY POINT

        if (!is_null($fcmToken)) {
            $message = CloudMessage::withTarget('token', $fcmToken->token)
                    ->withNotification(Notification::create("Yaaah... Pesanan untuk Kamu telah dibatalkan 🙁", "Pesananmu dibatalkan oleh Sohib"))
                    ->withData([
                        'window' => 'subconsole',
                        // REMARK - BAYU - FIX BUG EDIT DELIVERY POINT
                        'id' => $event->order->booking->id,
                        // REMARK - BAYU - FIX BUG EDIT DELIVERY POINT
                    ]);

            $this->messaging->send($message);
        }
    }
}

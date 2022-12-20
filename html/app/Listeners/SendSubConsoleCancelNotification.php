<?php

namespace App\Listeners;

use App\Events\SubConsoleOrderCanceled;
use App\FirebaseToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendSubConsoleCancelNotification implements ShouldQueue
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
     * @param  object  $event
     * @return void
     */
    public function handle(SubConsoleOrderCanceled $event)
    {
        $userId = $event->order->user_id;
        $fcmToken = FirebaseToken::where('user_id', $userId)->orderBy('updated_at','desc')->first();
        
        Log::error('subconsole canceled '.$userId->order->id);
        
        if (!is_null($fcmToken)) {
            $message = CloudMessage::withTarget('token', $fcmToken->token)
                    ->withNotification(Notification::create("Yaaah... Pesanan untuk Kamu telah dibatalkan 🙁", "Pesananmu dibatalkan oleh Sohib"))
                    ->withData([
                        'window' => 'subconsole',
                        'id' => $event->order->id,
                    ]);
            
            $this->messaging->send($message);
        }
    }
}

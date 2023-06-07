<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class PushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $body;
    protected $mobile;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $body )
    {
        $this->to = $to;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->pushFireBase();
    }

    protected function pushFireBase(){
        $deviceToken = $this->to;
        $message = isset($this->body['title']) ? $this->body['title'] : 'Không có tiêu đề';
        $push_data = [
            'body' => $this->body['body'],
            'title' => isset($this->body['title']) ? $this->body['title'] : 'Không có tiêu đề',
            'content' => isset($this->body) ? $this->body['content'] : '',
            'target_type' => $this->body['target_type'],
            'target_value' => $this->body['target_value'],
        ];
        $serverKey = env('FIREBASE_API_KEY', 'AAAAQwONabw:APA91bFNFfEJQCj65e-271-2xa2p_PzUqrpnnSeFMkX6tDHn7YdB1a4aBXHYjMQRppgkTCNnz2aT12c6vO82Rk95Nsc1bOqmsRY4ATpCKernUvupXNMVx0nDNWrgl0SXiTStJ_oyt6LV');
        $url = 'https://fcm.googleapis.com/fcm/send ';

        $msg = array(
            'message' => $message,
            'data' => $push_data
        );
        $fields = array();
        $fields['data'] = $msg;
        if (is_array($deviceToken)) {
            $fields['registration_ids'] = $deviceToken;
        } else {
            $fields['to'] = $deviceToken;
        }
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $serverKey
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $resp = curl_exec($ch);
        if ($resp === false) {
            \Log::info(curl_error($ch));
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

        $resp = json_decode($resp, true);

        if ($resp) {
           // \Log::info('fire base to success');
           // \Log::info($resp);
            // Update database
        }
    }
}

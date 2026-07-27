<?php

namespace App\Console\Commands;

use App\Services\FcmService;
use Illuminate\Console\Command;

class SendTestFcm extends Command
{
    protected $signature = 'fcm:test {title=Test} {body=Hello World}';
    protected $description = 'Kirim notifikasi FCM uji coba ke fcm_token spesifik';

    public function handle(FcmService $fcmService)
    {
        // $token = $this->argument('token');
        $token = "c0D7NWQSSy25wcLX7ivXRb:APA91bG6giRsvrYj4rpIr_i3FmiUO9WIeV91GxH7O8rjO0Vyln_T1bIo83XWhf-_bpvWWnL0w4DIXcD_TiI6VBf61zqxZwhb5qvezyhY7yiyqnym8sbJB_E";
        $title = $this->argument('title');
        $body = $this->argument('body');

        $this->info("Mengirim push notif FCM...");
        $success = $fcmService->sendToToken($token, $title, $body, ['test_key' => 'test_val']);

        if ($success) {
            $this->info("Notifikasi FCM berhasil dikirim!");
        } else {
            $this->error("Gagal mengirim notifikasi FCM. Cek log error Laravel.");
        }
    }
}

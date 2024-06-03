<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService
{
    public function sendEmail($to, $subject, $message)
    {
//        Mail::raw($message, function ($mail) use ($to, $subject) {
//            $mail->to($to)
//                ->subject($subject);
//        });
        return true;
    }
}

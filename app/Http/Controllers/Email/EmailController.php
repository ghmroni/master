<?php

namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use App\Mail\MailSend;
use App\Mail\ResetPasswordMail;
use Exception;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function send_new_account($data)
    {
        try {
            $subject = 'Pembuatan Akun Baru';
            $template = 'email.new_account';
            $fromName = 'R2N';
            Mail::to($data['email'])->queue(new MailSend($data, $subject, $template, $fromName));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function send_email_forgot($data)
    {
        try {
            $subject = 'Reset Password HRIS';
            $template = 'email.reset_password';
            Mail::to($data['email'])->queue(new MailSend($data, $subject, $template));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function send_email_pemanggilan($data)
    {
        try {
            $subject = 'Undangan Psikotes & Interview - PT Grha Humanindo Manajemen (GHM)';
            $template = 'email.pemanggilan';
            $fromName = 'PT. GHM';
            Mail::to($data['email'])->queue(new MailSend($data, $subject, $template, $fromName));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

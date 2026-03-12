<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $code;

    // How many times to retry
    public $tries = 3;

    // Seconds to wait before retry
    public $backoff = 5;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dream Mulk - Email Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='background-color: #f4f4f7; padding: 30px; font-family: sans-serif;'>
                    <table align='center' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 500px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                        <tr>
                            <td style='padding: 40px 20px; text-align: center;'>
                                <h2 style='color: #1f2937; margin-bottom: 10px; font-size: 24px;'>Welcome to Dream Mulk</h2>
                                <p style='color: #6b7280; font-size: 16px;'>Use the code below to verify your email address.</p>

                                <div style='margin: 30px 0;'>
                                    <span style='display: inline-block; background-color: #f3f4f6; border: 2px dashed #303B97; color: #303B97; padding: 15px 30px; font-size: 32px; font-weight: bold; letter-spacing: 10px; border-radius: 8px;'>
                                        {$this->code}
                                    </span>
                                </div>

                                <p style='color: #ef4444; font-size: 13px; font-weight: bold;'>This code expires in 10 minutes.</p>

                                <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb;'>
                                    <p style='color: #9ca3af; font-size: 12px; line-height: 1.5;'>
                                        If you did not request this code, please ignore this email.<br>
                                        &copy; 2026 Dream Mulk. All rights reserved.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            "
        );
    }

    // Optional: Log if the email keeps failing after all retries
    public function failed($exception)
    {
        \Log::error("OTP Email failed to send to user after 3 tries: " . $exception->getMessage());
    }
}

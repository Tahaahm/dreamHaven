<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // ✅ THIS MAKES IT SEND IN BACKGROUND
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $code;

    // ✅ If Contabo fails, Laravel will automatically try 3 more times
    public $tries = 3;

    // ✅ Wait 5 seconds before trying again if it fails
    public $backoff = 5;

    /**
     * Create a new message instance.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dream Mulk - Email Verification Code', // ✅ Updated
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // ✅ Using htmlString so you don't need to create a separate Blade file.
        // This is much safer for spam filters than sending plain text.
        return new Content(
            htmlString: "
                <div style='font-family: Arial, sans-serif; text-align: center; padding: 20px; background-color: #f8f9fa; border-radius: 10px; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #1f2937;'>Welcome to Dream Mulk</h2> <p style='color: #4b5563; font-size: 16px;'>Your verification code is:</p>
                    <div style='background: #ffffff; padding: 15px 25px; border-radius: 8px; display: inline-block; margin: 15px 0; border: 2px solid #303B97;'>
                        <h1 style='color: #303B97; letter-spacing: 10px; margin: 0; font-size: 36px;'>{$this->code}</h1>
                    </div>
                    <p style='color: #dc2626; font-size: 14px;'>This code will expire in 10 minutes.</p>
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;' />
                    <p style='color: #9ca3af; font-size: 12px;'>If you didn't request this code, please ignore this email.</p>
                </div>
            "
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

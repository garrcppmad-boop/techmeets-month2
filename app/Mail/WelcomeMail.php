<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 登録が完了したユーザーを受け取る。
     */
    public function __construct(public User $user)
    {
    }

    /**
     * 件名などの封筒情報。
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【'.config('app.name').'】ご登録ありがとうございます',
        );
    }

    /**
     * 本文（Blade ビュー）。ビューには $user が渡る。
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    /**
     * 添付ファイルは無し。
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    /**
     * 会員登録完了（Registered イベント）を受け取り、
     * ウェルカムメールを送信する。
     *
     * このリスナーは Laravel のイベント自動検出により
     * Registered イベントに自動で紐付く（手動登録は不要）。
     */
    public function handle(Registered $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeMail($event->user));
    }
}

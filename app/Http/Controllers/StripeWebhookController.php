<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            // ペイロードが不正
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            // 署名が不正 → 偽のリクエストを拒否
            return response('Invalid signature', 400);
        }

        // イベントの種類に応じて処理を分岐
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;

            // 決済完了イベントを受信 → 決済ID（PaymentIntent ID）をログに記録
            Log::info('Stripe Webhook 受信: payment_intent.succeeded', [
                'payment_intent_id' => $paymentIntent->id,
                'amount'            => $paymentIntent->amount,
                'currency'          => $paymentIntent->currency,
            ]);

            $this->recordPurchase($paymentIntent);
        }

        return response('OK', 200);
    }

    /**
     * 決済完了した PaymentIntent を購入履歴として保存する。
     * Stripe は同じイベントを複数回送ることがあるため、
     * payment_intent の ID をキーに updateOrCreate で冪等に処理する。
     */
    private function recordPurchase(\Stripe\PaymentIntent $paymentIntent): void
    {
        $metadata = $paymentIntent->metadata ?? null;

        $purchase = Purchase::updateOrCreate(
            ['stripe_payment_intent_id' => $paymentIntent->id],
            [
                'user_id'        => $metadata?->user_id,
                'product_id'     => $metadata?->product_id,
                'amount'         => $paymentIntent->amount_received ?: $paymentIntent->amount,
                'currency'       => $paymentIntent->currency ?? 'jpy',
                'status'         => $paymentIntent->status,
                'customer_email' => $paymentIntent->receipt_email ?? $metadata?->email,
            ],
        );

        Log::info('購入履歴を保存しました', [
            'purchase_id'       => $purchase->id,
            'payment_intent_id' => $paymentIntent->id,
        ]);
    }
}
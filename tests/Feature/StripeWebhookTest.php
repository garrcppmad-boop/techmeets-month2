<?php

namespace Tests\Feature;

use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => $this->secret]);
    }

    /** Stripe 署名付きのリクエストヘッダーを生成する */
    private function signature(string $payload): string
    {
        $timestamp = time();
        $signed = hash_hmac('sha256', "{$timestamp}.{$payload}", $this->secret);

        return "t={$timestamp},v1={$signed}";
    }

    private function paymentIntentPayload(array $overrides = []): string
    {
        return json_encode([
            'id'   => 'evt_test_123',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => array_merge([
                'id'              => 'pi_test_123',
                'object'          => 'payment_intent',
                'amount'          => 3000,
                'amount_received' => 3000,
                'currency'        => 'jpy',
                'status'          => 'succeeded',
                'receipt_email'   => 'buyer@example.com',
                'metadata'        => ['user_id' => null, 'product_id' => null],
            ], $overrides)],
        ]);
    }

    public function test_決済完了イベントで購入履歴が保存される(): void
    {
        $payload = $this->paymentIntentPayload();

        $response = $this->call(
            'POST',
            '/api/webhook/stripe',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => $this->signature($payload), 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertOk();
        $this->assertDatabaseHas('purchases', [
            'stripe_payment_intent_id' => 'pi_test_123',
            'amount'                   => 3000,
            'currency'                 => 'jpy',
            'status'                   => 'succeeded',
            'customer_email'           => 'buyer@example.com',
        ]);
    }

    public function test_決済完了イベントで決済IDがログに記録される(): void
    {
        Log::spy();

        $payload = $this->paymentIntentPayload();

        $this->call(
            'POST',
            '/api/webhook/stripe',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => $this->signature($payload), 'CONTENT_TYPE' => 'application/json'],
            $payload,
        )->assertOk();

        Log::shouldHaveReceived('info')
            ->withArgs(function (string $message, array $context) {
                return $message === 'Stripe Webhook 受信: payment_intent.succeeded'
                    && $context['payment_intent_id'] === 'pi_test_123';
            })
            ->once();
    }

    public function test_同じイベントを二回受信しても履歴は重複しない(): void
    {
        $payload = $this->paymentIntentPayload();
        $headers = ['HTTP_Stripe-Signature' => $this->signature($payload), 'CONTENT_TYPE' => 'application/json'];

        $this->call('POST', '/api/webhook/stripe', [], [], [], $headers, $payload)->assertOk();
        $this->call('POST', '/api/webhook/stripe', [], [], [], $headers, $payload)->assertOk();

        $this->assertSame(1, Purchase::where('stripe_payment_intent_id', 'pi_test_123')->count());
    }

    public function test_署名が不正な場合は400を返し履歴を保存しない(): void
    {
        $payload = $this->paymentIntentPayload();

        $response = $this->call(
            'POST',
            '/api/webhook/stripe',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => 't=1,v1=invalid', 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(400);
        $this->assertDatabaseCount('purchases', 0);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    /**
     * 購入可能な商品一覧（決済の入り口）。
     */
    public function index()
    {
        $products = Product::latest()->paginate(12);

        return view('checkout.index', compact('products'));
    }

    /**
     * 指定商品の Stripe Checkout セッションを作成し、Stripe の決済ページへリダイレクトする。
     */
    public function checkout(Request $request, Product $product)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $user = $request->user();

        // Webフック（payment_intent.succeeded）で購入履歴に紐付けるための情報
        $metadata = [
            'user_id'    => (string) $user->id,
            'product_id' => (string) $product->id,
        ];

        $productData = ['name' => $product->name];
        if (filled($product->description)) {
            $productData['description'] = Str::limit($product->description, 300);
        }

        try {
            $session = Session::create([
                'mode'           => 'payment',
                'customer_email' => $user->email,
                'line_items'     => [[
                    'quantity'   => 1,
                    'price_data' => [
                        'currency'     => 'jpy',
                        'unit_amount'  => $product->price,
                        'product_data' => $productData,
                    ],
                ]],
                'payment_intent_data' => ['metadata' => $metadata],
                'metadata'            => $metadata,
                'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('checkout.cancel'),
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Checkout セッション作成に失敗', [
                'product_id' => $product->id,
                'message'    => $e->getMessage(),
            ]);

            return back()->with('error', '決済ページの作成に失敗しました。時間をおいて再度お試しください。');
        }

        Log::info('Stripe Checkout セッション作成', [
            'session_id' => $session->id,
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        return redirect()->away($session->url);
    }

    /**
     * 決済成功後の戻り先。実際の購入記録は Webフック側で行う。
     */
    public function success()
    {
        return view('checkout.success');
    }

    /**
     * 決済キャンセル時の戻り先。
     */
    public function cancel()
    {
        return view('checkout.cancel');
    }
}

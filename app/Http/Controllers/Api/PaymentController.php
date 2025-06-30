<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request)
    {
        try {
            $user = $request->user();

            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY'); // Ganti dengan server key kamu
            \Midtrans\Config::$isProduction = false;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => 'ORDER-' . time(),
                    'gross_amount' => 25000,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            return response()->json(['token' => $snapToken]);
        } catch (\Throwable $e) {
            \Log::error('MIDTRANS ERROR: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal membuat Snap Token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function midtransCallback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signature = hash(
            "sha512",
            $request->order_id .
                $request->status_code .
                $request->gross_amount .
                $serverKey
        );

        if ($signature !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        if ($request->transaction_status === 'settlement') {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->status = 'premium';
                $user->save();
            }
        }

        return response()->json(['message' => 'Callback processed']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request)
    {
        try {
            $user = $request->user();

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'amount' => 50000,
                'order_type' => 'premium', // bisa ganti jadi topup, dsb
            ]);

            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = false;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => $order->amount,
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

                // Buat order (kalau belum)
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 'completed',
                    'amount' => (int) $request->gross_amount,
                    'order_type' => 'upgrade',
                ]);

                // Buat invoice
                $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                $invoice = Invoice::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $order->amount,
                    'pdf_url' => '', // sementara kosong
                ]);

                // Generate PDF dan simpan ke storage
                $pdf = Pdf::loadView('invoice', [
                    'invoice' => $invoice,
                    'user' => $user
                ]);

                $pdfPath = "invoices/{$invoiceNumber}.pdf";
                Storage::disk('public')->put($pdfPath, $pdf->output());

                // Update URL PDF
                $invoice->pdf_url = asset('storage/' . $pdfPath);
                $invoice->save();
            }
        }

        return response()->json(['message' => 'Callback processed']);
    }
}

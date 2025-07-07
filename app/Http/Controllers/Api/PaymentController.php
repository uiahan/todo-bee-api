<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Str;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request)
    {
        try {
            $user = $request->user();

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'amount' => 125000,
                'order_type' => 'premium',
            ]);

            $snapOrderId = 'ORDER-' . $order->id . '-' . time();
            $order->update(['snap_order_id' => $snapOrderId]);

            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = false;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $snapOrderId,
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
            Log::error('MIDTRANS ERROR: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal membuat Snap Token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function midtransCallback(Request $request)
    {
        try {
            Log::info('🔥 CALLBACK MASUK', $request->all());

            $serverKey = env('MIDTRANS_SERVER_KEY');
            $signature = hash(
                "sha512",
                $request->order_id .
                    $request->status_code .
                    $request->gross_amount .
                    $serverKey
            );

            if ($signature !== $request->signature_key) {
                Log::error('🚫 Signature tidak valid');
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // Temukan Order berdasarkan snap_order_id
            $order = Order::where('snap_order_id', $request->order_id)->first();

            if (!$order) {
                Log::error("❌ Order tidak ditemukan: " . $request->order_id);
                return response()->json(['message' => 'Order not found'], 404);
            }

            $user = $order->user;

            if ($request->transaction_status === 'settlement') {
                Log::info("✅ Pembayaran sukses untuk user ID {$user->id}, update ke premium");

                $user->update(['status' => 'premium']);

                $order->update([
                    'status' => 'completed',
                    'amount' => (int) $request->gross_amount,
                    'order_type' => 'upgrade',
                ]);

                $order->payment()->create([
                    'status' => 'completed',
                    'paid_at' => now()->format('H:i:s'),
                ]);

                $invoiceNumber = 'INV-' . strtoupper(\Illuminate\Support\Str::random(8));
                $invoice = Invoice::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $order->amount,
                    'pdf_url' => '',
                ]);

                $pdf = Pdf::loadView('invoice', compact('invoice', 'user'));
                $pdfPath = "invoices/{$invoiceNumber}.pdf";
                Storage::disk('public')->put($pdfPath, $pdf->output());

                $invoice->update([
                    'pdf_url' => asset("storage/{$pdfPath}"),
                ]);

                Log::info("🧾 Invoice berhasil dibuat: {$invoice->invoice_number}");
            }

            return response()->json(['message' => 'Callback processed']);
        } catch (\Throwable $e) {
            Log::error('MIDTRANS CALLBACK ERROR: ' . $e->getMessage());
            return response()->json(['message' => 'Callback error'], 500);
        }
    }


    public function downloadInvoice($snap_order_id)
    {
        $order = Order::where('snap_order_id', $snap_order_id)->first();

        if (!$order) {
            Log::error("Order dengan snap_order_id {$snap_order_id} tidak ditemukan.");
            abort(404, 'Order tidak ditemukan');
        }

        $invoice = Invoice::where('order_id', $order->id)->first();

        if (!$invoice) {
            Log::error("Invoice untuk order_id {$order->id} tidak ditemukan.");
            abort(404, 'Invoice tidak ditemukan');
        }

        $user = $invoice->user;

        $pdf = PDF::loadView('invoice', compact('invoice', 'user'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}

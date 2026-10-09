<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Rental;
use App\Notifications\RentalStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi SDK Midtrans
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized  = config('midtrans.is_sanitized', true);
        Config::$is3ds        = config('midtrans.is_3ds', true);
    }

    /**
     * Generate Snap Token Midtrans untuk checkout pembayaran sewa.
     * Endpoint: POST /api/payments/snap-token
     */
    public function generateSnapToken(Request $request): JsonResponse
    {
        $request->validate([
            'rental_id' => ['required', 'exists:rentals,id'],
        ]);

        $user = $request->user();
        $rental = Rental::with(['camera', 'payment'])->find($request->rental_id);

        // Authorization: Hanya customer pemilik pesanan yang boleh generate Snap Token
        if ($rental->user_id !== $user->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses ke transaksi ini.',
            ], 403);
        }

        // Cek jika status rental bukan pending_payment
        if ($rental->status !== 'pending_payment') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Transaksi ini sudah dibayar atau telah dibatalkan.',
            ], 400);
        }

        // Jika payment sudah ada dan snap_token masih berlaku, kembalikan token yang ada
        if ($rental->payment && $rental->payment->snap_token) {
            return response()->json([
                'status'     => 'success',
                'snap_token' => $rental->payment->snap_token,
                'order_id'   => $rental->payment->order_id,
            ], 200);
        }

        // Generate Order ID Unik (Contoh: RENT-12345-1712345678)
        $orderId = 'RENT-' . $rental->id . '-' . time();

        // Parameter Payload Midtrans Snap API
        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $rental->total_price,
            ],
            'item_details' => [
                [
                    'id'       => 'CAM-' . $rental->camera_id,
                    'price'    => (int) ($rental->total_price / $rental->quantity),
                    'quantity' => $rental->quantity,
                    'name'     => substr($rental->camera->name, 0, 50),
                ]
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '08123456789',
            ],
        ];

        try {
            // Panggil API Midtrans Snap
            $snapToken = Snap::getSnapToken($params);

            // Simpan data payment di database
            $payment = Payment::create([
                'rental_id'      => $rental->id,
                'order_id'       => $orderId,
                'snap_token'     => $snapToken,
                'gross_amount'   => $rental->total_price,
                'payment_status' => 'pending',
            ]);

            return response()->json([
                'status'     => 'success',
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat sesi pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook Callback IPN (Instant Payment Notification) Midtrans.
     * Endpoint Public: POST /api/payments/midtrans-notification
     */
    public function handleNotification(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received:', $payload);

        $orderId     = $payload['order_id'] ?? null;
        $statusCode  = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signature   = $payload['signature_key'] ?? null;
        $txStatus    = $payload['transaction_status'] ?? null;
        $type        = $payload['payment_type'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        $serverKey   = config('midtrans.server_key');

        // 🔴 CRITICAL SECURITY #2: Verifikasi Signature SHA512
        $inputSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signature !== $inputSignature) {
            Log::warning("Midtrans Webhook Invalid Signature! Order ID: {$orderId}");

            return response()->json([
                'status'  => 'error',
                'message' => 'Signature key tidak valid.',
            ], 403);
        }

        // Cari record payment berdasarkan order_id
        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order ID tidak ditemukan.',
            ], 404);
        }

        $rental = $payment->rental;

        // DB Transaction untuk mengamankan perubahan status payment & rental
        DB::transaction(function () use ($payment, $rental, $txStatus, $type, $fraudStatus) {
            $paymentStatus = 'pending';
            $rentalStatus  = $rental->status;

            if ($txStatus == 'capture') {
                if ($type == 'credit_card') {
                    $paymentStatus = ($fraudStatus == 'challenge') ? 'pending' : 'settlement';
                }
            } elseif ($txStatus == 'settlement') {
                $paymentStatus = 'settlement';
            } elseif ($txStatus == 'pending') {
                $paymentStatus = 'pending';
            } elseif (in_array($txStatus, ['deny', 'expire', 'cancel'])) {
                $paymentStatus = $txStatus;
            }

            // Jika pembayaran LUNAS (settlement)
            if ($paymentStatus === 'settlement') {
                $rentalStatus = 'paid';
            } elseif (in_array($paymentStatus, ['deny', 'expire', 'cancel'])) {
                $rentalStatus = 'cancelled';
            }

            // Update status Payment
            $payment->update([
                'payment_type'   => $type,
                'payment_status' => $paymentStatus,
            ]);

            // Update status Rental jika ada perubahan
            if ($rental->status !== $rentalStatus) {
                $rental->update(['status' => $rentalStatus]);

                // Kirim In-App Notification jika pembayaran lunas / batal
                if ($rental->user) {
                    $actionType = ($rentalStatus === 'paid') ? 'paid' : 'cancelled';
                    $rental->user->notify(new RentalStatusNotification($rental, $actionType));
                }
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi pembayaran berhasil diproses.',
        ], 200);
    }
}
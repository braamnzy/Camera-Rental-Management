<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Resources\RentalResource;
use App\Models\Camera;
use App\Models\Rental;
use App\Notifications\RentalStatusNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RentalController extends Controller
{
    /**
     * Menampilkan daftar transaksi penyewaan.
     * Customer: Hanya transaksi miliknya sendiri.
     * Admin: Seluruh transaksi penyewaan.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Rental::with(['user:id,name,email,phone', 'camera', 'payment']);

        // Otorisasi: Customer hanya melihat transaksi miliknya sendiri
        if ($user->isCustomer()) {
            $query->where('user_id', $user->id);
        }

        // Filter berdasarkan status rental jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rentals = $query->latest()->paginate($request->get('per_page', 10));

        return RentalResource::collection($rentals);
    }

    /**
     * Menampilkan detail satu transaksi rental.
     */
    public function show(Request $request, string $id): RentalResource|JsonResponse
    {
        $user = $request->user();
        $rental = Rental::with(['user', 'camera', 'payment'])->find($id);

        if (!$rental) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Transaksi penyewaan tidak ditemukan.',
            ], 404);
        }

        // Otorisasi: Customer tidak boleh mengakses transaksi milik orang lain
        if ($user->isCustomer() && $rental->user_id !== $user->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses ke transaksi ini.',
            ], 403);
        }

        return new RentalResource($rental);
    }

    /**
     * Membuat transaksi booking baru oleh Customer.
     * Menerapkan DB Transaction + lockForUpdate() untuk mencegah Race Condition / Overbooking.
     */
    public function store(StoreRentalRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $quantity  = $validated['quantity'] ?? 1;

        // DB Transaction dengan Lock Baris (lockForUpdate)
        $rental = DB::transaction(function () use ($request, $validated, $quantity) {
            // 1. Lock baris kamera di database agar tidak dibaca/ditulis transaksi concurrent lain
            $camera = Camera::where('id', $validated['camera_id'])
                ->lockForUpdate()
                ->first();

            if ($camera->status === 'maintenance') {
                throw ValidationException::withMessages([
                    'camera_id' => ['Kamera ini sedang dalam pemeliharaan (maintenance).'],
                ]);
            }

            // 2. Hitung jumlah unit yang sedang terpakai/direservasi pada rentang tanggal terpilih
            $activeUnitsRented = Rental::where('camera_id', $camera->id)
                ->whereIn('status', ['paid', 'picked_up', 'pending_payment'])
                ->where(function ($q) use ($validated) {
                    $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhere(function ($sub) use ($validated) {
                            $sub->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                        });
                })
                ->sum('quantity');

            // 3. Pengecekan sisa ketersediaan stok
            if (($activeUnitsRented + $quantity) > $camera->stock) {
                $availableStock = max(0, $camera->stock - $activeUnitsRented);
                throw ValidationException::withMessages([
                    'stock' => ["Stok kamera tidak mencukupi untuk rentang tanggal tersebut. Stok tersisa: {$availableStock} unit."],
                ]);
            }

            // 4. Kalkulasi Durasi & Total Harga
            $startDate  = Carbon::parse($validated['start_date']);
            $endDate    = Carbon::parse($validated['end_date']);
            $totalDays  = max(1, $startDate->diffInDays($endDate) + 1);
            $totalPrice = $totalDays * $camera->daily_rate * $quantity;

            // 5. Simpan Record Rental
            $newRental = Rental::create([
                'user_id'     => $request->user()->id,
                'camera_id'   => $camera->id,
                'start_date'  => $validated['start_date'],
                'end_date'    => $validated['end_date'],
                'pickup_date'   => $validated['pickup_date'],
                'pickup_time'   => $validated['pickup_time'],
                'pickup_method' => $validated['pickup_method'],
                'pickup_notes'  => $validated['pickup_notes'] ?? null,
                'total_days'  => $totalDays,
                'quantity'    => $quantity,
                'total_price' => $totalPrice,
                'status'      => 'pending_payment',
            ]);

            return $newRental;
        });

        // 6. Kirim In-App Notification ke Customer
        $request->user()->notify(new RentalStatusNotification($rental->load('camera'), 'created'));

        return (new RentalResource($rental->load(['camera', 'user'])))
            ->additional(['message' => 'Booking berhasil dibuat. Silakan selesaikan pembayaran.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Memperbarui status transaksi penyewaan [Khusus Admin].
     * Status yang diizinkan: picked_up, returned, cancelled
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['picked_up', 'returned', 'cancelled'])],
        ]);

        $rental = Rental::with(['camera', 'user'])->find($id);

        if (!$rental) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Transaksi penyewaan tidak ditemukan.',
            ], 404);
        }

        $oldStatus = $rental->status;
        $newStatus = $validated['status'];

        $rental->status = $newStatus;
        $rental->save();

        // Trigger In-App Notification ke Customer saat status diperbarui oleh Admin
        if ($rental->user) {
            $rental->user->notify(new RentalStatusNotification($rental, $newStatus));
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Status rental berhasil diperbarui dari '{$oldStatus}' menjadi '{$newStatus}'.",
            'data'    => new RentalResource($rental),
        ], 200);
    }

    /**
     * Rekap Jadwal Sewa Kamera untuk Dashboard Admin Monitoring Schedule.
     * Endpoint: GET /api/admin/rentals/schedule
     */
    public function schedule(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::today()->toDateString());
        $endDate   = $request->get('end_date', Carbon::today()->addDays(14)->toDateString());

        // Transaksi aktif dalam rentang tanggal pilihan
        $rentals = Rental::with(['user:id,name,phone', 'camera:id,name,brand,stock'])
            ->whereIn('status', ['paid', 'picked_up', 'pending_payment'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->get();

        // Peringatan Keterlambatan Pengembalian Unit (Overdue)
        $overdueRentals = Rental::with(['user:id,name,phone', 'camera:id,name'])
            ->where('status', 'picked_up')
            ->where('end_date', '<', Carbon::today()->toDateString())
            ->get();

        return response()->json([
            'status'          => 'success',
            'filter_period'   => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
            'overdue_count'   => $overdueRentals->count(),
            'overdue_rentals' => RentalResource::collection($overdueRentals),
            'data'            => RentalResource::collection($rentals),
        ], 200);
    }
}

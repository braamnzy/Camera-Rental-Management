<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi milik pengguna yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Mengambil seluruh notifikasi pengguna (termasuk yang sudah/belum dibaca)
        $notifications = $user->notifications()->paginate(15);

        // Menghitung jumlah notifikasi yang belum dibaca (unread count)
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'status'       => 'success',
            'unread_count' => $unreadCount,
            'data'         => $notifications,
        ], 200);
    }

    /**
     * Menandai notifikasi spesifik sebagai telah dibaca (read_at = now()).
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        // Cari notifikasi spesifik milik pengguna yang sedang login
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        // Tandai notifikasi sebagai telah dibaca jika belum
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi berhasil ditandai sebagai telah dibaca.',
        ], 200);
    }

    /**
     * Menandai seluruh notifikasi milik pengguna sebagai telah dibaca sekaligus.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        // Tandai semua unread notification menjadi read
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'status'  => 'success',
            'message' => 'Semua notifikasi berhasil ditandai sebagai telah dibaca.',
        ], 200);
    }
}
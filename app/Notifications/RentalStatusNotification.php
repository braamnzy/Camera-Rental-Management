<?php

namespace App\Notifications;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RentalStatusNotification extends Notification
{
    use Queueable;

    public Rental $rental;
    public string $actionStatus;

    /**
     * Membuat instance notifikasi baru.
     *
     * @param Rental $rental Instance transaksi rental yang mengalami perubahan status
     * @param string $actionStatus Jenis/kategori perubahan status (e.g., 'created', 'paid', 'picked_up', 'returned', 'cancelled')
     */
    public function __construct(Rental $rental, string$actionStatus = 'updated')
    {
        $this->rental =$rental;
        $this->actionStatus =$actionStatus;
    }

    /**
     * Menentukan kanal pengiriman notifikasi.
     * Menggunakan kanal 'database' untuk In-App Notification.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Format data yang disimpan ke dalam kolom `data` pada tabel `notifications`.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $cameraName = $this->rental->camera ? $this->rental->camera->name : 'Kamera';

        [$title, $message] = match ($this->actionStatus) {
            'created' => [
                'Pesanan Sewa Dibuat',
                "Pesanan sewa untuk unit {$cameraName} berhasil dibuat. Silakan selesaikan pembayaran."
            ],
            'paid' => [
                'Pembayaran Diterima',
                "Pembayaran sewa {$cameraName} berhasil diproses. Status pesanan: Lunas (Paid)."
            ],
            'picked_up' => [
                'Unit Kamera Diambil',
                "Unit {$cameraName} telah diserahkan. Selamat menggunakan!"
            ],
            'returned' => [
                'Penyewaan Selesai',
                "Unit {$cameraName} telah dikembalikan. Terima kasih telah menyewa di CamRent!"
            ],
            'cancelled' => [
                'Pesanan Dibatalkan',
                "Pesanan sewa untuk unit {$cameraName} telah dibatalkan."
            ],
            default => [
                'Pembaruan Status Sewa',
                "Status penyewaan {$cameraName} telah diperbarui menjadi '{$this->rental->status}'."
            ],
        };

        return [
            'rental_id'  => $this->rental->id,
            'camera_name'=> $cameraName,
            'status'     => $this->rental->status,
            'title'      => $title,
            'message'    => $message,
            'url'        => '/my-rentals',
        ];
    }
}
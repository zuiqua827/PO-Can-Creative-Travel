<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $trip = $this->order->trip;

        return (new MailMessage)
            ->subject("Pesanan Tiket Dibuat — {$this->order->order_code} [CAN Travel]")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pesanan tiket bus Anda untuk rute {$trip->route->origin} menuju {$trip->route->destination} berhasil dibuat.")
            ->line("Kode Pemesanan: {$this->order->order_code}")
            ->line("Total Pembayaran: {$this->order->formatted_total}")
            ->line("Batas Waktu Pembayaran: {$this->order->expires_at?->format('d M Y, H:i')} WIB")
            ->action('Selesaikan Pembayaran Sekarang', route('booking.payment', $this->order))
            ->line('Harap selesaikan pembayaran sebelum batas waktu berakhir agar kursi Anda tidak dilepaskan kembali.')
            ->salutation('Salam hangat, Tim CAN Travel');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'amount' => $this->order->total_amount,
            'status' => $this->order->status,
        ];
    }
}

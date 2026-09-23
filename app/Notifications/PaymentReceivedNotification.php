<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
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
            ->subject("Pembayaran Terverifikasi — E-Tiket {$this->order->order_code} Siap! [CAN Travel]")
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Kabar gembira! Pembayaran untuk pesanan tiket bus Anda telah berhasil diverifikasi secara sistem.')
            ->line("Kode Pemesanan: {$this->order->order_code}")
            ->line("Rute: {$trip->route->origin} → {$trip->route->destination}")
            ->line("Jadwal Berangkat: {$trip->departure_at->format('d M Y, H:i')} WIB")
            ->line("Total Lunas: {$this->order->formatted_total}")
            ->action('Lihat E-Tiket & Boarding Pass', route('orders.show', $this->order))
            ->line('E-Tiket resmi Anda dapat langsung ditunjukkan kepada petugas boarding CAN Travel di lokasi keberangkatan.')
            ->salutation('Terima kasih telah mempercayai perjalanan Anda bersama CAN Travel');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'amount' => $this->order->total_amount,
            'paid_at' => $this->order->payment?->paid_at,
        ];
    }
}

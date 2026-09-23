<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pesanan Dibatalkan — {$this->order->order_code} [CAN Travel]")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pesanan tiket bus dengan kode {$this->order->order_code} telah dibatalkan.")
            ->line('Alokasi kursi Anda telah dilepaskan kembali ke sistem tiket.')
            ->action('Cari Jadwal Baru', route('trips.index'))
            ->line('Jika pembatalan ini tidak dilakukan oleh Anda, silakan hubungi tim customer service kami.')
            ->salutation('Salam, Tim CAN Travel');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'status' => 'cancelled',
        ];
    }
}

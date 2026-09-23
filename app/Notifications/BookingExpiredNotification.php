<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingExpiredNotification extends Notification
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
            ->subject("Batas Waktu Pembayaran Habis — {$this->order->order_code} [CAN Travel]")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Batas waktu pembayaran 2 jam untuk pesanan {$this->order->order_code} telah terlewati.")
            ->line('Pesanan Anda telah otomatis kedaluwarsa dan kursi yang sebelumnya direservasi telah dilepaskan kembali.')
            ->action('Pesan Jadwal Baru', route('trips.index'))
            ->line('Anda dapat melakukan pemesanan ulang kapan saja melalui portal CAN Travel.')
            ->salutation('Salam, Tim CAN Travel');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'status' => 'expired',
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Transaction $transaction)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation for the database channel.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'transaction_id'   => $this->transaction->id,
            'type'             => $this->transaction->type,
            'source'           => $this->transaction->source,
            'status'           => $this->transaction->status,
            'amount'           => $this->transaction->amount,
            'description'      => $this->transaction->description,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'created_by'       => $this->transaction->user->name ?? 'Unknown',
            'message'          => sprintf(
                'New %s transaction: %s — %s',
                strtoupper($this->transaction->type),
                $this->transaction->description,
                $this->transaction->formattedAmount()
            ),
        ];
    }
}

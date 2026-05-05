<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'transaction_date',
        'type',
        'source',
        'status',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'integer',
    ];

    /**
     * The user who created this transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine the Tailwind color class for this transaction row.
     */
    public function colorClass(): string
    {
        if ($this->type === 'in') {
            return 'text-green-500';
        }

        if (
            $this->type === 'out' &&
            $this->source === 'personal' &&
            $this->status === 'pending'
        ) {
            return 'text-orange-500';
        }

        // type=out with source=contract OR status=settled
        return 'text-red-500';
    }

    /**
     * Return a formatted Rupiah amount string.
     */
    public function formattedAmount(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}

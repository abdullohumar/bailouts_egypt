<?php

use App\Models\Transaction;
use App\Models\User;
use App\Notifications\TransactionCreated;
use Livewire\Volt\Component;

new class extends Component {
    public string $description = '';
    public string $amount      = '';
    public string $transaction_date = '';
    public string $type   = 'out';
    public string $source = 'contract';
    public string $status = 'settled'; // contract default = settled
    public string $successMessage = '';

    public function mount(): void
    {
        $this->transaction_date = now()->toDateString();
    }

    /**
     * Auto-adjust status whenever source changes:
     * - personal → always pending (awaiting admin reimbursement)
     * - contract → default settled
     */
    public function updatedSource(string $value): void
    {
        $this->status = ($value === 'personal') ? 'pending' : 'settled';
    }

    public function save(): void
    {
        $this->validate([
            'description'      => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'type'             => ['required', 'in:in,out'],
            'source'           => ['required', 'in:contract,personal'],
            'status'           => ['required', 'in:pending,settled'],
        ]);

        $transaction = Transaction::create([
            'user_id'          => auth()->id(),
            'description'      => $this->description,
            'amount'           => (int) $this->amount,
            'transaction_date' => $this->transaction_date,
            'type'             => $this->type,
            'source'           => $this->source,
            'status'           => $this->status,
        ]);

        // Notify ALL users for transparency
        $transaction->load('user');
        User::all()->each->notify(new TransactionCreated($transaction));

        // Reset form fields but keep defaults
        $this->reset(['description', 'amount']);
        $this->transaction_date = now()->toDateString();
        $this->type   = 'out';
        $this->source = 'contract';
        $this->status = 'settled';
        $this->successMessage = 'Transaction saved! All members have been notified.';

        // Tell the TransactionList to refresh
        $this->dispatch('transaction-saved');
    }
}; ?>

<div x-data="{ open: false }">

    {{-- Mobile toggle button (hidden on desktop via md:hidden) --}}
    <div class="md:hidden mb-3">
        <button @click="open = !open"
            :class="open
                ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200'
                : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg'"
            class="w-full flex items-center justify-center gap-2 py-3 rounded-2xl font-semibold text-sm transition-all duration-150 active:scale-95"
        >
            <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span x-text="open ? 'Cancel' : '+ New Transaction'"></span>
        </button>
    </div>

    {{-- Mobile collapsible panel --}}
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="md:hidden bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 border border-gray-100 dark:border-gray-700 mb-3"
    >
        @include('livewire.partials.transaction-form-fields')
    </div>

    {{-- Desktop panel — ALWAYS visible, no Alpine x-show, pure CSS --}}
    <div class="hidden md:block bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 sm:p-6 border border-gray-100 dark:border-gray-700">
        @include('livewire.partials.transaction-form-fields')
    </div>

</div>
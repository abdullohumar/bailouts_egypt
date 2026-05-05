<?php

use App\Models\Transaction;
use App\Models\User;
use App\Notifications\TransactionCreated;
use Livewire\Volt\Component;

new class extends Component {
    public string $description = '';
    public string $amount = '';
    public string $transaction_date = '';
    public string $type = 'out';
    public string $source = 'contract';
    public string $status = 'pending';
    public string $successMessage = '';

    public function mount(): void
    {
        $this->transaction_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'description'      => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'type'             => ['required', 'in:in,out'],
            'source'           => ['required', 'in:contract,personal'],
            'status'           => ['required', 'in:pending,settled'],
        ];
    }

    public function save(): void
    {
        $this->validate();

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

        $this->reset(['description', 'amount', 'type', 'source', 'status']);
        $this->transaction_date = now()->toDateString();
        $this->successMessage = 'Transaction saved and all members notified!';

        // Refresh the transaction list
        $this->dispatch('transaction-saved');
    }
}; ?>

{{-- Collapsible form controlled by Alpine --}}
<div x-data="{ open: false }" class="relative">

    {{-- ── Mobile: Floating action button ── --}}
    <div class="md:hidden mb-4">
        <button
            @click="open = !open"
            class="w-full flex items-center justify-center gap-2 py-3 rounded-2xl font-semibold text-sm
                   bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white transition-all duration-150 shadow-lg"
        >
            <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span x-text="open ? 'Cancel' : 'Add Transaction'"></span>
        </button>
    </div>

    {{-- ── Form panel (always visible on desktop, collapsible on mobile) ── --}}
    <div
        x-show="open || window.innerWidth >= 768"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 sm:p-6 border border-gray-100 dark:border-gray-700 mb-2"
    >
        {{-- Header (desktop only — mobile has the FAB button) --}}
        <h2 class="hidden md:flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-100 mb-5">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>
            Add Transaction
        </h2>

        @if($successMessage)
            <div class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700
                        dark:bg-green-900/30 dark:border-green-700 dark:text-green-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ $successMessage }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-4">

            {{-- Description --}}
            <div>
                <x-input-label for="tf_description" :value="__('Description')" />
                <x-text-input id="tf_description" wire:model="description" type="text"
                    class="mt-1 block w-full text-base" placeholder="e.g. Client payment May" />
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>

            {{-- Amount + Date side by side on mobile too --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="tf_amount" :value="__('Amount (Rp)')" />
                    <x-text-input id="tf_amount" wire:model="amount" type="number" min="1"
                        class="mt-1 block w-full text-base" placeholder="5000000" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="tf_date" :value="__('Date')" />
                    <x-text-input id="tf_date" wire:model="transaction_date" type="date"
                        class="mt-1 block w-full text-base" />
                    <x-input-error :messages="$errors->get('transaction_date')" class="mt-1" />
                </div>
            </div>

            {{-- Type / Source / Status — segmented control style on mobile --}}
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <x-input-label for="tf_type" :value="__('Type')" />
                    <select id="tf_type" wire:model="type"
                        class="mt-1 block w-full rounded-xl border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                               px-2 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="in">Income</option>
                        <option value="out">Expense</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="tf_source" :value="__('Source')" />
                    <select id="tf_source" wire:model="source"
                        class="mt-1 block w-full rounded-xl border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                               px-2 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="contract">Contract</option>
                        <option value="personal">Personal</option>
                    </select>
                    <x-input-error :messages="$errors->get('source')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="tf_status" :value="__('Status')" />
                    <select id="tf_status" wire:model="status"
                        class="mt-1 block w-full rounded-xl border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                               px-2 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="pending">Pending</option>
                        <option value="settled">Settled</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
            </div>

            {{-- Submit --}}
            <div class="pt-1">
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm
                           bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white transition-all duration-150 shadow"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="save">Save Transaction</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        Saving…
                    </span>
                </button>
            </div>

        </form>
    </div>
</div>
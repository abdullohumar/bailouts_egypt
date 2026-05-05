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

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-6">
        Add Transaction
    </h2>

    @if($successMessage)
        <div class="mb-4 p-3 rounded bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200 text-sm">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">

        {{-- Description --}}
        <div>
            <x-input-label for="description" :value="__('Description')" />
            <x-text-input id="description" wire:model="description" type="text"
                class="mt-1 block w-full" placeholder="e.g. Client payment May" />
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        {{-- Amount --}}
        <div>
            <x-input-label for="amount" :value="__('Amount (Rp)')" />
            <x-text-input id="amount" wire:model="amount" type="number" min="1"
                class="mt-1 block w-full" placeholder="5000000" />
            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
        </div>

        {{-- Date --}}
        <div>
            <x-input-label for="transaction_date" :value="__('Transaction Date')" />
            <x-text-input id="transaction_date" wire:model="transaction_date" type="date"
                class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
        </div>

        {{-- Type + Source + Status in a grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- Type --}}
            <div>
                <x-input-label for="type" :value="__('Type')" />
                <select id="type" wire:model="type"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="in">Income (in)</option>
                    <option value="out">Expense (out)</option>
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>

            {{-- Source --}}
            <div>
                <x-input-label for="source" :value="__('Source')" />
                <select id="source" wire:model="source"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="contract">Contract</option>
                    <option value="personal">Personal</option>
                </select>
                <x-input-error :messages="$errors->get('source')" class="mt-2" />
            </div>

            {{-- Status --}}
            <div>
                <x-input-label for="status" :value="__('Status')" />
                <select id="status" wire:model="status"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="pending">Pending</option>
                    <option value="settled">Settled</option>
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>
                {{ __('Save Transaction') }}
            </x-primary-button>
            <span wire:loading wire:target="save" class="text-sm text-gray-500 dark:text-gray-400">
                Saving…
            </span>
        </div>

    </form>
</div>
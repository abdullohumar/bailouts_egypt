{{--
    Shared form fields partial — included by both the mobile and desktop panels
    in transaction-form.blade.php
--}}

{{-- Header --}}
<div class="flex items-center gap-3 mb-5">
    <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
    </div>
    <div>
        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">New Transaction</h2>
        <p class="text-xs text-gray-400 mt-0.5">
            Personal source → auto <span class="text-orange-500 font-semibold">Pending</span>
            (Admin will settle)
        </p>
    </div>
</div>

{{-- Success flash --}}
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
        <x-input-label for="tf_desc" value="Description" />
        <x-text-input id="tf_desc" wire:model="description" type="text"
            class="mt-1 block w-full" placeholder="e.g. Office supplies reimbursement" />
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    {{-- Amount + Date --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <x-input-label for="tf_amount" value="Amount (Rp)" />
            <x-text-input id="tf_amount" wire:model="amount" type="number" min="1"
                class="mt-1 block w-full" placeholder="5000000" />
            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="tf_date" value="Date" />
            <x-text-input id="tf_date" wire:model="transaction_date" type="date"
                class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('transaction_date')" class="mt-1" />
        </div>
    </div>

    {{-- Type + Source --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <x-input-label for="tf_type" value="Type" />
            <select id="tf_type" wire:model="type"
                class="mt-1 block w-full rounded-xl border border-gray-300 dark:border-gray-600
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                       px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="in">⬆️ Income</option>
                <option value="out">⬇️ Expense</option>
            </select>
            <x-input-error :messages="$errors->get('type')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="tf_source" value="Source" />
            <select id="tf_source" wire:model.live="source"
                class="mt-1 block w-full rounded-xl border border-gray-300 dark:border-gray-600
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                       px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="contract">🏢 Contract</option>
                <option value="personal">👤 Personal</option>
            </select>
            <x-input-error :messages="$errors->get('source')" class="mt-1" />
        </div>
    </div>

    {{-- Status (locked for personal, editable for contract) --}}
    <div>
        <x-input-label for="tf_status" value="Status" />
        @if($source === 'personal')
            <div class="mt-1 flex items-center gap-2 px-3 py-2.5 rounded-xl border border-yellow-300
                        bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                <span class="text-yellow-600 dark:text-yellow-400 text-sm font-semibold">⏳ Pending</span>
                <span class="text-xs text-yellow-500 ml-auto">Admin will settle this</span>
            </div>
            <input type="hidden" wire:model="status" />
        @else
            <select id="tf_status" wire:model="status"
                class="mt-1 block w-full rounded-xl border border-gray-300 dark:border-gray-600
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                       px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="settled">✅ Settled</option>
                <option value="pending">⏳ Pending</option>
            </select>
        @endif
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>

    {{-- Submit --}}
    <div class="pt-1">
        <button type="submit"
            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm
                   bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white transition-all shadow"
            wire:loading.attr="disabled"
            wire:target="save"
            wire:loading.class="opacity-70 cursor-not-allowed"
        >
            <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Transaction
            </span>
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

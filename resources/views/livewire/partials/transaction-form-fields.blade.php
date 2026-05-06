{{--
    Shared form fields partial — included by transaction-form.blade.php
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

{{-- ── Success flash ── --}}
@if($successMessage)
    <div class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700
                dark:bg-green-900/30 dark:border-green-700 dark:text-green-300 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ $successMessage }}
    </div>
@endif

{{-- ── Validation error summary banner ── --}}
@if($errors->any())
    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200
                dark:bg-red-900/20 dark:border-red-700 text-sm">
        <div class="flex items-center gap-2 text-red-700 dark:text-red-300 font-semibold mb-1.5">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Please fix the following errors:
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-red-600 dark:text-red-400">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form wire:submit="save" class="space-y-4">

    {{-- Description --}}
    <div>
        <x-input-label for="tf_desc" value="Description *" />
        <x-text-input
            id="tf_desc"
            wire:model="description"
            type="text"
            class="mt-1 block w-full {{ $errors->has('description') ? 'border-red-400 dark:border-red-500 ring-1 ring-red-400' : '' }}"
            placeholder="e.g. Office supplies reimbursement"
        />
        @error('description')
            <p class="mt-1 flex items-center gap-1 text-sm text-red-600 dark:text-red-400">
                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Amount + Date --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <x-input-label for="tf_amount" value="Amount (Rp) *" />
            <x-text-input
                id="tf_amount"
                wire:model="amount"
                type="number"
                min="1"
                class="mt-1 block w-full {{ $errors->has('amount') ? 'border-red-400 dark:border-red-500 ring-1 ring-red-400' : '' }}"
                placeholder="5000000"
            />
            @error('amount')
                <p class="mt-1 flex items-center gap-1 text-sm text-red-600 dark:text-red-400">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>
        <div>
            <x-input-label for="tf_date" value="Date *" />
            <x-text-input
                id="tf_date"
                wire:model="transaction_date"
                type="date"
                class="mt-1 block w-full {{ $errors->has('transaction_date') ? 'border-red-400 dark:border-red-500 ring-1 ring-red-400' : '' }}"
            />
            @error('transaction_date')
                <p class="mt-1 flex items-center gap-1 text-sm text-red-600 dark:text-red-400">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    {{-- Type + Source --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <x-input-label for="tf_type" value="Type *" />
            <select id="tf_type" wire:model="type"
                class="mt-1 block w-full rounded-xl border px-3 py-2.5 text-sm
                       focus:ring-2 focus:ring-indigo-500 focus:outline-none
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                       {{ $errors->has('type') ? 'border-red-400 dark:border-red-500 ring-1 ring-red-400' : 'border-gray-300 dark:border-gray-600' }}">
                <option value="in">⬆️ Income</option>
                <option value="out">⬇️ Expense</option>
            </select>
            @error('type')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <x-input-label for="tf_source" value="Source *" />
            <select id="tf_source" wire:model.live="source"
                class="mt-1 block w-full rounded-xl border px-3 py-2.5 text-sm
                       focus:ring-2 focus:ring-indigo-500 focus:outline-none
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                       {{ $errors->has('source') ? 'border-red-400 dark:border-red-500 ring-1 ring-red-400' : 'border-gray-300 dark:border-gray-600' }}">
                <option value="contract">🏢 Contract</option>
                <option value="personal">👤 Personal</option>
            </select>
            @error('source')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Status --}}
    <div>
        <x-input-label for="tf_status" value="Status *" />
        @if($source === 'personal')
            <div class="mt-1 flex items-center gap-2 px-3 py-2.5 rounded-xl border border-yellow-300
                        bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                <span class="text-yellow-600 dark:text-yellow-400 text-sm font-semibold">⏳ Pending</span>
                <span class="text-xs text-yellow-500 ml-auto">Admin will settle this</span>
            </div>
            <input type="hidden" wire:model="status" />
        @else
            <select id="tf_status" wire:model="status"
                class="mt-1 block w-full rounded-xl border px-3 py-2.5 text-sm
                       focus:ring-2 focus:ring-indigo-500 focus:outline-none
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                       {{ $errors->has('status') ? 'border-red-400 dark:border-red-500 ring-1 ring-red-400' : 'border-gray-300 dark:border-gray-600' }}">
                <option value="settled">✅ Settled</option>
                <option value="pending">⏳ Pending</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        @endif
    </div>

    {{-- Submit --}}
    <div class="pt-1">
        <button
            type="submit"
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

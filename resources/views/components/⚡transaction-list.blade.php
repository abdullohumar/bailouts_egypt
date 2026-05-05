<?php

use App\Models\Transaction;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filterType = '';

    public function with(): array
    {
        return [
            'transactions' => Transaction::with('user')
                ->when($this->search, fn ($q) => $q->where('description', 'like', '%' . $this->search . '%'))
                ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
                ->orderByDesc('transaction_date')
                ->paginate(15),
        ];
    }

    /**
     * Return the Tailwind color class for a transaction row.
     * Keeping the logic here as well as in the model for view flexibility.
     */
    public function colorClass(Transaction $tx): string
    {
        return $tx->colorClass();
    }
}; ?>

<div>
    {{-- ── Toolbar ── --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Search description…"
            class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
        />
        <select
            wire:model.live="filterType"
            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
        >
            <option value="">All Types</option>
            <option value="in">Income (in)</option>
            <option value="out">Expense (out)</option>
        </select>
    </div>

    {{-- ── Legend ── --}}
    <div class="flex gap-4 text-xs mb-4 text-gray-500 dark:text-gray-400">
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Income</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span> Personal Bailout (pending)</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Expense / Settled</span>
    </div>

    {{-- ── Table ── --}}
    <div class="overflow-x-auto rounded-lg shadow border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">By</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($transactions as $tx)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">
                            {{ $tx->transaction_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 {{ $tx->colorClass() }} font-medium">
                            {{ $tx->description }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $tx->user->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                                'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $tx->type === 'in',
                                'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'         => $tx->type === 'out',
                            ])>
                                {{ strtoupper($tx->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 capitalize">
                            {{ $tx->source }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize',
                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $tx->status === 'pending',
                                'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'         => $tx->status === 'settled',
                            ])>
                                {{ $tx->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $tx->colorClass() }} whitespace-nowrap">
                            {{ $tx->formattedAmount() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500 italic">
                            No transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    @if($transactions->hasPages())
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
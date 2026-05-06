<?php

use App\Models\Transaction;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search     = '';
    public string $filterType = '';

    protected $listeners = ['transaction-saved' => 'refresh'];

    /** Refresh the list every time a new transaction is saved */
    public function refresh(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'transactions' => Transaction::with('user')
                ->when($this->search,     fn ($q) => $q->where('description', 'like', '%' . $this->search . '%'))
                ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->paginate(15),
        ];
    }

    /**
     * Admin: mark a personal-pending transaction as settled.
     */
    public function settle(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $tx = Transaction::findOrFail($id);

        abort_unless(
            $tx->type === 'out' && $tx->source === 'personal' && $tx->status === 'pending',
            422,
            'Only pending personal transactions can be settled.'
        );

        $tx->update(['status' => 'settled']);
    }
}; ?>

<div>
    {{-- ── Toolbar ── --}}
    <div class="flex flex-col gap-3 mb-4 sm:flex-row">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                </svg>
            </span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search description…"
                class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm
                       focus:ring-2 focus:ring-indigo-500 focus:outline-none"/>
        </div>
        <select wire:model.live="filterType"
            class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700
                   text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm
                   focus:ring-2 focus:ring-indigo-500 focus:outline-none w-full sm:w-auto">
            <option value="">All Types</option>
            <option value="in">💚 Income</option>
            <option value="out">🔴 Expense</option>
        </select>
    </div>

    {{-- ── Color Legend ── --}}
    <div class="flex flex-wrap gap-3 text-xs mb-4 text-gray-500 dark:text-gray-400">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 shrink-0"></span><strong class="text-green-600 dark:text-green-400">Green</strong> = Income</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-500 shrink-0"></span><strong class="text-orange-600 dark:text-orange-400">Orange</strong> = Personal Bailout (awaiting reimbursement)</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></span><strong class="text-red-600 dark:text-red-400">Red</strong> = Contract Expense / Settled</span>
    </div>

    {{-- Loading indicator --}}
    <div wire:loading class="text-xs text-indigo-400 animate-pulse mb-2">Updating…</div>

    {{-- ══════════════════════════════════════════════
         MOBILE: Card list (md and below)
    ══════════════════════════════════════════════ --}}
    <div class="space-y-3 md:hidden">
        @forelse($transactions as $tx)
            @php $color = $tx->colorClass(); @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                {{-- Top row: date + amount --}}
                <div class="flex items-start justify-between gap-2 mb-2">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        {{ $tx->transaction_date->format('d M Y') }}
                    </span>
                    <span class="text-base font-bold whitespace-nowrap {{ $color }}">
                        {{ $tx->type === 'in' ? '+' : '-' }}{{ $tx->formattedAmount() }}
                    </span>
                </div>

                {{-- Description --}}
                <p class="text-sm font-semibold leading-snug mb-2 {{ $color }}">
                    {{ $tx->description }}
                </p>

                {{-- Meta badges --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        by <strong class="text-gray-600 dark:text-gray-300">{{ $tx->user->name ?? '—' }}</strong>
                    </span>

                    <span @class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $tx->type === 'in',
                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'         => $tx->type === 'out',
                    ])>{{ strtoupper($tx->type) }}</span>

                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize
                                 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">{{ $tx->source }}</span>

                    <span @class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize',
                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $tx->status === 'pending',
                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'         => $tx->status === 'settled',
                    ])>{{ $tx->status }}</span>

                    {{-- Settle button: Admin only, personal+pending --}}
                    @if(auth()->user()->isAdmin() && $tx->type === 'out' && $tx->source === 'personal' && $tx->status === 'pending')
                        <button
                            x-on:click="confirm('Mark this transaction as Settled?') && $wire.settle({{ $tx->id }})"
                            class="ml-auto inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold
                                   bg-indigo-600 hover:bg-indigo-700 text-white transition active:scale-95 shadow-sm"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Settle
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-gray-400 dark:text-gray-500 italic text-sm">
                No transactions found.
            </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════════════════
         DESKTOP: Full table (md and above)
    ══════════════════════════════════════════════ --}}
    <div class="hidden md:block overflow-x-auto rounded-2xl shadow border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">By</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($transactions as $tx)
                    @php $color = $tx->colorClass(); @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300">
                            {{ $tx->transaction_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 font-medium max-w-[200px] truncate {{ $color }}">
                            {{ $tx->description }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $tx->user->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span @class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                                'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $tx->type === 'in',
                                'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'         => $tx->type === 'out',
                            ])>{{ strtoupper($tx->type) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-gray-400 capitalize">
                            {{ $tx->source }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span @class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize',
                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $tx->status === 'pending',
                                'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'         => $tx->status === 'settled',
                            ])>{{ $tx->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold whitespace-nowrap {{ $color }}">
                            {{ $tx->type === 'in' ? '+' : '-' }}{{ $tx->formattedAmount() }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(auth()->user()->isAdmin() && $tx->type === 'out' && $tx->source === 'personal' && $tx->status === 'pending')
                                <button
                                    x-on:click="confirm('Mark this as Settled?') && $wire.settle({{ $tx->id }})"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold
                                           bg-indigo-600 hover:bg-indigo-700 text-white transition active:scale-95 shadow-sm
                                           disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Settle
                                </button>
                            @else
                                <span class="text-xs text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500 italic">
                            No transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
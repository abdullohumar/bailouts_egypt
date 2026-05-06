<?php

use App\Models\Transaction;
use Livewire\Volt\Component;

new class extends Component {

    public int  $month;
    public int  $year;

    public function mount(): void
    {
        $this->month = (int) now()->format('n');
        $this->year  = (int) now()->format('Y');
    }

    public function with(): array
    {
        $transactions = Transaction::with('user')
            ->whereMonth('transaction_date', $this->month)
            ->whereYear('transaction_date', $this->year)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $totalIncome  = $transactions->where('type', 'in')->sum('amount');
        $totalExpense = $transactions->where('type', 'out')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        return compact('transactions', 'totalIncome', 'totalExpense', 'balance');
    }

    /** Available years: current year ± 5 */
    public function years(): array
    {
        $current = (int) now()->format('Y');
        return range($current - 5, $current + 1);
    }
}; ?>

<div x-data>

    {{-- ══════════════════════════════════════════════════
         PRINT STYLES  (injected via <style> in head-equivalent)
    ══════════════════════════════════════════════════ --}}
    <style>
        @media print {
            /* Hide everything except the report */
            body > * { display: none !important; }
            #print-area,
            #print-area * { display: revert !important; }

            #print-area {
                position: fixed;
                inset: 0;
                background: white;
                color: black;
                font-family: sans-serif;
                font-size: 12px;
                padding: 24px;
            }

            .no-print { display: none !important; }

            /* Color classes must survive print */
            .print-green { color: #16a34a !important; }
            .print-orange { color: #ea580c !important; }
            .print-red   { color: #dc2626 !important; }

            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; font-size: 11px; }
            th { background: #f9fafb; font-weight: 600; }
        }
    </style>

    <div id="print-area">

        {{-- ── Period Selector ── --}}
        <div class="no-print flex flex-wrap gap-3 items-end mb-6">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Month</label>
                <select wire:model.live="month"
                    class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Year</label>
                <select wire:model.live="year"
                    class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700
                           text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @foreach($this->years() as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Print button --}}
            <button
                onclick="window.print()"
                class="ml-auto flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold
                       bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white transition-all shadow"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print / Save PDF
            </button>
        </div>

        {{-- ── Print Header (only visible when printing) ── --}}
        <div class="hidden print:block mb-6">
            <h1 class="text-xl font-bold text-gray-900">Shared Finance Report</h1>
            <p class="text-sm text-gray-500 mt-1">
                Period: {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Printed: {{ now()->format('d M Y, H:i') }}</p>
        </div>

        {{-- ── Period label (screen) ── --}}
        <div class="no-print flex items-center gap-2 mb-5">
            <span class="text-base font-semibold text-gray-700 dark:text-gray-200">
                {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500">
                · {{ $transactions->count() }} transaction(s)
            </span>
            <span wire:loading class="ml-2 text-xs text-indigo-400 animate-pulse">Loading…</span>
        </div>

        {{-- ── Summary Cards ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">

            {{-- Income --}}
            <div class="rounded-2xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-800 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-green-600 dark:text-green-400 font-medium uppercase tracking-wide">Total Income</p>
                    <p class="text-lg font-bold text-green-700 dark:text-green-300 truncate">
                        EGP {{ number_format($totalIncome, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Expense --}}
            <div class="rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-800 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-red-600 dark:text-red-400 font-medium uppercase tracking-wide">Total Expense</p>
                    <p class="text-lg font-bold text-red-700 dark:text-red-300 truncate">
                        EGP {{ number_format($totalExpense, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Balance --}}
            <div @class([
                'rounded-2xl border p-4 flex items-center gap-4',
                'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-700' => $balance >= 0,
                'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-700' => $balance < 0,
            ])>
                <div @class([
                    'w-10 h-10 rounded-xl flex items-center justify-center shrink-0',
                    'bg-indigo-100 dark:bg-indigo-800' => $balance >= 0,
                    'bg-orange-100 dark:bg-orange-800' => $balance < 0,
                ])>
                    <svg @class([
                        'w-5 h-5',
                        'text-indigo-600 dark:text-indigo-300' => $balance >= 0,
                        'text-orange-600 dark:text-orange-300' => $balance < 0,
                    ]) fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p @class([
                        'text-xs font-medium uppercase tracking-wide',
                        'text-indigo-600 dark:text-indigo-400' => $balance >= 0,
                        'text-orange-600 dark:text-orange-400' => $balance < 0,
                    ])>Net Balance</p>
                    <p @class([
                        'text-lg font-bold truncate',
                        'text-indigo-700 dark:text-indigo-300' => $balance >= 0,
                        'text-orange-700 dark:text-orange-300' => $balance < 0,
                    ])>
                        {{ $balance >= 0 ? '+' : '' }}EGP {{ number_format($balance, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             MOBILE: Cards (hidden md+)
        ══════════════════════════════════════════════════ --}}
        <div class="space-y-3 md:hidden no-print">
            @forelse($transactions as $tx)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <span class="text-xs text-gray-400">{{ $tx->transaction_date->format('d M Y') }}</span>
                        <span class="font-bold text-sm {{ $tx->colorClass() }} whitespace-nowrap">
                            {{ $tx->type === 'in' ? '+' : '-' }}EGP {{ number_format($tx->amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <p class="text-sm font-semibold {{ $tx->colorClass() }} leading-snug mb-2">{{ $tx->description }}</p>
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-xs text-gray-400">by <strong class="text-gray-600 dark:text-gray-300">{{ $tx->user->name ?? '—' }}</strong></span>
                        <span @class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                            'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $tx->type === 'in',
                            'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'         => $tx->type === 'out',
                        ])>{{ strtoupper($tx->type) }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 capitalize">{{ $tx->source }}</span>
                        <span @class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize',
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $tx->status === 'pending',
                            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'         => $tx->status === 'settled',
                        ])>{{ $tx->status }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-gray-400 italic text-sm">No transactions for this period.</div>
            @endforelse
        </div>

        {{-- ══════════════════════════════════════════════════
             DESKTOP + PRINT: Table
        ══════════════════════════════════════════════════ --}}
        <div class="hidden md:block overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">By</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($transactions as $i => $tx)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300">
                                {{ $tx->transaction_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 {{ $tx->colorClass() }} font-medium max-w-[220px] truncate">
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
                            <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $tx->source }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize',
                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $tx->status === 'pending',
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'         => $tx->status === 'settled',
                                ])>{{ $tx->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold {{ $tx->colorClass() }} whitespace-nowrap">
                                {{ $tx->type === 'in' ? '+' : '-' }}EGP {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500 italic">
                                No transactions found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- ── Totals footer ── --}}
                @if($transactions->isNotEmpty())
                <tfoot class="bg-gray-50 dark:bg-gray-900 border-t-2 border-gray-300 dark:border-gray-600">
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-sm font-semibold text-green-600 dark:text-green-400 text-right">
                            Total Income
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400 whitespace-nowrap">
                            +EGP {{ number_format($totalIncome, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-sm font-semibold text-red-600 dark:text-red-400 text-right">
                            Total Expense
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-red-600 dark:text-red-400 whitespace-nowrap">
                            -EGP {{ number_format($totalExpense, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="border-t border-gray-300 dark:border-gray-600">
                        <td colspan="7" class="px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-200 text-right">
                            Net Balance
                        </td>
                        <td @class([
                            'px-4 py-3 text-right font-bold text-base whitespace-nowrap',
                            'text-indigo-600 dark:text-indigo-400' => $balance >= 0,
                            'text-orange-600 dark:text-orange-400' => $balance < 0,
                        ])>
                            {{ $balance >= 0 ? '+' : '' }}EGP {{ number_format($balance, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- ── Mobile totals (no-print) ── --}}
        @if($transactions->isNotEmpty())
        <div class="md:hidden no-print mt-4 rounded-2xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
            <div class="flex justify-between items-center px-4 py-3 bg-white dark:bg-gray-800">
                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Total Income</span>
                <span class="font-bold text-green-600 dark:text-green-400">+EGP {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 bg-white dark:bg-gray-800">
                <span class="text-sm text-red-600 dark:text-red-400 font-medium">Total Expense</span>
                <span class="font-bold text-red-600 dark:text-red-400">-EGP {{ number_format($totalExpense, 0, ',', '.') }}</span>
            </div>
            <div @class([
                'flex justify-between items-center px-4 py-3',
                'bg-indigo-50 dark:bg-indigo-900/20' => $balance >= 0,
                'bg-orange-50 dark:bg-orange-900/20' => $balance < 0,
            ])>
                <span @class(['text-sm font-bold',
                    'text-indigo-700 dark:text-indigo-300' => $balance >= 0,
                    'text-orange-700 dark:text-orange-300' => $balance < 0,
                ])>Net Balance</span>
                <span @class(['font-bold text-base',
                    'text-indigo-700 dark:text-indigo-300' => $balance >= 0,
                    'text-orange-700 dark:text-orange-300' => $balance < 0,
                ])>{{ $balance >= 0 ? '+' : '' }}EGP {{ number_format($balance, 0, ',', '.') }}</span>
            </div>
        </div>
        @endif

    </div>{{-- /print-area --}}
</div>

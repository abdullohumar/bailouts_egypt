<?php

use App\Models\Transaction;
use Livewire\Volt\Component;

new class extends Component {

    protected $listeners = ['transaction-saved' => 'refresh'];

    public function refresh(): void {} // triggers re-render

    public function with(): array
    {
        $totalIncome  = Transaction::where('type', 'in')->sum('amount');
        $totalExpense = Transaction::where('type', 'out')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        // Pending reimbursements (personal + out + pending)
        $pendingAmount = Transaction::where('type', 'out')
            ->where('source', 'personal')
            ->where('status', 'pending')
            ->sum('amount');

        return compact('totalIncome', 'totalExpense', 'balance', 'pendingAmount');
    }
}; ?>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

    {{-- Total Income --}}
    <div class="rounded-2xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
        <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">Total Income</p>
        <p class="text-lg font-bold text-green-700 dark:text-green-300 truncate">
            EGP {{ number_format($totalIncome, 0, ',', '.') }}
        </p>
        <div class="mt-2 flex items-center gap-1 text-green-500">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
            </svg>
            <span class="text-xs">All time</span>
        </div>
    </div>

    {{-- Total Expense --}}
    <div class="rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
        <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide mb-1">Total Expense</p>
        <p class="text-lg font-bold text-red-700 dark:text-red-300 truncate">
            EGP {{ number_format($totalExpense, 0, ',', '.') }}
        </p>
        <div class="mt-2 flex items-center gap-1 text-red-400">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
            </svg>
            <span class="text-xs">All time</span>
        </div>
    </div>

    {{-- Net Balance --}}
    <div @class([
        'rounded-2xl border p-4',
        'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800' => $balance >= 0,
        'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800' => $balance < 0,
    ])>
        <p @class(['text-xs font-semibold uppercase tracking-wide mb-1',
            'text-indigo-600 dark:text-indigo-400' => $balance >= 0,
            'text-orange-600 dark:text-orange-400' => $balance < 0,
        ])>Net Balance</p>
        <p @class(['text-lg font-bold truncate',
            'text-indigo-700 dark:text-indigo-300' => $balance >= 0,
            'text-orange-700 dark:text-orange-300' => $balance < 0,
        ])>
            {{ $balance >= 0 ? '+' : '' }}EGP {{ number_format($balance, 0, ',', '.') }}
        </p>
        <div @class(['mt-2 flex items-center gap-1',
            'text-indigo-400' => $balance >= 0,
            'text-orange-400' => $balance < 0,
        ])>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
            </svg>
            <span class="text-xs">{{ $balance >= 0 ? 'Surplus' : 'Deficit' }}</span>
        </div>
    </div>

    {{-- Pending Reimbursement --}}
    <div class="rounded-2xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4">
        <p class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide mb-1">Pending Reimb.</p>
        <p class="text-lg font-bold text-yellow-700 dark:text-yellow-300 truncate">
            EGP {{ number_format($pendingAmount, 0, ',', '.') }}
        </p>
        <div class="mt-2 flex items-center gap-1 text-yellow-500">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-xs">Personal, unsettled</span>
        </div>
    </div>

</div>

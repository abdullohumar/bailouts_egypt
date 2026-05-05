<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-lg text-gray-800 dark:text-gray-200 leading-tight">
                📒 Shared Ledger
            </h2>
            @php $unread = Auth::user()->unreadNotifications->count(); @endphp
            @if($unread > 0)
                <a href="#" class="inline-flex items-center gap-1 text-xs bg-red-100 text-red-600
                                   dark:bg-red-900/40 dark:text-red-300 px-2.5 py-1 rounded-full font-semibold">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-2.83-2h5.66A3 3 0 0110 18z"/>
                    </svg>
                    {{ $unread }} new
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-5">

            {{-- ① Balance Summary Cards --}}
            <livewire:balance-summary />

            {{-- ② Add Transaction Form --}}
            <livewire:transaction-form />

            {{-- ③ Shared Ledger Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-4 sm:p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Transaction History
                    <span class="ml-auto text-xs text-gray-400 font-normal">
                        <a href="{{ route('report') }}" class="hover:text-indigo-500 transition">View Monthly Report →</a>
                    </span>
                </h3>
                <livewire:transaction-list />
            </div>

        </div>
    </div>
</x-app-layout>

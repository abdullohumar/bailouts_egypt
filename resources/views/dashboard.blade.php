<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Shared Ledger') }}
            </h2>
            @php $unread = Auth::user()->unreadNotifications->count(); @endphp
            @if($unread > 0)
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    You have <strong class="text-red-500">{{ $unread }}</strong> new notification(s).
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Transaction Form (visible to all authenticated users) --}}
            <livewire:transaction-form />

            {{-- Shared Ledger --}}
            <livewire:transaction-list />

        </div>
    </div>
</x-app-layout>

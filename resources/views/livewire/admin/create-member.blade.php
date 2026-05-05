<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $successMessage = '';

    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function createMember(): void
    {
        $this->validate();

        User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
            'role'     => 'member',
        ]);

        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->successMessage = 'Member created successfully!';
    }
}; ?>

<div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 sm:p-6 border border-gray-100 dark:border-gray-700">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 leading-tight">Add New Member</h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">All new users are assigned the Member role.</p>
        </div>
    </div>

    @if($successMessage)
        <div class="mb-5 p-3.5 rounded-xl bg-green-50 border border-green-200 text-green-700
                    dark:bg-green-900/30 dark:border-green-700 dark:text-green-300 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="createMember" class="space-y-4">

        {{-- Name --}}
        <div>
            <x-input-label for="cm_name" :value="__('Full Name')" />
            <x-text-input id="cm_name" wire:model="name" type="text"
                class="mt-1 block w-full text-base" placeholder="John Doe" autocomplete="off" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="cm_email" :value="__('Email Address')" />
            <x-text-input id="cm_email" wire:model="email" type="email"
                class="mt-1 block w-full text-base" placeholder="member@example.com" autocomplete="off" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        {{-- Password + Confirm side by side on larger mobile --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="cm_password" :value="__('Password')" />
                <x-text-input id="cm_password" wire:model="password" type="password"
                    class="mt-1 block w-full text-base" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="cm_password_confirmation" :value="__('Confirm')" />
                <x-text-input id="cm_password_confirmation" wire:model="password_confirmation"
                    type="password" class="mt-1 block w-full text-base" autocomplete="new-password" />
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
                <span wire:loading.remove wire:target="createMember">Create Member</span>
                <span wire:loading wire:target="createMember" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Creating…
                </span>
            </button>
        </div>

    </form>
</div>
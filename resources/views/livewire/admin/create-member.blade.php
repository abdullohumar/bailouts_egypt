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

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-6">
        Add New Member
    </h2>

    @if($successMessage)
        <div class="mb-4 p-3 rounded bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200 text-sm">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="createMember" class="space-y-5">

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input
                id="name"
                wire:model="name"
                type="text"
                class="mt-1 block w-full"
                placeholder="John Doe"
                autocomplete="off"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input
                id="email"
                wire:model="email"
                type="email"
                class="mt-1 block w-full"
                placeholder="member@example.com"
                autocomplete="off"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                wire:model="password"
                type="password"
                class="mt-1 block w-full"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input
                id="password_confirmation"
                wire:model="password_confirmation"
                type="password"
                class="mt-1 block w-full"
            />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>
                {{ __('Create Member') }}
            </x-primary-button>

            <span wire:loading wire:target="createMember" class="text-sm text-gray-500 dark:text-gray-400">
                Creating…
            </span>
        </div>

    </form>
</div>
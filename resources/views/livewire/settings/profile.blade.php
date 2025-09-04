<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $username = '';
    public string $role = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->username = Auth::user()->username;
        $this->role = Auth::user()->role;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'role' => ['required', 'string', 'max:255'],
        ]);

        $user->fill($validated);

        $user->save();

        $this->dispatch('profile-updated', username: $user->username);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="username" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:select wire:model="role" :label="__('Role...')">
                    <flux:select.option>Select Designation</flux:select.option>
                        <flux:select.option value="Student">Student</flux:select.option>
                        <flux:select.option value="Teacher">Teacher</flux:select.option>
                        <flux:select.option value="Staff">Staff</flux:select.option>
                        <flux:select.option value="Admin">Admin</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>

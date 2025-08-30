<flux:modal name="delete-confirmation" class="w-full">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl" class="text-danger">{{ $heading }}</flux:heading>

            <flux:text class="mt-5">
                <p class="loading-loose">{{ $subheading }} : <span class="text-sm text-green-600 dark:text-green-500">{{ $name }}</span></p>
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:modal.close>
                <flux:button variant="ghost" class="cursor-pointer" x-on:click="$flux.modal('delete-confirmation').close()">Cancel</flux:button>
            </flux:modal.close>

            <flux:button wire:click="delete" variant="danger" class="cursor-pointer">{{ $confirmButtonText }}</flux:button>
        </div>
    </div>
</flux:modal>

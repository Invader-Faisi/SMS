{{-- modal --}}
<flux:modal name="add-{{ $page }}" class="w-full max-w-2xl md:max-w-xl lg:max-w-2xl">
    <div class="space-y-6">
        <div class="text-center">
            {{ $isEditMode ? 'Update '.$page.' Data' : 'Add New '.$page.' Data' }}
        </div>
        <form wire:submit.prevent="{{ $isEditMode ? 'update'.$page : 'save'.$page }}">
            <div class="flex flex-col space-y-4">
                <div>
                    <flux:input wire:model="name" :label="__('Name')" class="w-full"/>
                </div>
                <div>
                    <flux:select wire:model="class" :label="__('Class')" class="w-full">
                        <flux:select.option value="null">Select Class...</flux:select.option>
                        <flux:select.option value="Junior">Junior</flux:select.option>
                        <flux:select.option value="Primary">Primary</flux:select.option>
                        <flux:select.option value="Middle">Middle</flux:select.option>
                        <flux:select.option value="Secondary">Secondary</flux:select.option>
                    </flux:select>
                </div>
                <div>
                    <flux:input type="number" min="10" wire:model="amount" :label="__('Amount')" class="w-full"/>
                </div>
                <div>
                    <flux:select wire:model="type" :label="__('Fee Type')" class="w-full">
                        <flux:select.option value="null">Select Type...</flux:select.option>
                        <flux:select.option value="Admission">Admission</flux:select.option>
                        <flux:select.option value="Recurring">Recurring</flux:select.option>
                        <flux:select.option value="Fine">Fine</flux:select.option>
                        <flux:select.option value="Misc">Misc</flux:select.option>
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model="frequency" :label="__('Fee Frequency')" class="w-full">
                        <flux:select.option value="null">Select Frequency...</flux:select.option>
                        <flux:select.option value="Monthly">Monthly</flux:select.option>
                        <flux:select.option value="Yearly">Yearly</flux:select.option>
                        <flux:select.option value="One Time">One Time</flux:select.option>
                    </flux:select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button variant="filled" class="cursor-pointer" wire:click="closeModal">Cancel</flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="image"
                             variant="primary" color="blue" class="cursor-pointer ms-2">
                    {{ $isEditMode ? 'Update' : 'Save' }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>



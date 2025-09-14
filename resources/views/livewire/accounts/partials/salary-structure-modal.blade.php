{{-- modal --}}
<flux:modal name="add-{{ $page }}" class="w-full max-w-2xl md:max-w-xl lg:max-w-2xl">
    <div class="space-y-6">
        <div class="text-center">
            {{ $isEditMode ? 'Update '.$page.' Data' : 'Add New '.$page }}
        </div>
        <form wire:submit.prevent="{{ $isEditMode ? 'update'.$page : 'save'.$page }}">
            <div class="space-y-4">
                <!-- Row 1: Two Selects -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <flux:select wire:model="type" :label="__('Type')" class="w-full">
                        <flux:select.option value="null">Select Class...</flux:select.option>
                        <flux:select.option value="teacher">Teacher</flux:select.option>
                        <flux:select.option value="staff">Staff</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="category" :label="__('Category')" class="w-full">
                        <flux:select.option value="null">Select Type...</flux:select.option>
                        <flux:select.option value="first">Cat - I</flux:select.option>
                        <flux:select.option value="second">Cat - II</flux:select.option>
                        <flux:select.option value="third">Cat - III</flux:select.option>
                        <flux:select.option value="lower">Cat - IV</flux:select.option>
                    </flux:select>
                </div>

                <!-- Row 2: Single Input -->
                <div>
                    <flux:input type="number" min="10000" wire:model="basic_salary" :label="__('Basic Salary')" class="w-full"/>
                </div>

                <!-- Row 3: Two Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <flux:input type="number" min="0" wire:model="house_allowance" :label="__('House Allowance')" class="w-full"/>
                    <flux:input type="number" min="0" wire:model="medical_allowance" :label="__('Medical Allowance')" class="w-full"/>
                </div>

                <!-- Row 4: Two Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <flux:input type="number" min="0" wire:model="transport_allowance" :label="__('Transportation')" class="w-full"/>
                    <flux:input type="number" min="0" wire:model="other_allowance" :label="__('Misc')" class="w-full"/>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <flux:button variant="filled" class="cursor-pointer" wire:click="closeModal">Cancel</flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="category"
                             variant="primary" color="blue" class="cursor-pointer ms-2">
                    {{ $isEditMode ? 'Update' : 'Save' }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>



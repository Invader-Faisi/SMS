{{-- modal --}}
<flux:modal name="add-{{ $page }}" class="w-full max-w-3xl md:max-w-2xl lg:max-w-4xl">
    <div class="space-y-6">
        <div>
            {{ $isEditMode ? 'Update '.$page.' Data' : 'Add New '.$page.' Data' }}
        </div>
        <form wire:submit.prevent="{{ $isEditMode ? 'update'.$page : 'save'.$page }}"
              enctype="multipart/form-data">
            <div class="flex flex-wrap -mx-2">
                <!-- Left Column -->
                <div class="form-group w-full md:w-1/2 px-2">
                    <flux:input wire:model="name" :label="__('Full Name')" class="w-full p-2"/>
                    <flux:input wire:model="{{ $page === 'Student' ? 'b_form' : 'cnic' }}" mask="99999-9999999-9" :label="$page === 'Student' ? __('B-Form') : __('CNIC')" class="w-full p-2"/>
                    <flux:input wire:model="password" type="text" :label="__('Password')" class="w-full p-2"/>
                    @if($page != 'Student')
                    <flux:input wire:model="mobile" :label="__('Mobile Number')" class="w-full p-2"/>
                    @endif
                    @if ($isEditMode && $updateImage)
                        <flux:input type="file" wire:model="updatedImage" accept="image/*"
                                    :label="__('Profile Image')" class="w-full p-2"/>
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $updateImage) }}"
                                 class="size-8 rounded-full object-cover" alt="{{ $page }} Image">
                        </div>
                    @else
                        <flux:input type="file" wire:model="image" accept="image/*" :label="__('Profile Image')"
                                    class="w-full p-2"/>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="form-group w-full md:w-1/2 px-2">
                    @if($page == 'Student')
                    <div class="flex flex-col gap-3">
                        <flux:select  wire:model="parent_id" :label="__('Parent')" class="w-full p-2">
                            <flux:select.option value="">Select Parent...</flux:select.option>
                            @foreach($parents as $parent)
                                <flux:select.option value="{{ $parent->parent_id }}">
                                    {{ $parent->name }} - {{ $parent->mobile }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="class" :label="__('Class')" class="w-full p-2">
                            <flux:select.option value="null">Select Class...</flux:select.option>
                            <flux:select.option value="Nursery">Nursery</flux:select.option>
                            <flux:select.option value="Prep">Prep</flux:select.option>
                            <flux:select.option value="I">I</flux:select.option>
                            <flux:select.option value="II">II</flux:select.option>
                            <flux:select.option value="III">III</flux:select.option>
                            <flux:select.option value="IV">IV</flux:select.option>
                            <flux:select.option value="V">V</flux:select.option>
                            <flux:select.option value="VI">VI</flux:select.option>
                            <flux:select.option value="VII">VII</flux:select.option>
                            <flux:select.option value="VIII">VIII</flux:select.option>
                            <flux:select.option value="IX">IX</flux:select.option>
                            <flux:select.option value="X">X</flux:select.option>
                        </flux:select>

                        <flux:select wire:model="section" :label="__('Section')" class="w-full p-2">
                            <flux:select.option value="null">Select Section...</flux:select.option>
                            <flux:select.option value="A">A</flux:select.option>
                            <flux:select.option value="B">B</flux:select.option>
                            <flux:select.option value="C">C</flux:select.option>
                            <flux:select.option value="D">D</flux:select.option>
                            <flux:select.option value="E">E</flux:select.option>
                            <flux:select.option value="F">F</flux:select.option>
                        </flux:select>
                    </div>
                    @else
                        <flux:input wire:model="email" :label="__('Email')" class="w-full p-2"/>
                        <flux:input wire:model="address" :label="__('Home Address')" class="w-full p-2"/>
                        <flux:input wire:model="qualification" :label="__('Qualification')" class="w-full p-2"/>
                        <flux:select wire:model="designation" :label="__('Designation...')">
                            <flux:select.option>Select Designation</flux:select.option>
                            @if($page == 'Teacher')
                                <flux:select.option value="ClassTeacher">Class Teacher</flux:select.option>
                                <flux:select.option value="SubjectSpecialist">Subject Specialist</flux:select.option>
                                <flux:select.option value="Teacher">Teacher</flux:select.option>
                            @elseif($page == 'Staff')
                                <flux:select.option value="Accountant">Accountant</flux:select.option>
                                <flux:select.option value="OfficeBoy">Office Boy</flux:select.option>
                                <flux:select.option value="Mali">Mali</flux:select.option>
                                <flux:select.option value="Guard">Guard</flux:select.option>
                                <flux:select.option value="Misc">Misc</flux:select.option>
                            @else
                                <flux:select.option value="Govt Servant">Govt Servant</flux:select.option>
                                <flux:select.option value="Private Job">Private Job</flux:select.option>
                                <flux:select.option value="Farmer">Farmer</flux:select.option>
                                <flux:select.option value="Shop Keeper">Shop Keeper</flux:select.option>
                                <flux:select.option value="Business">Business</flux:select.option>
                                <flux:select.option value="Misc">Misc</flux:select.option>
                            @endif
                        </flux:select>
                    @endif

                </div>
            </div>

            <div class="flex justify-end gap-2 mt-2">
                <flux:button variant="filled" class="cursor-pointer" wire:click="closeModal">Cancel</flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="image"
                             variant="primary" color="blue" class="cursor-pointer ms-2">
                    {{ $isEditMode ? 'Update' : 'Save' }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>

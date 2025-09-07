<flux:modal name="addTimeTable" class="w-full max-w-4xl md:max-w-3xl lg:max-w-5xl p-2 space-y-4">
    <div class="space-y-6">
        <div>
            Add Time Table For Whole of the Week
        </div>
        <form wire:submit.prevent="saveTimeTable">
            <div class="flex p-2 w-full">
                <flux:select  wire:model="class_id" wire:change="updateClass($event.target.value)" :label="__('Class')" class="w-full p-2">
                    <flux:select.option value="">Select Class...</flux:select.option>
                    @foreach($classes as $class)
                        <flux:select.option value="{{ $class->class_id }}">
                            {{ $class->class_id  }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            @foreach($totalDays as $day)
                @foreach($totalPeriods as $period)
                    <div class="grid grid-cols-6 gap-2 border p-2 rounded">
                        <!-- Day -->
                        <div class="flex p-2">
                            <flux:input type="text" value="{{ $day }}" disabled />
                        </div>

                        <!-- Period -->
                        <div class="flex p-2">
                            <flux:input type="text" value="{{ $period }}" disabled />
                        </div>

                        <!-- Subject -->
                        <div class="flex p-2">
                            <flux:select wire:model="timetableData.{{ $day }}.{{ $period }}.subject">
                                <flux:select.option value="">Select Subject...</flux:select.option>
                                @foreach($totalSubjects as $subject)
                                    <flux:select.option value="{{ $subject }}">
                                        {{ $subject }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <!-- Teacher -->
                        <div class="flex p-2">
                            <flux:select wire:model="timetableData.{{ $day }}.{{ $period }}.teacher_id">
                                <flux:select.option value="">Select Teacher...</flux:select.option>
                                @foreach($totalTeachers ?? [] as $teacher)
                                    <flux:select.option value="{{ $teacher->teacher_id }}">
                                        {{ $teacher->name }} - {{ $teacher->designation }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <!-- Start Time -->
                        <div class="flex p-2">
                            <flux:input type="time" wire:model="timetableData.{{ $day }}.{{ $period }}.start_time"/>
                        </div>

                        <!-- Save Checkbox -->
                        <div class="flex p-2">
                            <flux:checkbox wire:model="timetableData.{{ $day }}.{{ $period }}.save"
                                           wire:change="validateFields('timetableData.{{ $day }}.{{ $period }}.save')"/>
                        </div>
                    </div>
                @endforeach
            @endforeach
            <div class="flex justify-end gap-2 mt-2">
                <flux:button variant="filled" class="cursor-pointer" x-on:click="$flux.modal('addTimeTable').close()">Cancel</flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="image"
                             variant="primary" color="blue" class="cursor-pointer ms-2">
                    Save
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>



<?php

use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Title('Classes')]
class extends Component {
    use WithPagination;

    public string $navbarHeading = "Class Management";

    public Collection $teachersList;
    public ?string $teacher_id = null;
    public ?string $class_id = null;
    public ?string $academic_year = null;

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    public function mount(): void
    {
        $this->teachersList = new Collection();
    }

    // Helping variables

    public string $page = 'Classes';

    #[Url(history: true)]
    public $search;


    public function with(\App\Services\ClassManagementServices $classServices): array
    {
        return [
            'classes' => $classServices->getClassesList($this->search),
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function showTeachers($id, $academic_year, \App\Services\ClassManagementServices $classServices): void
    {
        $class = $classServices->getClassById($id);

        if ($class !== null) {
            $this->class_id = $class->class_id;
            $this->academic_year = $academic_year;
            $this->teachersList = $classServices->getTeachersList();
            Flux::modal('show-teachers')->show();
        }
    }


    public function updateClass(\App\Services\ClassManagementServices $classServices): void
    {
        if (empty($this->teacher_id) || $this->teacher_id === "null") {
            $this->dispatch('notify', type: 'error', message: 'Please Select the Teacher First');
            return;
        }

        $response = $classServices->updateClass($this->class_id, $this->teacher_id, $this->academic_year);
        if ($response > 0) {
            $this->dispatch('notify', type: 'success', message: 'Teacher Added to ' . $this->class_id . ' successfully.');
            $this->reset(['teacher_id', 'class_id']);
        } elseif(is_string($response)) {
            $this->dispatch('notify', type: 'error', message: $response);
        }elseif($response === 0){
            $this->dispatch('notify', type: 'error', message: 'No record found to update !!!');
        }
        Flux::modal('show-teachers')->close();

    }

}; ?>

<section class="space-y-2">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3 p-2">
        <div class="w-full">
            <flux:input wire:model.live.debounce.1000ms="search" icon="magnifying-glass" placeholder="Search Class..."
                        class="text-sm"/>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-6 p-2">
        @foreach($classes as $class)
            <article
                class="group flex rounded-radius max-w-xs flex-col overflow-hidden border border-outline bg-surface-alt text-on-surface dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark">
                <!-- Images -->
                <div class="relative h-24">
                    <div
                        class="relative mx-auto mt-5 size-24 overflow-hidden rounded-full border-4 border-surface-alt dark:border-surface-dark-alt">
                        <img src="{{ asset('storage/' . $class->teacher->image) }}"
                             class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                             alt="avatar"/>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex flex-col gap-2 p-2 text-center mt-2">
                    <p class="h-14 text-balance text-md font-bold text-on-surface-strong lg:text-md dark:text-on-surface-dark-strong">
                        {{$class->teacher->name}}</p>
                    <span
                        class="mx-auto w-full bg-indigo-500 px-2 py-1 text-lg text-on-primary dark:bg-primary-dark dark:text-on-primary-dark rounded-radius">{{$class->class_id}}</span>
                    <p id="profileDescription" class="mt-4 text-pretty text-sm">{{$class->academic_year}}</p>
                    <!-- Links -->
                    <div class="mt-4 flex items-center justify-center gap-4">

                        <!-- Time Table -->
                        <flux:button tooltip="View Time Table" variant="primary" color="red" size="xs"
                                     class="cursor-pointer">
                            <a href="{{ route('classes.class.timetable', $class->class_id) }}">
                                <flux:icon.table-cells variant="solid" class="size-4"/>
                            </a>
                        </flux:button>
                        <!-- Teacher Update -->
                        <flux:button tooltip="Assign Class Teacher" variant="primary" color="yellow" size="xs"
                                     class="cursor-pointer" wire:click="showTeachers({{ $class->id }}, '{{ $class->academic_year }}')">
                            <flux:icon.pencil variant="solid" class="size-4"/>
                        </flux:button>

                        <!-- View Class -->
                        <flux:button tooltip="View Class Strength" variant="primary" color="green" size="xs"
                                     class="cursor-pointer">
                            <a href="{{ route('classes.class', $class->class_id) }}">
                                <flux:icon.eye variant="solid" class="size-4"/>
                            </a>
                        </flux:button>
                    </div>
                    <flux:button href="{{ route('classes.class.attendance', $class->class_id) }}" icon:trailing="arrow-up-right">
                            Attendance
                    </flux:button>
                </div>
            </article>
        @endforeach
    </div>

    <flux:modal name="show-teachers" class="w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="xl" class="text-primary">Update Class</flux:heading>
            </div>

            <div class="flex flex-col gap-2">
                <flux:select wire:model="teacher_id" :label="__('Teachers')">
                    <flux:select.option value="null">Teachers List...</flux:select.option>
                    @foreach($teachersList as $teacher)
                        <flux:select.option value="{{ $teacher->teacher_id }}">
                            {{ $teacher->name }} - {{ $teacher->designation }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input type="date" wire:model="academic_year" :label="__('Academic Year')"  />
                <flux:spacer/>
                <div class="flex flex-row gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="cursor-pointer"
                                     x-on:click="$flux.modal('show-teachers').close()">Cancel
                        </flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="updateClass" variant="danger" class="cursor-pointer">
                        Update Class
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</section>





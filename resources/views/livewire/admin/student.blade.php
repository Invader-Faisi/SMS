<?php

use Livewire\Volt\Component;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new
#[Title('Student')]
class extends Component {
    use WithFileUploads;
    use WithPagination;

    public string $navbarHeading = "Students Management";

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    //    Form fields
    public $image = null;
    public string $parent_id = '';
    public string $name = '';
    public string $password = '';
    public string $class = '';
    public string $section = '';

    //    helper variables
    public $updateImage = null;
    public $student_id;
    public $isEditMode = false;
    public string $page = 'Student';

    //    Table variables
    public $search;

    public $perPage = 5;

    public $sortedBy = 'student_id';

    public $sortDirection = 'DESC';

    public function with(\App\Services\StudentManagementServices $studentServices): array
    {

        return [
            'students' => $studentServices->getStudentList(
                $this->search,
                $this->perPage,
                $this->sortedBy,
                $this->sortDirection,
            ),
            'parents' => \App\Models\Parents::orderBy('name')->get(),
        ];
    }

    public function saveStudent(\App\Services\StudentManagementServices $studentServices): void
    {
        $studentForm = $this->validateFields();
        $student = $studentServices->saveStudent($studentForm);

        if ($student === true) {
            $this->dispatch('notify', type: 'success', message: 'Student added successfully.');
        } else {
            $this->dispatch('notify', type: 'error', message: $student);
        }
        $this->closeModal();
    }

    public function showStudent(\App\Services\StudentManagementServices $studentServices, $studentId): void
    {
        $student = $studentServices->getStudentById($studentId);

        if ($student !== null) {
            $this->student_id = $student->student_id;
            $this->parent_id = $student->parent_id;
            $this->updateImage = $student->image;
            $this->name = $student->name;
            $this->password = $student->password;
            $this->class = $student->class;
            $this->section = $student->section;

            $this->isEditMode = true;
            Flux::modal('add-' . $this->page)->show();
        }
    }

    public function updateStudent(\App\Services\StudentManagementServices $studentServices)
    {
        $studentForm = $this->validateFields();
        if ($this->updateImage != null && $this->image == null) {
            $studentForm['image'] = $this->updateImage;
        }

        $student = $studentServices->updateStudent($studentForm, $this->student_id);

        if ($student > 0) {
            $this->reset();
            $this->isEditMode = false;
            $this->dispatch('notify', type: 'success', message: 'Student updated successfully.');

        } else {
            $this->dispatch('notify', type: 'error', message: $student);
        }
        $this->closeModal();
    }

    #[On('delete-student')]
    public function delete(\App\Services\StudentManagementServices $studentServices, $id): void
    {
        $student = $studentServices->getStudentById($id);
        if($student !== null){
            $this->student_id = $student->student_id;
            $student = $studentServices->deleteStudent($this->student_id);
            if ($student > 0) {
                $this->dispatch('notify', type: 'success', message: 'Student deleted successfully.');
            }else{
                $this->dispatch('notify', type: 'error', message: $student);
            }
            $this->reset();
        }
    }

    public function validateFields(): array
    {
        if ($this->isEditMode) {
            return $this->validate([
                'parent_id' => ['required', 'string', 'max:255'],
                'image' => ['nullable', 'image', 'max:2048'],
                'name' => ['required', 'string', 'max:255'],
                'class' => ['required', 'string', 'max:255'],
                'section' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);
        } else {
            return $this->validate([
                'parent_id' => ['required', 'string', 'max:255'],
                'image' => ['nullable', 'image', 'max:2048'],
                'name' => ['required', 'string', 'max:255'],
                'class' => ['required', 'string', 'max:255'],
                'section' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);
        }

    }

    public function closeModal(): void
    {
        $this->reset();
        $this->isEditMode = false;
        Flux::modal('add-' . $this->page)->close();
    }






}; ?>

<section class="p-2">
    {{-- Student Table--}}
    <div class="space-y-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3">
            <div class="w-full lg:w-52">
                <flux:input wire:model.live.debounce.1000ms="search" icon="magnifying-glass" placeholder="Search orders"
                            class="text-sm"/>
            </div>
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <flux:select wire:model.live.debounce.200ms="perPage">
                    <flux:select.option value="null">Choose Per Page Record...</flux:select.option>
                    <flux:select.option>5</flux:select.option>
                    <flux:select.option>10</flux:select.option>
                    <flux:select.option>15</flux:select.option>
                    <flux:select.option>20</flux:select.option>
                </flux:select>
                <flux:select>
                    <flux:select.option value="null">Parent List...</flux:select.option>
                    @foreach($parents as $parent)
                        <flux:select.option value="{{ $parent->parent_id }}">
                            {{ $parent->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:modal.trigger name="add-{{ $page }}">
                    <flux:button variant="primary" color="indigo" icon="plus-circle" class="cursor-pointer">
                        Add {{$page}}
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <div
            class="overflow-hidden w-full overflow-x-auto rounded-radius border border-outline dark:border-outline-dark">

            <div class="mx-2 mb-2 mt-2">
                {{$students->links()}}
            </div>
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
                <thead
                    class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                <tr>
                    <th scope="col" class="p-4">{{$page}} - ID</th>
                    <th scope="col" class="p-4">{{$page}}</th>
                    <th scope="col" class="p-4">Password</th>
                    <th scope="col" class="p-4">Class</th>
                    <th scope="col" class="p-4">Section</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @forelse($students as $student)
                    <tr key="{{$student->id}}">
                        <td class="p-4">{{$student->student_id}}</td>
                        <td class="p-4">
                            <div class="flex w-max items-center gap-2">
                                <img src="{{ asset('storage/' . $student->image) }}"
                                     class="size-8 rounded-full object-cover" alt="Staff Image">
                                <div class="flex flex-col">
                                    <span class="text-neutral-900 dark:text-white">{{$student->name}}</span>
                                    <p class="text-pink-800 mt-2 font-bold text-md">Parental Information</p>
                                    <span class="text-success dark:text-white">{{$student->parent->name}}</span>
                                    <span class="text-neutral-500 dark:text-white">{{$student->parent->mobile}}</span>
                                    <span
                                        class="text-sm text-neutral-600 opacity-85 dark:text-neutral-300">{{$student->parent->email}}</span>
                                    <span class="text-neutral-900 dark:text-white">{{$student->parent->address}}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">{{$student->password}}</td>
                        <td class="p-4 text-lg text-primary text-center" >{{$student->class}}</td>
                        <td class="p-4"><span
                                class="inline-flex overflow-hidden rounded-radius border-success px-1 py-0.5 text-lg text-center font-medium text-success bg-success/10">{{$student->section}}</span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="delete-confirmation">
                                        <flux:button variant="primary" color="rose" size="xs" class="cursor-pointer"
                                                     wire:click="$dispatch('confirm-delete',{
                                            id: {{$student->id}},
                                            dispatchAction: 'delete-student',
                                            heading: 'Delete Student Data',
                                            subheading: 'You are deleting data of',
                                            name: '{{$student->name}}',
                                            confirmButtonText: 'Delete Student',
                                            })">
                                            <flux:icon.trash variant="solid" class="size-4"/>
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <flux:button variant="primary" color="yellow" size="xs" class="cursor-pointer"
                                                 wire:click="showStudent({{ $student->id }})">
                                        <flux:icon.pencil variant="solid" class="size-4"/>
                                    </flux:button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center">No data found!!!</td>
                    </tr>
                @endforelse

                </tbody>
            </table>
            <flux:separator variant="subtle"/>
            <div class="mx-2 mb-2 mt-2">
                {{$students->links()}}
            </div>
            <livewire:common.delete/>
        </div>
    </div>
    @include('livewire.admin.partials.teacher-staff-modal', ['isEditMode' => $isEditMode, 'page' => $page])
</section>

<?php

use Livewire\Volt\Component;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new
#[Title('Teacher')]
class extends Component {
    use WithFileUploads;
    use WithPagination;

    public string $navbarHeading = "Teacher Management";

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }


//    Form fields
    public $image = null;
    public string $name = '';
    public string $cnic = '';
    public string $email = '';
    public string $mobile = '';
    public string $address = '';
    public string $password = '';
    public string $qualification = '';
    public string $designation = '';

//    helper variables
    public $updateImage = null;
    public $teacher_id;
    public $isEditMode = false;
    public string $page = 'Teacher';

//    Table variables
   #[Url(history: true)]
    public $search;

    #[Url(history: true)]
    public $perPage = 5;

    #[Url(history: true)]
    public $sortedBy = 'teacher_id';

    public $sortDirection = 'DESC';

    public function with(\App\Services\TeacherManagementServices $teacherServices): array
    {
        return [
            'teachers' => $teacherServices->getTeacherList(
                $this->search,
                $this->perPage,
                $this->sortedBy,
                $this->sortDirection
            ),
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function saveTeacher(\App\Services\TeacherManagementServices $teacherServices): void
    {
        $teacherForm = $this->validateFields();
        $teacher = $teacherServices->saveTeacher($teacherForm);

        if ($teacher === true) {
            $this->dispatch('notify', type: 'success', message: 'Teacher added successfully.');
        } else {
            $this->dispatch('notify', type: 'error', message: $teacher);
        }
        $this->closeModal();
    }

    public function showTeacher(\App\Services\TeacherManagementServices $teacherServices, $teacherId): void
    {
        $teacher = $teacherServices->getTeacherById($teacherId);

        if ($teacher !== null) {
            $this->teacher_id = $teacher->teacher_id;
            $this->updateImage = $teacher->image;
            $this->name = $teacher->name;
            $this->cnic = $teacher->cnic;
            $this->email = $teacher->email;
            $this->password = $teacher->password;
            $this->mobile = $teacher->mobile;
            $this->address = $teacher->address;
            $this->qualification = $teacher->qualification;
            $this->designation = $teacher->designation;

            $this->isEditMode = true;
            Flux::modal('add-' . $this->page)->show();
        }
    }

    public function updateTeacher(\App\Services\TeacherManagementServices $teacherServices): void
    {
        $teacherForm = $this->validateFields();
        if ($this->updateImage != null && $this->image == null) {
            $teacherForm['image'] = $this->updateImage;
        }

        $teacher = $teacherServices->updateTeacher($teacherForm, $this->teacher_id);

        if ($teacher > 0) {
            $this->reset();
            $this->isEditMode = false;
            $this->dispatch('notify', type: 'success', message: 'Teacher updated successfully.');

        } else {
            $this->dispatch('notify', type: 'error', message: $teacher);
        }
        $this->closeModal();
    }

    #[On('delete-teacher')]
    public function delete(\App\Services\TeacherManagementServices $teacherServices, $id): void
    {
        $teacher = $teacherServices->getTeacherById($id);
        if($teacher !== null){
            $this->teacher_id = $teacher->teacher_id;
            $teacher = $teacherServices->deleteTeacher($this->teacher_id);
            if ($teacher > 0) {
                $this->dispatch('notify', type: 'success', message: 'Teacher deleted successfully.');
            }else{
                $this->dispatch('notify', type: 'error', message: $teacher);
            }
            $this->reset();
        }
    }

    public function validateFields(): array
    {
        if ($this->isEditMode) {
            return $this->validate([
                'image' => ['nullable', 'image', 'max:2048'],
                'name' => ['required', 'string', 'max:255'],
                'cnic' => ['required', 'string', 'max:16'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'mobile' => ['required', 'string', 'min:11', 'max:11'],
                'address' => ['required', 'string', 'max:255'],
                'qualification' => ['required', 'string', 'max:255'],
                'designation' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);
        } else {
            return $this->validate([
                'image' => ['nullable', 'image', 'max:2048'],
                'name' => ['required', 'string', 'max:255'],
                'cnic' => ['required', 'string', 'max:16', 'unique:teachers'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'mobile' => ['required', 'string', 'min:11', 'max:11', 'unique:teachers'],
                'address' => ['required', 'string', 'max:255'],
                'qualification' => ['required', 'string', 'max:255'],
                'designation' => ['required', 'string', 'max:255'],
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
    {{-- Teacher Table--}}
    <div class="space-y-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3">
            <div class="w-full lg:w-52">
                <flux:input wire:model.live.debounce.500ms="search" icon="magnifying-glass" placeholder="Search orders"
                            class="text-sm"/>
            </div>
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <flux:select wire:model.live.debounce.500ms="perPage">
                    <flux:select.option value="null">Choose Per Page Record...</flux:select.option>
                    <flux:select.option>5</flux:select.option>
                    <flux:select.option>10</flux:select.option>
                    <flux:select.option>15</flux:select.option>
                    <flux:select.option>20</flux:select.option>
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
                {{$teachers->links()}}
            </div>
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
                <thead
                    class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                <tr>
                    <th scope="col" class="p-4">{{$page}} -ID</th>
                    <th scope="col" class="p-4">{{$page}}</th>
                    <th scope="col" class="p-4">Password</th>
                    <th scope="col" class="p-4">Qualification</th>
                    <th scope="col" class="p-4">Designation</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @forelse($teachers as $teacher)
                    <tr key="{{$teacher->id}}">
                        <td class="p-4">{{$teacher->teacher_id}}</td>
                        <td class="p-4">
                            <div class="flex w-max items-center gap-2">
                                <img src="{{ asset('storage/' . $teacher->image) }}"
                                     class="size-8 rounded-full object-cover" alt="Staff Image">
                                <div class="flex flex-col">
                                    <span class="text-neutral-900 dark:text-white">{{$teacher->name}}</span>
                                    <span class="text-neutral-500 dark:text-white">{{$teacher->mobile}}</span>
                                    <span
                                        class="text-sm text-neutral-600 opacity-85 dark:text-neutral-300">{{$teacher->cnic}}</span>
                                    <span class="text-neutral-900 dark:text-white">{{$teacher->address}}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">{{$teacher->password}}</td>
                        <td class="p-4">{{$teacher->qualification}}</td>
                        <td class="p-4"><span
                                class="inline-flex overflow-hidden rounded-radius border-success px-1 py-0.5 text-xs font-medium text-success bg-success/10">{{$teacher->designation}}</span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="delete-confirmation">
                                        <flux:button variant="primary" color="rose" size="xs" class="cursor-pointer"
                                                     wire:click="$dispatch('confirm-delete',{
                                        id: {{$teacher->id}},
                                        dispatchAction: 'delete-teacher',
                                        heading: 'Delete Teacher Data',
                                        subheading: 'You are deleting data of',
                                        name: '{{$teacher->name}}',
                                        confirmButtonText: 'Delete Teacher',
                                        })">
                                            <flux:icon.trash variant="solid" class="size-4"/>
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <flux:button variant="primary" color="yellow" size="xs" class="cursor-pointer"
                                                 wire:click="showTeacher({{ $teacher->id }})">
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
                {{$teachers->links()}}
            </div>
            <livewire:common.delete/>
        </div>
    </div>
    @include('livewire.admin.partials.teacher-staff-modal', ['isEditMode' => $isEditMode, 'page' => $page])
</section>

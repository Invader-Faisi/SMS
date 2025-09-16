<?php

use App\Services\StaffManagementServices;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new
#[Title('Staff')]
class extends Component {

    use WithFileUploads;
    use WithPagination;

    public string $navbarHeading = "Staff Management";

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
    public $staff_id;
    public bool $isEditMode = false;
    public string $page = 'Staff';

//    Table variables
    #[Url(history: true)]
    public $search;

    #[Url(history: true)]
    public $perPage = 5;

    #[Url(history: true)]
    public $sortedBy = 'staff_id';

    public $sortDirection = 'DESC';

    public function with(StaffManagementServices $staffServices): array
    {
        return [
            'staffs' => $staffServices->getStaffList(
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

    public function saveStaff(StaffManagementServices $staffServices): void
    {
        $staffForm = $this->validateFields();
        $staff = $staffServices->saveStaff($staffForm);

        if ($staff === true) {
            $this->dispatch('notify', type: 'success', message: 'Staff added successfully.');
        } else {
            $this->dispatch('notify', type: 'error', message: $staff);
        }

        $this->closeModal();
    }

    public function showStaff(StaffManagementServices $staffServices, $staffId): void
    {
        $staff = $staffServices->getStaffById($staffId);

        if ($staff !== null) {
            $this->staff_id = $staff->staff_id;
            $this->updateImage = $staff->image;
            $this->name = $staff->name;
            $this->cnic = $staff->cnic;
            $this->email = $staff->email;
            $this->password = $staff->password;
            $this->mobile = $staff->mobile;
            $this->address = $staff->address;
            $this->qualification = $staff->qualification;
            $this->designation = $staff->designation;

            $this->isEditMode = true;
            Flux::modal('add-' . $this->page)->show();
        }
    }

    public function updateStaff(StaffManagementServices $staffServices): void
    {
        $staffForm = $this->validateFields();
        if ($this->updateImage != null && $this->image == null) {
            $staffForm['image'] = $this->updateImage;
        }

        $staff = $staffServices->updateStaff($staffForm, $this->staff_id);

        if ($staff > 0) {
            $this->reset();
            $this->isEditMode = false;
            $this->dispatch('notify', type: 'success', message: 'Staff updated successfully.');

        }elseif($staff === false){
            $this->dispatch('notify', type: 'error', message: 'Failed to Update password for Login');
        } else {
            $this->dispatch('notify', type: 'error', message: $staff);
        }
        $this->closeModal();
    }

    #[On('delete-staff')]
    public function delete(StaffManagementServices $staffServices, $id): void
    {
        $staff = $staffServices->getStaffById($id);
        if($staff !== null){
            $this->staff_id = $staff->staff_id;
            $staff = $staffServices->deleteStaff($this->staff_id);
            if ($staff > 0) {
                $this->dispatch('notify', type: 'success', message: 'Staff deleted successfully.');
            }else{
                $this->dispatch('notify', type: 'error', message: $staff);
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
                'cnic' => ['required', 'string', 'max:16', 'unique:staffs'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'mobile' => ['required', 'string', 'min:11', 'max:11', 'unique:staffs'],
                'address' => ['required', 'string', 'max:255'],
                'qualification' => ['required', 'string', 'max:255'],
                'designation' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);
        }

    }

    public function closeModal()
    {
        $this->reset();
        $this->isEditMode = false;
        Flux::modal('add-' . $this->page)->close();
    }


}; ?>

<section>
     {{-- Staff Table--}}
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
                {{$staffs->links()}}
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
                @forelse($staffs as $staff)
                    <tr key="{{$staff->id}}">
                        <td class="p-4">{{$staff->staff_id}}</td>
                        <td class="p-4">
                            <div class="flex w-max items-center gap-2">
                                <img src="{{ asset('storage/' . $staff->image) }}"
                                     class="size-8 rounded-full object-cover" alt="Staff Image">
                                <div class="flex flex-col">
                                    <span class="text-neutral-900 dark:text-white">{{$staff->name}}</span>
                                    <span class="text-neutral-500 dark:text-white">{{$staff->mobile}}</span>
                                    <span
                                        class="text-sm text-neutral-600 opacity-85 dark:text-neutral-300">{{$staff->cnic}}</span>
                                    <span class="text-neutral-900 dark:text-white">{{$staff->address}}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">{{$staff->password}}</td>
                        <td class="p-4">{{$staff->qualification}}</td>
                        <td class="p-4"><span
                                class="inline-flex overflow-hidden rounded-radius border-success px-1 py-0.5 text-xs font-medium text-success bg-success/10">{{$staff->designation}}</span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="delete-confirmation">
                                        <flux:button variant="primary" color="rose" size="xs" class="cursor-pointer"
                                                     wire:click="$dispatch('confirm-delete',{
                                        id: {{$staff->id}},
                                        dispatchAction: 'delete-staff',
                                        heading: 'Delete Staff Data',
                                        subheading: 'You are deleting data of',
                                        name: '{{$staff->name}}',
                                        confirmButtonText: 'Delete Staff',
                                        })">
                                            <flux:icon.trash variant="solid" class="size-4"/>
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <flux:button variant="primary" color="yellow" size="xs" class="cursor-pointer"
                                                 wire:click="showStaff({{ $staff->id }})">
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
                {{$staffs->links()}}
            </div>
            <livewire:common.delete/>
        </div>
    </div>
    @include('livewire.admin.partials.teacher-staff-modal', ['isEditMode' => $isEditMode, 'page' => $page])
</section>




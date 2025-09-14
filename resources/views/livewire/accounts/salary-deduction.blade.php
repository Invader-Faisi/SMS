<?php

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[\Livewire\Attributes\Title('Deductions')]
class extends Component {
    use WithPagination;

    public string $navbarHeading = "Salary Management";

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    //    Form fields
    public string $type = '';
    public string $category = '';
    public string $name = '';
    public string $amount = '0.00';

    //    helper variables
    public $id;
    public bool $isEditMode = false;
    public string $page = 'SalaryDeduction';

    //    Table variables
    #[Url(history: true)]
    public $search;

    #[Url(history: true)]
    public $perPage = 30;

    #[Url(history: true)]
    public $sortedBy = 'id';

    public $sortDirection = 'ASC';

    public function with(\App\Services\SalaryManagementServices $salaryServices)
    {
        return [
            'deductions' => $salaryServices->getSalaryDeductionList(
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

    public function saveSalaryDeduction(\App\Services\SalaryManagementServices $salaryServices): void
    {
        $deduction = $this->validateFields();

        $salary = $salaryServices->saveSalaryDeduction($deduction);
        if ($salary === true) {
            $this->dispatch('notify', type: 'success', message: 'Salary Deduction added successfully.');
        } else {
            $this->dispatch('notify', type: 'error', message: $salary);
        }

        $this->closeModal();
    }

    public function showSalaryDeduction(\App\Services\SalaryManagementServices $salaryServices, $id): void
    {
        $salaryDeduction = $salaryServices->getSalaryDeductionById($id);

        if (!is_string($salaryDeduction)) {
            $this->id = $salaryDeduction->id;
            $this->type = $salaryDeduction->type;
            $this->category = $salaryDeduction->category;
            $this->name = $salaryDeduction->name;
            $this->amount = $salaryDeduction->amount;

            $this->isEditMode = true;
            Flux::modal('add-' . $this->page)->show();
        } else {
            $this->dispatch('notify', type: 'error', message: $salaryDeduction);
        }
    }

    public function updateSalaryDeduction(\App\Services\SalaryManagementServices $salaryServices): void
    {
        $this->validateFields();
        $salaryDeduction = $salaryServices->getSalaryDeductionById($this->id);
        if ($salaryDeduction) {
            $salaryDeduction->type = $this->type;
            $salaryDeduction->category = $this->category;
            $salaryDeduction->name = $this->name;
            $salaryDeduction->amount = $this->amount;

            $salaryDeduction->save(); // directly saving using eloquent

            $this->dispatch('notify', type: 'success', message: 'Deduction Updated successfully.');
            $this->closeModal();
        } else {
            $this->dispatch('notify', type: 'success', message: 'Deduction not Found.');
        }
    }

    #[On('delete-salary-deduction')]
    public function delete(\App\Services\SalaryManagementServices $salaryServices, $id): void
    {
        $salaryDeduction = $salaryServices->getSalaryDeductionById($id);
        if ($salaryDeduction !== null) {
            $salaryDeduction->destroy($id); // directly deleting using eloquent
            $this->dispatch('notify', type: 'success', message: 'Salary Deduction deleted successfully.');
        } else {
            $this->dispatch('notify', type: 'success', message: 'Structure not Found.');
        }
        $this->reset();
    }

    public function validateFields()
    {
        return $this->validate([
            'type' => ['required', 'string'],
            'category' => ['required', 'string'],
            'name' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);
    }

    public function closeModal(): void
    {
        $this->reset();
        $this->isEditMode = false;
        Flux::modal('add-' . $this->page)->close();
    }

}; ?>

<section class="mt-12 p-2">
    {{-- Salary Structure Table--}}
    <div class="space-y-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3">
            <div class="w-full lg:w-52">
                <flux:input wire:model.live.debounce.1000ms="search" icon="magnifying-glass" placeholder="Search orders"
                            class="text-sm"/>
            </div>
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <flux:select wire:model.live.debounce.200ms="perPage">
                    <flux:select.option value="null">Choose Per Page Record...</flux:select.option>
                    <flux:select.option>30</flux:select.option>
                    <flux:select.option>50</flux:select.option>
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
                {{$deductions->links()}}
            </div>
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
                <thead
                    class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                <tr>
                    <th scope="col" class="p-4">Entity</th>
                    <th scope="col" class="p-4">Cat</th>
                    <th scope="col" class="p-4">Name</th>
                    <th scope="col" class="p-4">Amount</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @forelse($deductions as $deduction)
                    <tr key="{{$deduction->id}}">
                        <td class="p-4">{{ucfirst($deduction->type)}}</td>
                        @php
                            $cat = strtolower($deduction->category);
                            $category = match ($cat) {
                                'first' => 'CAT-I',
                                'second' => 'CAT-II',
                                'third' => 'CAT-III',
                                'lower' => 'CAT-IV',
                                default => 'Cat',
                            };
                        @endphp
                        <td class="p-4">{{$category}}</td>
                        <td class="p-4">{{$deduction->name}}</td>
                        <td class="p-4"><span
                                class="inline-flex overflow-hidden rounded-radius border-success px-1 py-0.5 text-xs font-medium text-success bg-success/10">{{$deduction->amount}}</span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="delete-confirmation">
                                        <flux:button variant="primary" color="rose" size="xs" class="cursor-pointer"
                                                     wire:click="$dispatch('confirm-delete',{
                                        id: {{$deduction->id}},
                                        dispatchAction: 'delete-salary-deduction',
                                        heading: 'Delete Salary Deduction Data',
                                        subheading: 'You are deleting data of',
                                        name: '{{$deduction->name}}',
                                        confirmButtonText: 'Delete Salary Deduction',
                                        })">
                                            <flux:icon.trash variant="solid" class="size-4"/>
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <flux:button variant="primary" color="yellow" size="xs" class="cursor-pointer"
                                                 wire:click="showSalaryDeduction({{$deduction->id}})">
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
                {{$deductions->links()}}
            </div>
            <livewire:common.delete/>
        </div>
    </div>


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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <flux:select wire:model="name" :label="__('Deduction Type')" class="w-full">
                            <flux:select.option value="null">Select Type...</flux:select.option>
                            <flux:select.option value="Late">Late</flux:select.option>
                            <flux:select.option value="Loan">Loan</flux:select.option>
                            <flux:select.option value="Leave">Leave</flux:select.option>
                        </flux:select>
                        <flux:input type="numeric" min="0" wire:model="amount" :label="__('Amount')" class="w-full"/>
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
</section>

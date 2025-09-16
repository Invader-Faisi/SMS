<?php

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[\Livewire\Attributes\Title('Salary Structure')]
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
    public string $basic_salary = '10000';
    public string $house_allowance = '0.00';
    public string $medical_allowance = '0.00';
    public string $transport_allowance = '0.00';
    public string $other_allowance = '0.00';

    //    helper variables
    public $id;
    public bool $isEditMode = false;
    public string $page = 'SalaryStructure';

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
            'salaries' => $salaryServices->getSalaryStructureList(
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

    public function saveSalaryStructure(\App\Services\SalaryManagementServices $salaryServices): void
    {
        $structure = $this->validateFields();

        $salary = $salaryServices->saveSalaryStructure($structure);
        if ($salary === true) {
            $this->dispatch('notify', type: 'success', message: 'Salary Structure added successfully.');
        } else {
            $this->dispatch('notify', type: 'error', message: $salary);
        }

        $this->closeModal();
    }

    public function showSalaryStructure(\App\Services\SalaryManagementServices $salaryServices, $id): void
    {
        $salaryStructure = $salaryServices->getSalaryStructureById($id);

        if (!is_string($salaryStructure)) {
            $this->id = $salaryStructure->id;
            $this->type = $salaryStructure->type;
            $this->category = $salaryStructure->category;
            $this->basic_salary = $salaryStructure->basic_salary;
            $this->house_allowance = $salaryStructure->house_allowance;
            $this->medical_allowance = $salaryStructure->medical_allowance;
            $this->transport_allowance = $salaryStructure->transport_allowance;
            $this->other_allowance = $salaryStructure->other_allowance;

            $this->isEditMode = true;
            Flux::modal('add-' . $this->page)->show();
        } else {
            $this->dispatch('notify', type: 'error', message: $salaryStructure);
        }
    }

    public function updateSalaryStructure(\App\Services\SalaryManagementServices $salaryServices): void
    {
        $this->validateFields();

        $salaryStructure = $salaryServices->getSalaryStructureById($this->id);
        if ($salaryStructure) {
            $salaryStructure->type = $this->type;
            $salaryStructure->category = $this->category;
            $salaryStructure->basic_salary = $this->basic_salary;
            $salaryStructure->house_allowance = $this->house_allowance;
            $salaryStructure->medical_allowance = $this->medical_allowance;
            $salaryStructure->transport_allowance = $this->transport_allowance;
            $salaryStructure->other_allowance = $this->other_allowance;

            $salaryStructure->save(); // directly saving using eloquent

            $this->dispatch('notify', type: 'success', message: 'Structure Updated successfully.');
            $this->closeModal();
        } else {
            $this->dispatch('notify', type: 'success', message: 'Structure not Found.');
        }
    }

    #[On('delete-salary-structure')]
    public function delete(\App\Services\SalaryManagementServices $salaryServices, $id): void
    {
        $salaryStructure = $salaryServices->getSalaryStructureById($id);
        if ($salaryStructure !== null) {
            $salaryStructure->destroy($id); // directly deleting using eloquent
            $this->dispatch('notify', type: 'success', message: 'Salary Structure deleted successfully.');
        }else{
            $this->dispatch('notify', type: 'success', message: 'Structure not Found.');
        }
        $this->reset();
    }

    public function validateFields()
    {
        return $this->validate([
            'type' => ['required', 'string'],
            'category' => ['required', 'string'],
            'basic_salary' => ['required', 'numeric', 'min:10000'],
            'house_allowance' => ['required', 'numeric', 'min:0'],
            'medical_allowance' => ['required', 'numeric', 'min:0'],
            'transport_allowance' => ['required', 'numeric', 'min:0'],
            'other_allowance' => ['required', 'numeric', 'min:0'],
        ]);
    }

    public function closeModal(): void
    {
        $this->reset();
        $this->isEditMode = false;
        Flux::modal('add-' . $this->page)->close();
    }
}; ?>

<section>
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
                {{$salaries->links()}}
            </div>
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
                <thead
                    class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                <tr>
                    <th scope="col" class="p-4">Entity</th>
                    <th scope="col" class="p-4">Cat</th>
                    <th scope="col" class="p-4">Basic</th>
                    <th scope="col" class="p-4">House</th>
                    <th scope="col" class="p-4">Medical</th>
                    <th scope="col" class="p-4">Transport</th>
                    <th scope="col" class="p-4">Misc</th>
                    <th scope="col" class="p-4">Gross</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @forelse($salaries as $salary)
                    <tr key="{{$salary->id}}">
                        <td class="p-4">{{ucfirst($salary->type)}}</td>
                        @php
                            $cat = strtolower($salary->category);
                            $category = match ($cat) {
                                'first' => 'CAT-I',
                                'second' => 'CAT-II',
                                'third' => 'CAT-III',
                                'lower' => 'CAT-IV',
                                default => 'Cat',
                            };
                        @endphp
                        <td class="p-4">{{$category}}</td>
                        <td class="p-4"><span
                                class="inline-flex overflow-hidden rounded-radius border-info px-1 py-0.5 text-xs font-medium text-info bg-info/10">{{$salary->basic_salary}}</span>
                        </td>
                        <td class="p-4">{{$salary->house_allowance}}</td>
                        <td class="p-4">{{$salary->medical_allowance}}</td>
                        <td class="p-4">{{$salary->transport_allowance}}</td>
                        <td class="p-4">{{$salary->other_allowance}}</td>
                        <td class="p-4"><span
                                class="inline-flex overflow-hidden rounded-radius border-success px-1 py-0.5 text-xs font-medium text-success bg-success/10">{{$salary->gross_salary}}</span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="delete-confirmation">
                                        <flux:button variant="primary" color="rose" size="xs" class="cursor-pointer"
                                                     wire:click="$dispatch('confirm-delete',{
                                        id: {{$salary->id}},
                                        dispatchAction: 'delete-salary-structure',
                                        heading: 'Delete Salary Structure Data',
                                        subheading: 'You are deleting data of',
                                        name: '{{$salary->type}}',
                                        confirmButtonText: 'Delete Salary Structure',
                                        })">
                                            <flux:icon.trash variant="solid" class="size-4"/>
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <flux:button variant="primary" color="yellow" size="xs" class="cursor-pointer"
                                                 wire:click="showSalaryStructure({{$salary->id}})">
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
                {{$salaries->links()}}
            </div>
            <livewire:common.delete/>
        </div>
    </div>
    @include('livewire.accounts.partials.salary-structure-modal', ['isEditMode' => $isEditMode, 'page' => $page])
</section>

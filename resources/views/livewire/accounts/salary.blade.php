<?php

use App\Models\Salary;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new
#[\Livewire\Attributes\Title('Salaries')]
class extends Component {
    public string $navbarHeading = "Monthly Salaries";

    public $month;

    public $year;

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    //    form variables
    public $teacher_id = null;
    public $staff_id = null;
    public $salary_structure_id;
    public $account;
    public $payment_method;

    //    helper variables
    public $id;
    public string $page = 'Salary';
    public bool $isEditMode = false;
    public $name = null;
    public $designation = null;


    //    Table variables
    #[Url(history: true)]
    public $search;

    #[Url(history: true)]
    public $perPage = 30;

    #[Url(history: true)]
    public $sortedBy = 'id';

    public $sortDirection = 'ASC';

    public function with(\App\Services\SalaryManagementServices $salaryServices): array
    {
        return [
            'salaries' => $salaryServices->getMonthlySalaryList(
                $this->search,
                $this->perPage,
                $this->sortedBy,
                $this->sortDirection,
                $this->month,
                $this->year,
            ),
            'teachers' => $salaryServices->getTeachersList(),
            'staffs' => $salaryServices->getStaffList(),
            'structures' => $salaryServices->getSalaryStructures(),
            'deductions' => $salaryServices->getSalaryDeductions(),
        ];
    }

    public function addSalary(\App\Services\SalaryManagementServices $salaryServices): void
    {
        if ($this->teacher_id !== null && $this->staff_id !== null) {
            $this->dispatch('notify', type: 'error', message: 'Please select Teacher or Staff not both !!!');
            $this->reset(['teacher_id', 'staff_id', 'name', 'designation']);
        } elseif ($this->teacher_id === null && $this->staff_id === null) {
            $this->dispatch('notify', type: 'info', message: 'Please select Teacher or Staff First !!!');
            $this->reset(['teacher_id', 'staff_id', 'name', 'designation']);
        } else {
            if ($this->teacher_id !== null) {
                $teacher = $this->with(app(\App\Services\SalaryManagementServices::class))['teachers']
                    ->firstWhere('teacher_id', $this->teacher_id);
                if ($teacher) {
                    $this->name = $teacher->name;
                    $this->designation = $teacher->designation;
                }
            } elseif ($this->staff_id !== null) {
                $staff = $this->with(app(\App\Services\SalaryManagementServices::class))['staffs']
                    ->firstWhere('staff_id', $this->staff_id);
                if ($staff) {
                    $this->name = $staff->name;
                    $this->designation = $staff->designation;
                }
            }
            // Finally show modal
            Flux::modal('add-' . $this->page)->show();
        }
    }


    public function saveSalary(\App\Services\SalaryManagementServices $salaryServices): void
    {
        $this->validateFields();
        if ($this->teacher_id !== null && $this->staff_id === null) {
            $salary = $this->calculateSalary($this->teacher_id,$this->salary_structure_id, $this->account, $this->payment_method, $salaryServices);
            $salary['teacher_id'] = $this->teacher_id;
            $response = $salaryServices->saveSalary($salary);
            if ($response === true) {
                $this->dispatch('notify', type: 'success', message: 'Salary of teacher (' . $this->name . ') added successfully.');
            } else {
                $this->dispatch('notify', type: 'error', message: $response);
            }
        }
        if ($this->teacher_id === null && $this->staff_id !== null) {
            $salary = $this->calculateSalary($this->staff_id,$this->salary_structure_id, $this->account, $this->payment_method, $salaryServices);
            $salary['staff_id'] = $this->staff_id;
            $response = $salaryServices->saveSalary($salary);
            if ($response === true) {
                $this->dispatch('notify', type: 'success', message: 'Salary of staff (' . $this->name . ') added successfully.');
            } else {
                $this->dispatch('notify', type: 'error', message: $response);
            }
        }

        $this->closeModal();

    }

    public function showSalary(\App\Services\SalaryManagementServices $salaryServices, $id): void
    {
        $this->id = $id;
        $salary = $salaryServices->getSalary($this->id);

        if($salary)
        {
            $this->isEditMode = true;
            $this->name = $salary->staff_id === null ? $salary->teacher->name : $salary->staff->name;
            $this->designation = $salary->staff_id === null ? $salary->teacher->designation : $salary->staff->designation;
            $this->teacher_id = $salary->teacher_id;
            $this->staff_id = $salary->staff_id;
            $this->salary_structure_id = $salary->salary_structure_id;
            $this->account = $salary->account;
            $this->payment_method = $salary->payment_method;

            Flux::modal('add-' . $this->page)->show();
        }else{
            $this->closeModal();
            $this->dispatch('notify', type: 'info', message: 'Salary Not Found !!!');
        }
    }
    public function updateSalary(\App\Services\SalaryManagementServices $salaryServices): void
    {
        $salary = $salaryServices->getSalary($this->id);

        $this->validateFields();

        if ($this->teacher_id !== null && $this->staff_id === null) {
            $newSalary = $this->calculateSalary($this->teacher_id, $this->salary_structure_id, $this->account, $this->payment_method, $salaryServices);
            if($salary){
                $salary = $this->updateNewSalary($salary, $newSalary);
                $salary['teacher_id'] = $this->teacher_id;
                $salary->save();
                $this->dispatch('notify', type: 'success', message: 'Salary of teacher (' . $this->name . ') updated successfully.');
            }else{
                $this->dispatch('notify', type: 'error', message: $salary);
            }
        }
        if ($this->teacher_id === null && $this->staff_id !== null) {
            $newSalary = $this->calculateSalary($this->staff_id, $this->salary_structure_id, $this->account, $this->payment_method, $salaryServices);
            if($salary){
                $salary = $this->updateNewSalary($salary, $newSalary);
                $salary['staff_id'] = $this->staff_id;
                $salary->save();
                $this->dispatch('notify', type: 'success', message: 'Salary of Staff (' . $this->name . ') updated successfully.');
            }else{
                $this->dispatch('notify', type: 'error', message: $salary);
            }
        }

        $this->closeModal();

    }

    private function calculateSalary($id,$strcutureId, $account, $method, \App\Services\SalaryManagementServices $salaryServices): Salary
    {
        $month = now()->month;
        $structure = $salaryServices->getSalaryStructureById($strcutureId);

        $deduction = $salaryServices->getSalaryDeductionByEmployeeId($id, $month);

        $totalDeduction = optional($deduction)->sum(function ($item) {
            return (floatval($item->amount) * intval($item->multiples));
        }) ?? 0;

        $salary = new Salary();
        $newSalary = [
            'salary_structure_id' => $strcutureId,
            'gross_salary' => $structure->gross_salary,
            'total_deduction' => $totalDeduction,
            'net_salary' => $structure->gross_salary - $totalDeduction,
            'payment_date' => now()->addMonth()->startOfMonth(),
            'account' => $account,
            'payment_method' => $method,
            'status' => 'Pending',
        ];

        $salary->fill($newSalary);
        return $salary;
    }

    private function updateNewSalary(Salary $salary, Salary $newSalary): Salary
    {
        $salary->salary_structure_id = $newSalary->salary_structure_id;
        $salary->gross_salary = $newSalary->gross_salary;
        $salary->total_deduction = $newSalary->total_deduction;
        $salary->net_salary = $newSalary->net_salary;
        $salary->payment_date = $newSalary->payment_date;
        $salary->account = $newSalary->account;
        $salary->payment_method = $newSalary->payment_method;
        $salary->status = $newSalary->status;

        return $salary;
    }

    public function validateFields()
    {
        return $this->validate([
            'teacher_id' => ['nullable', 'string'],
            'staff_id' => ['nullable', 'string'],
            'salary_structure_id' => ['required', 'numeric'],
            'account' => ['nullable', 'string'],
            'payment_method' => ['required', 'string'],
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
    {{-- Salary Table--}}
    <div class="space-y-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-center w-full gap-3">
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <flux:select wire:model="staff_id" class="w-40">
                    <flux:select.option value="null">Select Staff...</flux:select.option>
                    @foreach($staffs as $staff)
                        <flux:select.option value="{{ $staff->staff_id }}">
                            {{ $staff->name }} - {{ $staff->designation }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="teacher_id" class="w-40">
                    <flux:select.option value="null">Select Teacher...</flux:select.option>
                    @foreach($teachers as $teacher)
                        <flux:select.option value="{{ $teacher->teacher_id }}">
                            {{ $teacher->name }} - {{ $teacher->designation }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:button variant="primary" color="emerald" icon="plus-circle" class="cursor-pointer"
                             wire:click="addSalary">
                    Add {{ $page }}
                </flux:button>
            </div>
        </div>
        <flux:separator variant="subtle"/>
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3">
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <flux:input wire:model.live.debounce.1000ms="search" icon="magnifying-glass" placeholder="Search orders"
                            class="text-sm"/>
                <flux:select wire:model.live.debounce.200ms="perPage">
                    <flux:select.option value="null">Choose Per Page Record...</flux:select.option>
                    <flux:select.option>50</flux:select.option>
                    <flux:select.option>100</flux:select.option>
                    <flux:select.option>200</flux:select.option>
                    <flux:select.option>500</flux:select.option>
                </flux:select>
            </div>
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <!-- Month Select -->
                <div class="relative">
                    <flux:select wire:model.live="month" class="w-40">
                        <flux:select.option value="">Select Month...</flux:select.option>
                        @foreach(range(1,12) as $m)
                            <flux:select.option value="{{ $m }}">
                                {{ date("F", mktime(0,0,0,$m,1)) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <span wire:loading wire:target="month" class="absolute right-2 top-2">
                        <flux:icon.loading class="w-4 h-4 text-blue-500 animate-spin"/>
                    </span>
                </div>

                <!-- Year Select -->
                <div class="relative">
                    <flux:select wire:model.live="year" class="w-40">
                        <flux:select.option value="">Select Year...</flux:select.option>
                        @foreach(range(now()->year, now()->year - 5) as $y)
                            <flux:select.option value="{{ $y }}">{{ $y }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <span wire:loading wire:target="year" class="absolute right-2 top-2">
                        <flux:icon.loading class="w-4 h-4 text-blue-500 animate-spin"/>
                    </span>
                </div>
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
                    <th scope="col" class="p-4">ID</th>
                    <th scope="col" class="p-4">Teacher / Staff</th>
                    <th scope="col" class="p-4">Gross</th>
                    <th scope="col" class="p-4">Deductions</th>
                    <th scope="col" class="p-4">Net</th>
                    <th scope="col" class="p-4">Month</th>
                    <th scope="col" class="p-4">Method</th>
                    <th scope="col" class="p-4">Account</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @forelse($salaries as $salary)
                    <tr key="{{$salary->id}}">

                        @if($salary->teacher_id && $salary->teacher)
                            <td class="p-4">{{ $salary->teacher->teacher_id }}</td>
                            <td class="p-4">
                                <div class="flex w-max items-center gap-2">
                                    <img src="{{ asset('storage/' . $salary->teacher->image) }}"
                                         class="size-8 rounded-full object-cover" alt="Teacher Image">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-neutral-900 dark:text-white">{{ $salary->teacher->name }}</span>
                                        <span
                                            class="text-neutral-500 dark:text-white">{{ $salary->teacher->mobile }}</span>
                                    </div>
                                </div>
                            </td>
                        @elseif($salary->staff_id && $salary->staff)
                            <td class="p-4">{{ $salary->staff->staff_id }}</td>
                            <td class="p-4">
                                <div class="flex w-max items-center gap-2">
                                    <img src="{{ asset('storage/' . $salary->staff->image) }}"
                                         class="size-8 rounded-full object-cover" alt="Staff Image">
                                    <div class="flex flex-col">
                                        <span class="text-neutral-900 dark:text-white">{{ $salary->staff->name }}</span>
                                        <span
                                            class="text-neutral-500 dark:text-white">{{ $salary->staff->mobile }}</span>
                                    </div>
                                </div>
                            </td>
                        @endif


                        <td class="p-4">{{$salary->gross_salary}}</td>
                        <td class="p-4">{{$salary->total_deduction}}</td>
                        <td class="p-4"><span
                                class="inline-flex overflow-hidden rounded-radius border-success px-1 py-0.5 text-xs font-medium text-success bg-success/10">{{$salary->net_salary}}</span>
                        </td>
                        <td class="p-4">{{\Carbon\Carbon::parse($salary->payment_date)->format('F Y')}}</td>
                        <td class="p-4">{{$salary->payment_method}}</td>
                        <td class="p-4">{{$salary->account ?? '-'}}</td>
                        <td class="p-4">
                            @php
                                $status = strtolower($salary->status);
                                $color = match ($status) {
                                    'paid' => 'green',
                                    'pending' => 'red',
                                    default => 'yellow',
                                };
                            @endphp
                            <flux:badge color="{{ $color }}" size="sm"
                                        inset="top bottom">{{ ucfirst($salary->status) }}</flux:badge>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex gap-2">
                                    @if($salary->status !== 'Paid')
                                    <flux:button variant="primary" color="yellow" size="xs" class="cursor-pointer"
                                                 tooltip="Edit Salary"
                                                 wire:click="showSalary({{ $salary->id }})">
                                        <flux:icon.pencil variant="solid" class="size-4"/>
                                    </flux:button>

                                    <flux:button variant="primary" color="green" size="xs" class="cursor-pointer"
                                                 tooltip="Pay Salary" href="{{ route('salary-slip', $salary->id) }}">
                                        <flux:icon.arrow-up-on-square-stack variant="solid" class="size-4"/>
                                    </flux:button>
                                    @else
                                        <flux:button variant="primary" color="indigo" size="xs" class="cursor-pointer"
                                                     tooltip="Salary Paid" href="{{ route('salary-slip', $salary->id) }}">
                                            <flux:icon.arrow-up-on-square-stack variant="solid" class="size-4"/>
                                        </flux:button>
                                    @endif
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

    {{--    salary model--}}
    <flux:modal name="add-{{ $page }}" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div class="text-center text-indigo-300 font-bold">
                {{ $isEditMode ? 'Update '.$page.' For ('.$name. ') '.$designation : 'Add New '.$page. ' For ('.$name. ') '.$designation}}
            </div>
            <form wire:submit.prevent="{{ $isEditMode ? 'update'.$page : 'save'.$page }}">
                <div class="space-y-3">
                    <div class="flex flex-col space-y-4">
                        <flux:select wire:model="salary_structure_id" :label="__('Structure')" class="w-40">
                            <flux:select.option value="null">Select Structure...</flux:select.option>
                            @foreach($structures as $structure)
                                @if(($staff_id !== null && $structure->type === 'staff') ||
                                    ($teacher_id !== null && $structure->type === 'teacher'))
                                    <flux:select.option value="{{ $structure->id }}">
                                        {{ ucfirst($structure->type) }} -
                                        {{ ucfirst($structure->category)}} - B
                                        {{ $structure->basic_salary }} - H -
                                        {{ $structure->house_allowance }} - M -
                                        {{ $structure->medical_allowance }} - T -
                                        {{ $structure->transport_allowance }} - O -
                                        {{ $structure->other_allowance }}
                                    </flux:select.option>
                                @endif
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="account" :label="__('Account Number')" class="w-28"/>
                        <flux:select wire:model="payment_method" :label="__('Method')" class="w-40">
                            <flux:select.option value="null">Select Method...</flux:select.option>
                            <flux:select.option value="Cash">Cash</flux:select.option>
                            <flux:select.option value="Cheque">Cheque</flux:select.option>
                            <flux:select.option value="Bank Transfer">Bank Transfer</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <flux:button variant="filled" class="cursor-pointer"
                                 wire:click="closeModal()">Cancel
                    </flux:button>
                    <flux:button type="submit" wire:loading.attr="disabled" wire:target="salary_structure_id" variant="primary" color="blue" class="cursor-pointer">
                        {{ $isEditMode ? 'Update' : 'Save' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>

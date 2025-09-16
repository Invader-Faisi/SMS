<?php

use Carbon\Carbon;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[\Livewire\Attributes\Title('Fees')]
class extends Component {
    use WithPagination;

    public string $navbarHeading = "Monthly Fees";

    public $month;
    public $year;

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    //    helper variables

    public $id;
    public string $page = 'Fees';
    public $studentId;
    public $structure = [];
    public array $oneTimeFees = [];
    public array $oneTimeCounts = [];
    public array $oneTimeMonth = [];


    //    Table variables
    #[Url(history: true)]
    public $search;

    #[Url(history: true)]
    public $perPage = 30;

    #[Url(history: true)]
    public $sortedBy = 'id';

    public $sortDirection = 'ASC';

    public function with(\App\Services\AccountManagementServices $feeServices): array
    {
        return [
            'fees' => $feeServices->getMonthlyFeeList(
                $this->search,
                $this->perPage,
                $this->sortedBy,
                $this->sortDirection,
                $this->month,
                $this->year,
            ),
        ];
    }

    public function generateFees(\App\Services\AccountManagementServices $feeServices): void
    {
        $this->validate([
            'month' => 'required|integer|min:1|max:12',
        ]);

        $month = $this->month;
        $year = now()->year;

        $response = $feeServices->generateFees($month, $year);
        if ($response === true) {
            $this->dispatch('notify', type: 'success', message: 'Fees successfully generated for the month');
            $this->reset();
        } else {
            $this->dispatch('notify', type: 'error', message: $response);
        }
    }

    public function showOneTime($feeId, $class, \App\Services\AccountManagementServices $feeServices): void
    {
        $fee = $feeServices->getMonthlyFeeById($feeId);
        $this->studentId = $fee->student_id;
        $this->oneTimeFees = [];
        $this->oneTimeCounts = [];
        $this->oneTimeMonth = [];
        $this->structure = $feeServices->getOneTimeFeeStructure($class);
        Flux::modal('one-time-fee-modal')->show();
    }

    public function showAnnual($feeId, $class, \App\Services\AccountManagementServices $feeServices): void
    {
        $fee = $feeServices->getMonthlyFeeById($feeId);
        $this->studentId = $fee->student_id;
        $this->oneTimeFees = [];
        $this->oneTimeCounts = [];
        $this->oneTimeMonth = [];
        $this->structure = $feeServices->getAnnualStructure($class);
        Flux::modal('one-time-fee-modal')->show();
    }

    public function saveFees(\App\Services\AccountManagementServices $feeServices): void
    {
        $studentId = $this->studentId;
        $year = now()->year;
        $month = $this->oneTimeMonth[array_key_first($this->oneTimeMonth)] ?? now()->month;

        foreach ($this->oneTimeFees as $id => $checked) {
            if ($checked) {
                $structure = $feeServices->getFeeStructureById($id);
                if ($structure) {
                    $count = $this->oneTimeCounts[$id] ?? 1;
                    $amount = $structure->amount * $count;

                    $fee = [
                        'student_id'      => $studentId,
                        'fee_structure_id'=> $structure->id,
                        'amount'          => $amount,
                        'due_date'        => Carbon::create($year, $month, 10),
                        'pending_amount'  => $amount,
                        'status'          => 'pending',
                    ];

                    $response = $feeServices->addOneTimeFee($fee);

                    if ($response) {
                        $this->dispatch('notify', type: 'success', message: "{$structure->name} added for {$studentId}");
                    } else {
                        $this->dispatch('notify', type: 'error', message: $response);
                    }
                }
            }
        }

        Flux::modal('one-time-fee-modal')->close();
        $this->reset();
    }


}; ?>

<section>
    {{-- Fee Table--}}
    <div class="space-y-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3">
            <div class="w-full lg:w-52">
                <flux:input wire:model.live.debounce.1000ms="search" icon="magnifying-glass" placeholder="Search orders"
                            class="text-sm"/>
            </div>
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <flux:select wire:model.live.debounce.200ms="perPage">
                    <flux:select.option value="null">Choose Per Page Record...</flux:select.option>
                    <flux:select.option>50</flux:select.option>
                    <flux:select.option>100</flux:select.option>
                    <flux:select.option>200</flux:select.option>
                    <flux:select.option>500</flux:select.option>
                </flux:select>
                <flux:select wire:model="month" class="w-40">
                    <flux:select.option value="null">Select Month...</flux:select.option>
                    @foreach(range(1,12) as $m)
                        <flux:select.option value="{{ $m }}">
                            {{ date("F", mktime(0,0,0,$m,1)) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:button variant="primary" color="emerald" icon="arrow-path" class="cursor-pointer"
                             wire:click="generateFees">
                    Generate Fees
                </flux:button>
            </div>
        </div>

        <div
            class="overflow-hidden w-full overflow-x-auto rounded-radius border border-outline dark:border-outline-dark">
            <div class="mx-2 mb-2 mt-2">
                {{$fees->links()}}
            </div>
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
                <thead
                    class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                <tr>
                    <th scope="col" class="p-4">Student</th>
                    <th scope="col" class="p-4">Class</th>
                    <th scope="col" class="p-4">Fee</th>
                    <th scope="col" class="p-4">Month</th>
                    <th scope="col" class="p-4">Due Date</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                @php
                    $groupedFees = $fees->getCollection()
                        ->groupBy(function ($fee) {
                            return \Carbon\Carbon::parse($fee->due_date)->format('F Y'); // group by month
                        });
                @endphp
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @forelse($groupedFees as $monthName => $monthFees)
                    @php
                        $groupedByClass = $monthFees->groupBy(fn($fee) => $fee->student->class);
                    @endphp

                    @foreach($groupedByClass as $className => $classFees)
                        <tr>
                            <td colspan="7" class="p-4 bg-gray-400 dark:bg-gray-700 font-semibold text:lg text-white text-center">
                                {{ $monthName }} Fee for Class: {{ $className }}
                            </td>
                        </tr>

                        @foreach($classFees as $fee)
                            <tr key="{{ $fee->id }}">
                                <td class="p-4">
                                    <div class="flex w-max items-center gap-2">
                                        <img src="{{ asset('storage/' . $fee->student->image) }}"
                                             class="size-8 rounded-full object-cover" alt="Student Image">
                                        <div class="flex flex-col">
                                            <span class="text-neutral-900 dark:text-white">{{ $fee->student->student_id }}</span>
                                            <span class="text-neutral-900 dark:text-white">{{ $fee->student->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">{{ $fee->student->class }}</td>
                                <td class="p-4">{{ $fee->amount }}</td>
                                <td class="p-4">{{ Carbon::parse($fee->due_date)->format('F Y') }}</td>
                                <td class="p-4">{{ Carbon::parse($fee->due_date)->format('d-m-Y') }}</td>
                                <td class="p-4">
                                    @php
                                        $status = strtolower($fee->status);
                                        $color = match ($status) {
                                            'paid' => 'green',
                                            'pending' => 'red',
                                            default => 'yellow',
                                        };
                                    @endphp
                                    <flux:badge color="{{ $color }}" size="sm" inset="top bottom">{{ ucfirst($fee->status) }}</flux:badge>
                                </td>
                                <td class="p-4">
                                    @if($fee->status !== 'paid')
                                    <div class="flex gap-2">
                                        <flux:button variant="primary" color="yellow" size="xs" icon="plus-circle" class="cursor-pointer"
                                                     wire:click="showOneTime({{ $fee->id }}, '{{ $fee->student->class }}')">
                                            One Time
                                        </flux:button>
                                        <flux:button variant="primary" color="emerald" size="xs" icon="plus-circle" class="cursor-pointer"
                                                     wire:click="showAnnual({{ $fee->id }}, '{{ $fee->student->class }}')">
                                            Annual
                                        </flux:button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    {{-- Month Totals Row --}}
                    @php
                        $monthTotal = $monthFees->sum('amount');
                        $paidOrPartial = $monthFees->whereIn('status', ['paid', 'partial'])->sum('amount');
                        $monthPending = $monthTotal - $paidOrPartial;
                    @endphp
                    <tr class="bg-pink-200 dark:bg-gray-800 font-bold dark:text-blue-400">
                        <td colspan="2" class="p-4 text-right">Total Amount of {{ $monthName }} :</td>
                        <td class="p-4">{{ $monthTotal }}</td>
                        <td colspan="2" class="p-4 text-left">Total Pending for {{ $monthName }} :</td>
                        <td class="p-4">{{ $monthPending }}</td>
                        <td></td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center">No data found!!!</td>
                    </tr>
                @endforelse
                </tbody>

            </table>
            <flux:separator variant="subtle"/>
            <div class="mx-2 mb-2 mt-2">
                {{$fees->links()}}
            </div>
            <livewire:common.delete/>
        </div>
    </div>
    <flux:modal name="one-time-fee-modal" class="w-full max-w-2xl">
        <div class="space-y-6">
            <h2 class="text-lg font-bold">Assign One-Time Fees</h2>
            <div class="space-y-3">
                @foreach($structure as $struct)
                    <div class="flex flex-col space-y-4">
                        <flux:field variant="inline" class="flex items-center gap-2">
                            <flux:checkbox wire:model="oneTimeFees.{{ $struct->id }}" class="cursor-pointer"/>
                            <flux:label>{{ $struct->name }} ({{ $struct->amount }})</flux:label>
                        </flux:field>
                        <flux:select wire:model="oneTimeMonth.{{ $struct->id }}" class="w-40">
                            <flux:select.option value="null">Select Month...</flux:select.option>
                            @foreach(range(1,12) as $m)
                                <flux:select.option value="{{ $m }}">
                                    {{ date("F", mktime(0,0,0,$m,1)) }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input type="number" min="1" wire:model="oneTimeCounts.{{ $struct->id }}" :label="__('Multiple')"
                                    class="w-28"/>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <flux:button variant="filled" class="cursor-pointer"
                             x-on:click="$flux.modal('one-time-fee-modal').close()">Cancel
                </flux:button>
                <flux:button variant="primary" color="blue" class="cursor-pointer" wire:click="saveFees">Save
                </flux:button>
            </div>
        </div>
    </flux:modal>

</section>

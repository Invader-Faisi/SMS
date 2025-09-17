<?php

use Barryvdh\DomPDF\Facade\Pdf;
use Flux\Flux;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;


new
#[Title('Fee Slips')]
class extends Component {
    use WithPagination;

    public string $navbarHeading = "Fee Slips";

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    //    helper variables

    public $id;
    public $due_date;
    public $amount;
    public $studentId;
    public $studentName;
    public $studentImage;


    public $studentFees = [];
    public $feeMonth;
    public $totalFee = 0;
    public $paidFee = 0;

    //    Table variables
    #[Url(history: true)]
    public $search;

    #[Url(history: true)]
    public $perPage = 30;

    #[Url(history: true)]
    public $sortedBy = 'id';

    public $sortDirection = 'ASC';

    public $month;

    public $year;

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

    public function showFee(\App\Services\AccountManagementServices $feeServices, $id): void
    {
        $fee = $feeServices->getMonthlyFeeById($id);
        if ($fee) {
            $this->id = $fee->id;
            $this->studentId = $fee->student_id;
            $this->amount = $fee->amount;
            $this->due_date = Carbon::parse($fee->due_date)->format('Y-m-d');

            Flux::modal('update-fee-modal')->show();
        }
    }

    public function updateFees(\App\Services\AccountManagementServices $feeServices): void
    {
        $this->validate([
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $fee = $feeServices->getMonthlyFeeById($this->id);
        if ($fee) {
            $fee->due_date = $this->due_date;
            $fee->amount = $this->amount;
            $fee->pending_amount = $this->amount;
            $fee->save();

            $this->dispatch('notify', type: 'success', message: 'Fee Updated successfully.');
            Flux::modal('update-fee-modal')->close();
        } else {
            $this->dispatch('notify', type: 'success', message: 'Fee not Found.');
        }
        $this->reset();
    }

    public function payFee($studentId, $month, \App\Services\AccountManagementServices $feeServices): void
    {
        $this->studentId = $studentId;
        $this->feeMonth = $month;
        $fees = $feeServices->getMonthlyFeesByStudentAndMonth($studentId, $month);
        //dd($fees);
        if ($fees && $fees->count() > 0) {
            $this->studentFees = $fees;
            $this->studentName = $fees->first()->student->name;
            $this->studentImage = $fees->first()->student->image;
            $this->totalFee = $fees->sum('pending_amount');
            Flux::modal('pay-fee-modal')->show();
        } else {
            $this->dispatch('notify', type: 'warning', message: 'No Fees Found for the selected month.');
        }
    }

    public function feePayment($studentId, $month, \App\Services\AccountManagementServices $feeServices): void
    {
        $this->validate([
            'paidFee' => ['required', 'numeric', 'min:0'],
        ]);

        $fees = $feeServices->getMonthlyFeesByStudentAndMonth($studentId, $month);
        if ($fees && $fees->count() > 0) {
            $totalFee = $fees->sum('pending_amount');
            if ($this->paidFee > $totalFee) {
                $this->dispatch('notify', type: 'warning', message: 'Paid amount exceeds total fee.');
                return;
            }

            $remainingAmount = $this->paidFee;

            foreach ($fees as $fee) {
                if ($remainingAmount <= 0) {
                    break;
                }

                if ($fee->status == 'paid') {
                    continue; // Already paid fees
                }

                if ($remainingAmount >= $fee->pending_amount) {
                    // Full payment for this fee
                    $remainingAmount -= $fee->pending_amount;
                    $fee->pending_amount = 0;
                    $fee->status = 'paid';
                } else {
                    // Partial payment for this fee
                    $fee->pending_amount = $fee->pending_amount - $remainingAmount;
                    $fee->status = 'partial';
                    $remainingAmount = 0;
                }
                $fee->save();
            }

            $student = $fees->first()->student;
            $pdf = Pdf::loadView('livewire.accounts.partials.fee-slip', [
                'student' => $student,
                'fees' => $fees,
                'paidFee' => $this->paidFee,
                'date' => now()->format('d-m-Y'),
                'totalAmount' => $fees->sum('amount'),
                'totalPending' => $fees->sum('pending_amount'),
            ]);

            $fileName = 'fee-slip-' . $student->student_id . '-' . $month . '-' . now()->timestamp . '.pdf';
            $home = getenv('USERPROFILE') ?: getenv('HOME');
            $desktop = $home . DIRECTORY_SEPARATOR . 'Desktop' . DIRECTORY_SEPARATOR . 'pay slips' . DIRECTORY_SEPARATOR;

            if (!file_exists($desktop)) {
                mkdir($desktop, 0777, true);
            }

            $pdf->save($desktop . $fileName);

            $this->dispatch('notify', type: 'success', message: 'Fee Payment processed successfully.');
            Flux::modal('pay-fee-modal')->close();
        } else {
            $this->dispatch('notify', type: 'warning', message: 'No Fees Found for the selected month.');
        }
        $this->reset();
    }
}; ?>

<section>
    {{-- Fee Table--}}
    <div class="space-y-2">
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
                {{$fees->links()}}
            </div>
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
                <thead
                    class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                <tr>
                    <th scope="col" class="p-4">Fee Name</th>
                    <th scope="col" class="p-4">Amount</th>
                    <th scope="col" class="p-4">Due Date</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                @php
                    $groupedFees = $fees->count() > 0
                        ? $fees->getCollection()->groupBy(fn($fee) => $fee->student->student_id . '-' . \Carbon\Carbon::parse($fee->due_date)->format('F Y'))
                        : collect();
                @endphp
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @forelse($groupedFees as $key => $studentMonthFees)
                    @php
                        $firstFee = $studentMonthFees->first();
                    @endphp

                    @if($firstFee && $firstFee->student)
                        @php
                            $student = $firstFee->student;
                            $monthName = \Carbon\Carbon::parse($firstFee->due_date)->format('F Y');
                            $totalAmount = $studentMonthFees->sum('amount');
                            $pendingAmount = $studentMonthFees->sum('pending_amount');
                        @endphp

                        {{-- Student + Month Header --}}
                        <tr class="bg-gray-200 dark:bg-gray-700 font-bold">
                            <td colspan="5" class="p-4">
                                <div class="flex justify-between items-center">
                                    <span>{{ $student->name }} ({{ $student->class }}) - {{ $monthName }}</span>
                                    <flux:button variant="primary" color="cyan" size="xs" icon="plus-circle"
                                                 class="cursor-pointer"
                                                 wire:click="payFee('{{ $student->student_id }}', '{{ $monthName }}')">
                                        Fee Payment
                                    </flux:button>
                                </div>
                            </td>
                        </tr>

                        {{-- Fee Entries --}}
                        @foreach($studentMonthFees as $fee)
                            <tr>
                                <td class="p-4">{{ $fee->feeStructure->name }}</td>
                                <td class="p-4">{{ $fee->amount }}</td>
                                <td class="p-4">{{ \Carbon\Carbon::parse($fee->due_date)->format('d-m-Y') }}</td>
                                <td class="p-4">
                                    @php
                                        $status = strtolower($fee->status);
                                        $color = match ($status) {
                                            'paid' => 'green',
                                            'pending' => 'red',
                                            default => 'yellow',
                                        };
                                    @endphp
                                    <flux:badge color="{{ $color }}" size="sm"
                                                inset="top bottom">{{ ucfirst($fee->status) }}</flux:badge>
                                </td>
                                <td class="p-4">
                                    @if(in_array($fee->status, ['pending', 'partial']))
                                        <flux:button variant="primary" color="yellow" size="xs" icon="pencil"
                                                     class="cursor-pointer"
                                                     wire:click="showFee({{ $fee->id }})">Edit
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        {{-- Totals --}}
                        <tr class="bg-gray-100 font-bold">
                            <td class="p-4 text-right">Total</td>
                            <td class="p-4">{{ $totalAmount }}</td>
                            <td class="p-4 text-right">Pending</td>
                            <td class="p-4">{{ $pendingAmount }}</td>
                            <td></td>
                        </tr>
                    @endif
                @empty
                    <flux:callout variant="danger" icon="x-circle" heading="No Record Found !!!"/>
                @endforelse
                </tbody>
            </table>

            <flux:separator variant="subtle"/>
            <div class="mx-2 mb-2 mt-2">
                {{$fees->links()}}
            </div>
        </div>
    </div>

    {{-- Fee Edit Modal --}}
    <flux:modal name="update-fee-modal" class="w-full max-w-lg">
        <div class="space-y-6">
            <h2 class="text-lg font-bold">Update Fee</h2>
            <div class="space-y-3">
                <flux:input label="Due Date" type="date" wire:model="due_date" class="w-full"/>
                <flux:input label="Amount" type="number" wire:model="amount" class="w-full"/>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <flux:button variant="filled" class="cursor-pointer"
                             x-on:click="$flux.modal('update-fee-modal').close()">Cancel
                </flux:button>
                <flux:button variant="primary" color="blue" class="cursor-pointer" wire:click="updateFees">Update
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Fee Payment Modal --}}
    <flux:modal name="pay-fee-modal">
        <article
            class="group flex rounded-radius max-w-sm flex-col overflow-hidden border border-outline bg-surface-alt text-on-surface dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark">
            <!-- Image -->
            <div class="h-44 md:h-64 overflow-hidden">
                <img
                    src="{{ $studentImage && file_exists(storage_path('app/public/' . $studentImage))
                            ? asset('storage/' . $studentImage)
                            : asset('storage/users/students/student.png') }}"
                    class="object-contain transition duration-700 ease-out group-hover:scale-105"
                    alt="Student"
                />
            </div>

            <!-- Content -->
            <div class="flex flex-col gap-4 p-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row gap-4 md:gap-12 justify-between">
                    <!-- Title & Rating -->
                    <div class="flex flex-col">
                        <h3 class="text-lg lg:text-xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong"
                            aria-describedby="productDescription">{{$studentName}}</h3>
                    </div>
                    <span class="text-lg"><span class="sr-only">Total Fee</span>Rs : {{$totalFee}}</span>
                </div>
                @forelse ($studentFees as $fee)
                    <div class="flex justify-between">
                        <span class="text-sm">{{$fee->feeStructure->name}}</span>
                        <span class="text-sm">Rs : {{$fee->pending_amount}}</span>
                        <span class="text-sm">{{Carbon::parse($fee->due_date)->format('d-m-Y')}}</span>
                    </div>
                @empty

                @endforelse
                <flux:input label="Paid Fee" type="number" wire:model="paidFee" class="w-full"/>
                <!-- Button -->

                <div class="flex justify-end gap-2 mt-4">
                    <flux:button variant="filled" class="cursor-pointer"
                                 x-on:click="$flux.modal('pay-fee-modal').close()">Cancel
                    </flux:button>
                    <flux:button variant="primary" color="emerald" icon="check" class="cursor-pointer"
                                 wire:click="feePayment('{{ $studentId }}', '{{ $feeMonth }}')">
                             <span wire:loading>
                                <flux:icon.loading class="animate-spin mr-2"/>
                            </span>
                        Paid
                    </flux:button>
                </div>
            </div>
        </article>
    </flux:modal>

</section>


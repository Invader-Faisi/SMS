<?php

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new
#[\Livewire\Attributes\Title('Salary Slip')]
class extends Component {

    public string $navbarHeading = 'Salary Slip';

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    public $salary_id;
    public $salary;
    public $deductions;

    public function mount(string $id): void
    {
        $this->salary_id = $id;
        $this->loadSalary();

    }

    private function loadSalary(): void
    {
        $this->salary = app(\App\Services\SalaryManagementServices::class)->getSalary($this->salary_id);

        $employeeId = $this->salary->teacher_id !== null
            ? $this->salary->teacher->teacher_id
            : $this->salary->staff->staff_id;

        $month = \Carbon\Carbon::parse($this->salary->payment_date)->subMonth();

        $this->deductions = app(\App\Services\SalaryManagementServices::class)
            ->getSalaryDeductionByEmployeeId($employeeId, $month);
    }

    public function paySalary(\App\Services\SalaryManagementServices $salaryServices, $id): void
    {
        $salary = $salaryServices->getSalary($id);
        $employeeId = $this->salary->teacher_id !== null
            ? $this->salary->teacher->teacher_id
            : $this->salary->staff->staff_id;

        $month = \Carbon\Carbon::parse($this->salary->payment_date)->subMonth();
        $deductions = $salaryServices->getSalaryDeductionByEmployeeId($employeeId, $month);
        if ($salary) {
            $salary->status = 'Paid';
            $salary->save();

            $pdf = Pdf::loadView('livewire.accounts.partials.salary-payment-slip', [
                'salary' => $salary,
                'deductions' => $deductions,
                'date' => now()->format('d-m-Y'),
            ]);

            $fileName = 'salary-slip-' . $employeeId . '-' . now()->format('F Y') . '-' . now()->timestamp . '.pdf';
            $home = getenv('USERPROFILE') ?: getenv('HOME');
            $desktop = $home . DIRECTORY_SEPARATOR . 'Desktop' . DIRECTORY_SEPARATOR . 'salary slips' . DIRECTORY_SEPARATOR;

            if (!file_exists($desktop)) {
                mkdir($desktop, 0777, true);
            }

            $pdf->save($desktop . $fileName);
            $this->dispatch('refresh')->to(self::class);
            $this->dispatch('notify', type: 'success', message: 'Salary Paid Successfully.');

        } else {
            $this->dispatch('notify', type: 'success', message: 'Salary not found!!!.');
        }
    }

    #[On('refresh')]
    public function refresh(): void
    {
        $this->loadSalary();
    }

}; ?>

<section class="w-full">
    <div class="space-y-2">
        <article
            class="group grid grid-cols-1 md:grid-cols-8 rounded-radius overflow-hidden border border-outline bg-surface-alt text-on-surface dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark w-full"
        >
            <!-- image -->
            <div class="col-span-3 overflow-hidden">
                <img
                    src="{{ $salary->teacher && $salary->teacher->image
                            ? asset('storage/' . $salary->teacher->image)
                            : ($salary->staff && $salary->staff->image
                                ? asset('storage/' . $salary->staff->image)
                                : asset('storage/users/students/student.png')) }}"
                    class="h-64 md:h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                    alt="Employee Picture"
                />
            </div>

            <!-- body -->
            <div class="flex flex-col justify-center p-2 col-span-5">
                <!-- Salary Body -->
                <div class="flex flex-col p-6 col-span-5 space-y-6">
                    <!-- Month -->
                    <div class="text-center">
                        <h2 class="text-xl font-bold">Salary Slip
                            - {{\Carbon\Carbon::parse($salary->payment_date)->format('F Y')}}</h2>
                    </div>

                    <!-- Employee Info Row -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div><strong>Name:</strong> {{$salary->teacher->name ?? $salary->staff->name}}</div>
                        <div><strong>Mobile:</strong> {{$salary->teacher->mobile ?? $salary->staff->mobile}}</div>
                        <div><strong>Address:</strong> {{$salary->teacher->address ?? $salary->staff->address}}</div>
                        <div>
                            <strong>Designation:</strong> {{$salary->teacher->designation ?? $salary->staff->designation}}
                        </div>
                    </div>

                    <!-- Salary Breakdown -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Column 1: Basic & Allowances -->
                        <div class="border rounded p-4 space-y-2">
                            <h3 class="font-semibold text-lg">Salary</h3>
                            <div class="flex justify-between"><span>Basic Pay</span>
                                <span>{{$salary->structure->basic_salary}}</span></div>
                            <div class="flex justify-between"><span>House Allowance</span>
                                <span>{{$salary->structure->house_allowance ?? '-'}}</span></div>
                            <div class="flex justify-between"><span>Medical Allowance</span>
                                <span>{{$salary->structure->medical_allowance ?? '-'}}</span></div>
                            <div class="flex justify-between"><span>Transport Allowance</span>
                                <span>{{$salary->structure->transport_allowance ?? '-'}}</span></div>
                            <div class="flex justify-between"><span>Other Allowance</span>
                                <span>{{$salary->structure->other_allowance ?? '-'}}</span></div>

                            <!-- Gross Salary -->
                            <div class="border-t pt-2 font-bold flex justify-between">
                                <span>Gross Salary</span> <span>{{$salary->gross_salary}}</span>
                            </div>
                        </div>

                        <!-- Column 2: Deductions -->
                        <div class="border rounded p-4 space-y-2">
                            <h3 class="font-semibold text-lg">Deductions</h3>
                            @foreach($deductions as $deduction)
                                <div class="flex justify-between"><span>{{$deduction->name}}</span>
                                    <span>{{$deduction->amount * $deduction->multiples}}</span></div>
                            @endforeach
                            <!-- Net Salary -->
                            <div class="border-t pt-2 font-bold flex justify-between">
                                <span>Net Salary</span> <span>{{$salary->net_salary}}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <div class="flex justify-end">
                        @if($salary->status !== 'Paid')
                            <button
                                class="px-6 py-2 rounded bg-green-600 text-white font-semibold hover:bg-green-700 cursor-pointer"
                                wire:click="paySalary({{$salary->id}})">
                                Paid
                            </button>
                        @else
                            <span
                                class="w-fit inline-flex overflow-hidden rounded-radius border border-info bg-surface text-xs font-medium text-info dark:border-info dark:bg-surface-dark dark:text-info">
                                <span class="flex items-center gap-1 bg-info/10 px-2 py-1 dark:bg-info/10">
                                    <span class="size-1.5 rounded-full bg-info dark:bg-info"></span>
                                    Paid on {{\Carbon\Carbon::parse($salary->updated_at)->format('d-m-Y')}}
                                </span>
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </article>
    </div>
</section>


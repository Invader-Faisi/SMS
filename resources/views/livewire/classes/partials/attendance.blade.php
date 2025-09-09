<?php

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Carbon\Carbon;

new 
#[Title('Class Attendance')]
class extends Component {

    public string $class = '';
    public $students; // all students of the class
    public $attendance; // attendance collection

    public string $selectedDate; 
    public string $selectedMonth; // format: Y-m
    public array $monthDates = []; // all dates of selected month

    public function mount(string $id): void
    {
        $this->class = $id;
        $this->selectedDate = now()->toDateString();
        $this->selectedMonth = now()->format('Y-m');

        $this->generateMonthDates();
        $this->loadStudents();
        $this->loadAttendance();
    }

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->class,
        ]);
    }

    // Load all students of the class (even if no attendance exists)
    private function loadStudents(): void
    {
        $this->students = app(\App\Services\StudentManagementServices::class)
            ->getStudentsByClass($this->class,'');
    }

    // Load attendance for selected month
    public function loadAttendance(): void
    {
        $this->selectedMonth = Carbon::parse($this->selectedDate)->format('Y-m');
        $this->generateMonthDates();

        $this->attendance = app(\App\Services\StudentManagementServices::class)
            ->getAttendanceForClassByMonth($this->class, $this->selectedMonth);
    }

    private function generateMonthDates(): void
    {
        $start = Carbon::parse($this->selectedMonth . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $this->monthDates = [];
        for ($date = $start; $date->lte($end); $date->addDay()) {
            $this->monthDates[] = $date->toDateString();
        }
    }
};
?>

<section class="mt-12 p-2">
    <div class="space-y-4">
        {{-- Month Selector --}}
        <div class="flex flex-col sm:flex-row justify-between items-center w-full gap-2">
            <div class="w-40">
                <flux:input type="date" wire:model="selectedDate" class="w-full" />
            </div>
            <div class="flex items-center gap-2">
                <flux:button icon="check" wire:click="loadAttendance" variant="primary" color="blue" class="cursor-pointer">
                    Load Attendance
                </flux:button>
                <span wire:loading wire:target="loadAttendance">
                    <flux:icon.loading />
                </span>
            </div>
        </div>


        {{-- Attendance Calendar Table --}}
        <div class="overflow-x-auto rounded-radius border border-outline dark:border-outline-dark mt-4">
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark border-collapse">
                <thead class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                    <tr>
                        <th class="p-2 sticky left-0 bg-surface-alt z-10">Student</th>
                        @foreach($monthDates as $date)
                            <th class="p-2 text-center">{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($students ?? collect() as $student)
                        <tr>
                            {{-- Student Column --}}
                            <td class="p-2 sticky left-0 bg-white dark:bg-gray-800 flex items-center gap-2 z-10">
                                <img src="{{ asset('storage/' . $student->image) }}" class="w-8 h-8 rounded-full object-cover" alt="Student Image">
                                <span>{{ $student->name }}</span>
                            </td>

                            {{-- Attendance Columns --}}
                            @foreach($monthDates as $date)
                                @php
                                    $attendanceRecord = $attendance->firstWhere('student_id', $student->student_id)?->attendances->firstWhere('date', $date);
                                @endphp
                                <td class="p-2 text-center">
                                    {{ $attendanceRecord->status ?? 'Not Marked' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($monthDates) + 1 }}" class="p-4 text-center">No students found for this class.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>


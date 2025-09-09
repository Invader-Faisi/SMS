<?php

use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Illuminate\Database\Eloquent\Collection;

new 
#[Title('Attendance')]
class extends Component {


    public ?Collection $students = null;
    public array $attendanceData = [];
    public $studentAttendance = null;

    #[Url(history: true)]
    public $search;

    // Helping variables
    public $searchStudents = '';
    public string $class_id = '';
    public string $searchDate;
    
    public string $navbarHeading = "Attendance Management";

    public function mount()
    {
        $this->searchDate = now()->toDateString();
        $this->loadAttendance();
    }
    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    public function with(\App\Services\ClassManagementServices $classServices): array
    {
        return [
            'classes' => $classServices->getClassesList($this->search),
        ];
    }

    public function loadAttendance()
    {
        $studentServices = app(\App\Services\StudentManagementServices::class);
        $this->studentAttendance = $studentServices->getClassAttendance($this->searchDate)->flatten();
    }

    public function getStudentsOfClass(\App\Services\StudentManagementServices $studentServices)
    {
        $this->class_id = $this->searchStudents;

        if ($this->class_id !== null) {
            $this->students = $studentServices->getStudentsByClass($this->class_id, '');
            foreach ($this->students as $student) {
                $this->attendanceData[$student->student_id] = [
                    'student_id' => $student->student_id,
                    'class_id'   => $this->class_id,
                    'date'       => now()->toDateString(),   
                    'status'     => 'Present',               
                    'remarks'    => '',
                    'save'       => false,
                ];
            }

        } else {
            $this->dispatch('notify', type: 'error', message: 'Please select a class');
            return;
        }
    }


    public function validateFields($studentId)
    {
        $this->validate([
            "attendanceData.$studentId.date" => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($studentId) {
                    // Check if attendance already exists
                    $student = app(\App\Services\StudentManagementServices::class)
                        ->getStudentAttendance($this->attendanceData[$studentId]['student_id'], $value);

                    if ($student && $student->student) {
                        $fail('Failed to Save');
                        $this->dispatch('notify', type: 'error', message: 'Attendance for Student ID ' . $student->student_id .
                            ' (' . $student->student->name . ') on ' . $student->date . ' already exists.');
                    }
                },
            ],
            "attendanceData.$studentId.status" => ['required', 'string', 'in:Present,Absent,Leave'],
        ]);
    }


    public function saveAttendance(\App\Services\StudentManagementServices $studentServices)
    {
        $bulkData = [];

        foreach ($this->attendanceData as $studentId => $data) {
            if (!isset($data['save']) || !$data['save']) {
                continue;
            }

            $bulkData[] = [
                'student_id' => $data['student_id'],
                'class_id'   => $data['class_id'],
                'date'       => $data['date'],
                'status'     => $data['status'],
                'remarks'    => $data['remarks'],
            ];
        }

        if (empty($bulkData)) {
            $this->dispatch('notify', type: 'error', message: 'No Student is selected for saving.');
            return;
        }

        $response = $studentServices->addStudentsAttendance($bulkData);

        if ($response === true) {
            $this->dispatch('notify', type: 'success', message: 'Attendance added successfully.');
            $this->attendanceData = [];
            $this->students = null;
            $this->class_id = '';
            $this->resetValidation();
        } else {
            $this->dispatch('notify', type: 'error', message: $response);
        }
    }

}; ?>

<section class="mt-12 p-2">
    <div class="space-y-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3">    
            <div class="flex justify-between w-full gap-2">
                <div class="flex items-center gap-2">
                <flux:select wire:model="searchStudents" class="w-1/2">
                    <flux:select.option value="null">Choose Class for Attendance...</flux:select.option>
                    @forelse ($classes as $class)
                        <flux:select.option value="{{$class->class_id}}">{{ $class->class_id }}</flux:select.option>
                    @empty
                        <flux:select.option disabled>No Class Found</flux:select.option>
                    @endforelse                
                </flux:select>
                <flux:button variant="primary" color="indigo" icon="check" wire:click="getStudentsOfClass" class="cursor-pointer">
                    Select
                </flux:button>
                </div>
                <div class="flex items-center gap-2">
                <flux:input type="date" wire:model='searchDate' wire:change="loadAttendance"/>
                <span wire:loading>
                    <flux:icon.loading />
                </span>
                </div>
            </div>            
        </div>

        @if($students && $students->isNotEmpty())
        <div class="overflow-hidden w-full overflow-x-auto rounded-radius border border-outline dark:border-outline-dark">
            <div class="space-y-2 p-2 border rounded">
                <div>
                    Add Attendance for Class: <strong>{{ $class_id }}</strong>
                </div>

                <form wire:submit.prevent="saveAttendance">
                    @foreach($students as $student)
                        <div class="grid grid-cols-6 gap-2 border p-2 rounded">
                            
                            <!-- Student ID -->
                            <div class="flex p-2">
                                <flux:input type="text" value="{{ $student->student_id }}" disabled />
                            </div>

                            <!-- Student Name -->
                            <div class="flex p-2">
                                <flux:input type="text" value="{{ $student->name }}" disabled />
                            </div>

                            <!-- Date -->
                            <div class="flex p-2">
                                <flux:input type="date" wire:model="attendanceData.{{ $student->student_id }}.date" value="{{ now()->toDateString() }}"/>
                            </div>

                            <!-- Status -->
                            <div class="flex p-2">
                                <flux:select wire:model="attendanceData.{{ $student->student_id }}.status">
                                    <flux:select.option value="">Select Status...</flux:select.option>
                                    <flux:select.option value="Present">Present</flux:select.option>
                                    <flux:select.option value="Absent">Absent</flux:select.option>
                                    <flux:select.option value="Leave">Leave</flux:select.option>
                                </flux:select>
                            </div>

                            <!-- Remarks -->
                            <div class="flex p-2">
                                <flux:input type="text" 
                                            wire:model="attendanceData.{{ $student->student_id }}.remarks" 
                                            placeholder="Remarks (optional)" />
                            </div>

                            <!-- Save Checkbox -->
                            <div class="flex p-2">
                                <flux:field variant="inline" class="p-2">
                                    <flux:checkbox wire:model="attendanceData.{{ $student->student_id }}.save" wire:change="validateFields('{{ $student->student_id }}')" />
                                    <flux:label>Save</flux:label>
                                </flux:field>
                            </div>
                        </div>
                    @endforeach

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 mt-2">
                        <flux:button type="submit" wire:loading.attr="disabled" wire:target="saveAttendance"
                                    variant="primary" color="blue" class="cursor-pointer ms-2">
                            Save
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
        @elseif($students !== null)
        <div
            class="relative w-full overflow-hidden rounded-md border border-red-500 bg-surface text-on-surface dark:bg-surface-dark dark:text-on-surface-dark"
            role="alert">
            <div class="flex w-full items-center gap-2 bg-danger/10 p-4">
                <div class="bg-red-500/15 text-red-500 rounded-full p-1" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-6"
                         aria-hidden="true">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-2">
                    <h3 class="text-sm font-semibold text-danger">{{$class_id}}</h3>
                    <p class="text-xs font-medium sm:text-sm">No Students are in this Class...</p>
                </div>
                <button class="ml-auto" aria-label="dismiss alert">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor"
                         fill="none" stroke-width="2.5" class="size-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        @endif

    </div>

    

    <div class="overflow-hidden w-full overflow-x-auto rounded-radius border border-outline dark:border-outline-dark mt-4">
    <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
        <thead
            class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
            <tr>
                <th scope="col" class="p-4">ID</th>
                <th scope="col" class="p-4">Student</th>
                <th scope="col" class="p-4">Attendance</th>
                <th scope="col" class="p-4">Date</th>
                <th scope="col" class="p-4">Marked</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline dark:divide-outline-dark">

            @forelse(($studentAttendance ?? collect())->groupBy(fn($student) => $student->class . '-' . $student->section) as $classKey => $classStudents)
                
                {{-- Class Header Row --}}
                <tr>
                    <td colspan="8" class="bg-gray-200 p-2 font-bold text-center text-black">
                        <span class="pl-5">{{ $classKey }}</span>
                    </td>
                </tr>

                {{-- Students Rows --}}
                @foreach($classStudents as $student)
                    <tr key="{{ $student->id }}">
                        <td class="p-4">{{ $student->student_id }}</td>
                        <td class="p-4">
                            <div class="flex w-max items-center gap-2">
                                <img src="{{ asset('storage/' . $student->image) }}"
                                     class="size-8 rounded-full object-cover" alt="Student Image">
                                <div class="flex flex-col">
                                    <span class="text-neutral-900 dark:text-white">{{ $student->student_id }}</span>
                                    <span class="text-neutral-500 dark:text-white">{{ $student->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">{{ optional($student->attendances->first())->status ?? '-' }}</td>
                        <td class="p-4">{{ optional($student->attendances->first())->date ?? '-' }}</td>                        
                        <td class="p-4">{{ $student->attendances->isNotEmpty() ? 'Marked' : 'Not Marked' }}</td>
                    </tr>
                @endforeach

            @empty
                <tr>
                    <td colspan="6" class="p-4 text-center">No data found!!!</td>
                </tr>
            @endforelse

        </tbody>
    </table>
</div>
</section>


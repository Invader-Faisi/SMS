<?php

use App\Services\ClassManagementServices;
use App\Services\TimeTableManagementServices;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Title('Time Table')]
class extends Component {
    use WithPagination;

    public string $class_id;
    public string $teacher_id;
    public string $subject;
    public string $days;
    public string $period;
    public string $start_time;

    public string $navbarHeading = "Time Table Management";

    public array $totalDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    public array $totalSubjects = ['Math', 'English', 'Physics', 'Biology or Computer', 'Chemistry', 'Urdu', 'History', 'Social Studies', 'Islamiat', 'Science'];
    public array $totalPeriods = [1, 2, 3, 4, 5, 6, 7, 8];
    public array $timetableData = [];

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    //    helper variables
    public $timetable_id;
    public string $page = 'TimeTable';


//    Table variables
    #[Url(history: true)]
    public $search;

    #[Url(history: true)]
    public $perPage = 40;

    #[Url(history: true)]
    public $sortedBy = 'id';

    public $sortDirection = 'ASC';

    public function with(TimeTableManagementServices $timeTableServices): array
    {
        return [
            'timetables' => $timeTableServices->getTimeTableList(
                $this->search, $this->perPage, $this->sortedBy, $this->sortDirection
            ),
            'totalTeachers' => $timeTableServices->getTeacherList(),
            'classes' => $timeTableServices->getClassesList($this->search),
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

    public function updated($property, $value): void
    {
        if (str_contains($property, 'timetableData') && str_ends_with($property, 'save') && $value) {
            $this->validateFields($property);
        }
    }

    public function saveTimeTable(TimeTableManagementServices $timeTableServices): void
    {
        $bulkData = [];

        foreach ($this->timetableData as $day => $periods) {
            foreach ($periods as $period => $data) {
                if (!isset($data['save']) || !$data['save']) {
                    continue;
                }
                $startTime = Carbon::createFromFormat('H:i', $data['start_time']);
                $endTime = $startTime->copy()->addMinutes(40);
                $data['end_time'] = $endTime->format('H:i');

                $bulkData[] = [
                    'class_id' => $this->class_id,
                    'teacher_id' => $data['teacher_id'],
                    'days' => $day,
                    'period' => $period,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'subject' => $data['subject'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($bulkData)) {
            $this->dispatch('notify', type: 'error', message: 'No timetable rows selected for saving.');
            return;
        }

        $response = $timeTableServices->saveWeekTimeTable($bulkData);

        if ($response === true) {
            $this->dispatch('notify', type: 'success', message: 'Week TimeTable added successfully.');
            $this->reset();
            $this->closeModal();
        } else {
            $this->dispatch('notify', type: 'error', message: $response);
        }
    }

    #[On('delete-timetable')]
    public function delete(TimeTableManagementServices $timeTableServices, $id): void
    {
        $timetable = $timeTableServices->getTimeTableById($id);
        if ($timetable !== null) {
            $this->timetable_id = $timetable->id;
            $timetable = $timeTableServices->deleteTimeTable($this->timetable_id);

            if ($timetable === true) {
                $this->dispatch('notify', type: 'success', message: 'Period deleted successfully.');
            } elseif ($timetable === false) {
                $this->dispatch('notify', type: 'error', message: 'Period not Found !!!');
            } else {
                $this->dispatch('notify', type: 'error', message: $timetable);
            }
            $this->reset();
        }
    }

    public function validateFields(string $property): void
    {
        $parts = explode('.', $property);
        $day = $parts[1] ?? null;
        $period = $parts[2] ?? null;

        if (!$day || !$period) {
            return;
        }

        $classServices = app(ClassManagementServices::class);

        $this->validate([
            'class_id' => ['required', 'exists:classes,class_id'],
            "timetableData.$day.$period.teacher_id" => ['required', 'exists:teachers,teacher_id'],
            "timetableData.$day.$period.days" => ['required', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'])],
            "timetableData.$day.$period.period" => ['required', 'integer', 'min:1', 'max:8'],
            "timetableData.$day.$period.start_time" => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($day, $period) {
                    if ($period > 1) {
                        $previousPeriod = $this->timetableData[$day][$period - 1] ?? null;

                        if ($previousPeriod && !empty($previousPeriod['start_time'])) {
                            $previousStart = Carbon::createFromFormat('H:i', $previousPeriod['start_time']);
                            $previousEnd   = $previousStart->copy()->addMinutes(40);
                            $minNextStart  = $previousEnd->copy()->addMinutes(5);

                            $currentStart  = Carbon::createFromFormat('H:i', $value);

                            if ($currentStart->lt($minNextStart)) {
                                $this->dispatch('notify', type: 'error', message: 'Period '.$period.' must start 45 minutes after Period '.($period - 1));
                                $fail("Failed to enter");
                            }
                        }
                    }
                }
            ],
            "timetableData.$day.$period.subject" => [
                'required',
                Rule::in([
                    'Math', 'English', 'Physics', 'Biology or Computer', 'Chemistry',
                    'Urdu', 'History', 'Social Studies', 'Islamiat', 'Science', 'GK'
                ]),
                function ($attribute, $value, $fail) use ($classServices) {
                    $class = $classServices->getClassById($this->class_id);
                    if (!$class) {
                        return;
                    }
                    $grade = preg_replace('/[- ].*/', '', $class->class_id);

                    $allowed = match (true) {
                        in_array($grade, ['IX', 'X']) =>
                        ['Math', 'English', 'Physics', 'Biology or Computer', 'Chemistry', 'Urdu', 'Social Studies', 'Islamiat'],
                        in_array($grade, ['VI', 'VII', 'VIII']) =>
                        ['Math', 'English', 'Urdu', 'Social Studies', 'Islamiat', 'Computer', 'History'],
                        in_array($grade, ['Nursery', 'Prep']) =>
                        ['Math', 'English', 'Urdu', 'GK'],
                        in_array($grade, ['I', 'II', 'III', 'IV', 'V']) =>
                        ['Math', 'English', 'Urdu', 'GK', 'Islamiat'],
                        default => [],
                    };

                    if (!in_array($value, $allowed)) {
                        $this->dispatch('notify', type: 'error', message: 'Subject '.$value.' is not allowed for class '.$class->name);
                        $fail("Failed to enter");
                    }
                }
            ],
        ]);
    }

    public function updateClass($classId): void
    {
        $this->class_id = $classId;

        $classServices = app(ClassManagementServices::class);
        $class = $classServices->getClassById($this->class_id);

        if (!$class) {
            $this->totalSubjects = [];
            return;
        }
        $grade = preg_replace('/[- ].*/', '', $class->class_id);

        $this->totalSubjects = match (true) {
            in_array($grade, ['IX', 'X']) =>
            ['Math', 'English', 'Physics', 'Biology or Computer', 'Chemistry', 'Urdu', 'Social Studies', 'Islamiat'],
            in_array($grade, ['VI', 'VII', 'VIII']) =>
            ['Math', 'English', 'Urdu', 'Social Studies', 'Islamiat', 'Computer', 'History'],
            in_array($grade, ['Nursery', 'Prep']) =>
            ['Math', 'English', 'Urdu', 'GK'],
            in_array($grade, ['I', 'II', 'III', 'IV', 'V']) =>
            ['Math', 'English', 'Urdu', 'GK', 'Islamiat'],
            default => ['Math', 'English', 'Physics', 'Biology or Computer', 'Chemistry', 'Urdu', 'History', 'Social Studies', 'Islamiat', 'Science'],
        };
    }

    public function showPeriod(TimeTableManagementServices $timeTableServices, $id): void
    {
        $period = $timeTableServices->getPeriod($id);
        if($period !== null){
            $this->timetable_id = $id;
            $this->class_id = $period->class_id;
            $this->teacher_id = $period->teacher_id;
            $this->subject = $period->subject;
            $this->period = $period->period;
            $this->days = $period->days;
            $this->start_time = $period->start_time;

            $this->start_time = Carbon::parse($period->start_time)->format('H:i');
            $this->updateClass($this->class_id);
            Flux::modal('updatePeriod')->show();
        }
    }

    public function updatePeriod(TimeTableManagementServices $timeTableServices): void
    {
        $period = $this->validate([
            'class_id'   => ['required', 'exists:classes,class_id'],
            'teacher_id' => ['required', 'exists:teachers,teacher_id'],
            'days'       => ['required', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'])],
            'period'     => ['required', 'integer', 'min:1', 'max:8'],
            'start_time' => ['required', 'date_format:H:i'],
            'subject'    => ['required'],
        ]);
        $end_time = Carbon::createFromFormat('H:i', $period['start_time'])->addMinutes(40);
        $period['end_time'] = $end_time->format('H:i');

        $response = $timeTableServices->updatePeriod($period,$this->timetable_id);
        if ($response > 0) {
            $this->reset();
            $this->dispatch('notify', type: 'success', message: 'Period updated successfully.');
        }elseif($response === false){
            $this->dispatch('notify', type: 'error', message: 'Add 5 Minutes Difference from Previous Period End Time ');
        }else{
            $this->dispatch('notify', type: 'error', message: $response);
        }
        Flux::modal('updatePeriod')->close();
    }

}; ?>

<section>
    {{-- Parent Table--}}
    <div class="space-y-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3">
            <div class="w-full lg:w-52"></div>
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <flux:select wire:model.live.debounce.200ms="perPage">
                    <flux:select.option value="null">Choose Per Page Record...</flux:select.option>
                    <flux:select.option>40</flux:select.option>
                    <flux:select.option>80</flux:select.option>
                    <flux:select.option>120</flux:select.option>
                    <flux:select.option>800</flux:select.option>
                </flux:select>
                <flux:modal.trigger name="addTimeTable">
                    <flux:button variant="primary" color="indigo" icon="plus-circle" class="cursor-pointer">
                        Add {{$page}}
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <div
            class="overflow-hidden w-full overflow-x-auto rounded-radius border border-outline dark:border-outline-dark">
            <div class="mx-2 mb-2 mt-2">
                {{$timetables->links()}}
            </div>
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
                <thead
                    class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                <tr>
                    <th scope="col" class="p-4">ID</th>
                    <th scope="col" class="p-4">Teacher</th>
                    <th scope="col" class="p-4">Class</th>
                    <th scope="col" class="p-4">Period</th>
                    <th scope="col" class="p-4">Subject</th>
                    <th scope="col" class="p-4">Start</th>
                    <th scope="col" class="p-4">End</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @php
                    $groupedTimetables = $timetables->getCollection()->groupBy('class_id')
                    ->map(function ($classGroup) {  return $classGroup->groupBy('days');});
                @endphp
                @forelse($groupedTimetables as $classId => $daysGroup)
                    <tr>
                        <td colspan="8" class="bg-gray-200 p-2 font-bold text-center text-black">
                            <div class="flex justify-between items-center">
                                <span class="pl-5"> {{ $classId }} </span>
                                <flux:button href="{{ route('classes.timetable', ['id' => $classId]) }}" icon:trailing="eye" >
                                    View
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                    @foreach($daysGroup as $day => $classTimetables)
                        <td colspan="8" class="bg-green-300 p-2 font-semibold text-indigo-600 text-center">
                            {{ $day }}
                        </td>
                        @foreach($classTimetables  as $timetable)
                            <tr key="{{$timetable->id}}">
                                <td class="p-4">{{$timetable->id}}</td>
                                <td class="p-4">
                                    <div class="flex w-max items-center gap-2">
                                        <img src="{{ asset('storage/' . $timetable->teacher->image) }}"
                                             class="size-8 rounded-full object-cover" alt="Staff Image">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-neutral-900 dark:text-white">{{$timetable->teacher->name}}</span>
                                            <span
                                                class="text-neutral-500 dark:text-white">{{$timetable->teacher->designation}}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">{{$timetable->class->class_id}}</td>
                                <td class="p-4">{{$timetable->period}}</td>
                                <td class="p-4">{{$timetable->subject}}</td>
                                <td class="p-4">{{ \Carbon\Carbon::parse($timetable->start_time)->format('H:i') }}</td>
                                <td class="p-4">{{ \Carbon\Carbon::parse($timetable->end_time)->format('H:i') }}</td>
                                <td class="p-4">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex gap-2">
                                            <flux:modal.trigger name="delete-confirmation">
                                                <flux:button variant="primary" color="rose" size="xs"
                                                             class="cursor-pointer"
                                                             wire:click="$dispatch('confirm-delete',{
                                            id: {{$timetable->id}},
                                            dispatchAction: 'delete-timetable',
                                            heading: 'Delete Time Table',
                                            subheading: 'You are deleting data of',
                                            name: '{{$timetable->class->class_id}}',
                                            confirmButtonText: 'Delete TimeTable',
                                            })">
                                                <flux:icon.trash variant="solid" class="size-4"/>
                                                </flux:button>
                                            </flux:modal.trigger>
                                            <flux:button variant="primary" color="yellow" size="xs"
                                                         class="cursor-pointer"
                                                         wire:click="showPeriod({{ $timetable->id }})">
                                                <flux:icon.pencil variant="solid" class="size-4"/>
                                            </flux:button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center">No data found!!!</td>
                    </tr>
                @endforelse

                </tbody>
            </table>
            <flux:separator variant="subtle"/>
            <div class="mx-2 mb-2 mt-2">
                {{$timetables->links()}}
            </div>
            <livewire:common.delete/>
        </div>
    </div>

{{--    period modal --}}
    <flux:modal name="updatePeriod" class="w-full max-w-3xl md:max-w-2xl lg:max-w-4xl">
        <div class="space-y-6">

            <h3>Update Period Data</h3>

            <form wire:submit.prevent="updatePeriod" enctype="multipart/form-data">
                <div class="flex flex-wrap -mx-2">
                    <!-- Left Column -->
                    <div class="form-group w-full md:w-1/2 px-2 space-y-4">
                        <flux:input wire:model="class_id" :label="__('Class')" class="w-full p-2" disabled/>
                        <flux:select wire:model="subject" :label="__('Subject')" class="w-full p-2">
                            <flux:select.option value="">Select Subject...</flux:select.option>
                            @foreach($totalSubjects ?? [] as $subject)
                                <flux:select.option value="{{ $subject }}">
                                    {{ $subject }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="teacher_id" :label="__('Teacher')" class="w-full p-2">
                            <flux:select.option value="">Select Teacher...</flux:select.option>
                            @foreach($totalTeachers ?? [] as $teacher)
                                <flux:select.option value="{{ $teacher->teacher_id }}">
                                    {{ $teacher->name }} - {{ $teacher->designation }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <!-- Right Column -->
                    <div class="form-group w-full md:w-1/2 px-2 space-y-2">
                        <flux:input wire:model="days" :label="__('Day')" class="w-full p-2" disabled/>
                        <flux:input type="time" wire:model="start_time" :label="__('Start Time')" class="w-full p-2"/>
                        <flux:select wire:model="period" :label="__('Period')" class="w-full p-2">
                            <flux:select.option value="">Select Period...</flux:select.option>
                            @foreach($totalPeriods ?? [] as $period)
                                <flux:select.option value="{{ $period }}">
                                    {{ $period }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <flux:button variant="filled" class="cursor-pointer" x-on:click="$flux.modal('updatePeriod').close()">Cancel</flux:button>
                    <flux:button type="submit" wire:loading.attr="disabled" variant="primary" color="blue" class="cursor-pointer ms-2">
                        Update Period
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    @include('livewire.classes.partials.time-table-modal')
</section>

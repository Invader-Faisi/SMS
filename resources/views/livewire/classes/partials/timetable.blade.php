<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Title('Class Time table')]
class extends Component {

    public string $class = '';
    public Collection $timetable;

    public function mount(string $id): void
    {
        $this->timetable = new Collection();
        $this->class = $id;
    }

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->class,
        ]);
    }

    public function with(\App\Services\TimeTableManagementServices $timeTableServices): array
    {
        return [
            $this->timetable = $timeTableServices->getTimeTableByClass($this->class),
        ];

    }
};

?>

<section class="pt-12 p-2">
    <div class="overflow-hidden w-full overflow-x-auto rounded-radius border border-outline dark:border-outline-dark">
        <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
            <!-- Table Head -->
            <thead class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
            <tr>
                <th scope="col" class="p-4 text-center">Period</th>
                <th scope="col" class="p-4 text-center">Mon</th>
                <th scope="col" class="p-4 text-center">Tue</th>
                <th scope="col" class="p-4 text-center">Wed</th>
                <th scope="col" class="p-4 text-center">Thu</th>
                <th scope="col" class="p-4 text-center">Fri</th>
                <th scope="col" class="p-4 text-center">Sat</th>
            </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="divide-y divide-outline dark:divide-outline-dark">
            @if($timetable->isNotEmpty())
                @for($period = 1; $period <= 8; $period++)
                    <tr>
                        <!-- Period Number -->
                        <td class="px-2 py-2 border border-gray-300 dark:border-zinc-700 font-semibold text-center">
                            {{ $period }}
                        </td>

                        <!-- Day Columns -->
                        @foreach (['Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                            @php
                                $entry = $timetable->firstWhere(fn($row) => $row->period == $period && $row->days == $day);
                            @endphp
                            <td class="px-4 py-2 border border-gray-300 dark:border-zinc-700 text-center">
                                @if ($entry)
                                    <div class="font-medium text-indigo-400">{{ $entry->teacher->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $entry->subject ?? '-' }}</div>
                                    <div class="font-medium text-green-400">{{ \Carbon\Carbon::parse($entry->start_time)->format('H:i') ?? '-' }} to {{ \Carbon\Carbon::parse($entry->end_time)->format('H:i') ?? '-' }}</div>
                                @else
                                    –
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @if($period == 6)
                        <tr>
                            <td colspan="7" class="px-4 py-2 border border-gray-300 dark:border-zinc-700 text-center font-semibold bg-gray-200 dark:bg-zinc-800">
                                Break
                            </td>
                        </tr>
                    @endif
                @endfor
            @else
                <tr>
                    <td colspan="7" class="p-4">
                        <div class="relative w-full overflow-hidden rounded-md border border-red-500 bg-surface text-on-surface dark:bg-surface-dark dark:text-on-surface-dark"
                             role="alert">
                            <div class="flex w-full items-center gap-2 bg-danger/10 p-4">
                                <div class="bg-red-500/15 text-red-500 rounded-full p-1" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                         class="size-6" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                              d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-2">
                                    <h3 class="text-sm font-semibold text-danger">{{ $class }}</h3>
                                    <p class="text-xs font-medium sm:text-sm">No Time Table Found...</p>
                                </div>
                                <button class="ml-auto" aria-label="dismiss alert">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"
                                         stroke="currentColor" fill="none" stroke-width="2.5" class="size-4 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</section>


<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new
#[Title('Classes')]
class extends Component {

    public Collection $students;
    public string $class = '';
    public string $navbarHeading = "Class Management";

    //    Helping variables

    public string $page = 'Class';
    #[Url(history: true)]
    public $search;

    public function mount(string $id): void
    {
        $this->students = new Collection();
        $this->class = $id;
    }

    public function with(\App\Services\StudentManagementServices $studentServices): array
    {
        return [
            $this->students =  $studentServices->getStudentsByClass($this->class, $this->search),
        ];
    }

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->class,
        ]);
    }



}; ?>

<section class="p-2 space-y-2">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3 p-2">
        <div class="w-full">
            <flux:input wire:model.live.debounce.1000ms="search" icon="magnifying-glass" placeholder="Search Students..."
                        class="text-sm"/>
        </div>
    </div>
    @if(!$students->isEmpty())
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4 lg:grid-cols-8 p-2">
            @foreach($students as $student)
                <article
                    class="group flex rounded-radius max-w-xs flex-col overflow-hidden border border-outline bg-surface-alt text-on-surface dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark">
                    <!-- Images -->
                    <div class="relative h-8">
                        <div
                            class="relative z-10 mx-auto mt-2 size-14 overflow-hidden rounded-full border-4 border-surface-alt dark:border-surface-dark-alt">
                            <img src="{{ asset('storage/' . $student->image) }}"
                                 class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                                 alt="avatar"/>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="flex flex-col text-center mt-8">
                        <h3 class="h-6 text-balance text-sm font-bold text-on-surface-strong lg:text-sm dark:text-on-surface-dark-strong">
                            {{$student->name}}</h3>
                        <p class="mt-2 text-green-400 text-sm">{{$student->student_id}}</p>
                        <p class="mt-2 text-pretty text-sm">Parent</p>
                        <p class="text-sm text-indigo-400">{{$student->parent->name}}</p>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <!-- danger Alert -->
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
                    <h3 class="text-sm font-semibold text-danger">{{$class}}</h3>
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


</section>

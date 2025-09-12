<?php

use App\Services\AccountManagementServices;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[\Livewire\Attributes\Title('Fee Management')]
class extends Component {
    use WithPagination;

    public string $navbarHeading = "Fee Management";

    public function rendering(View $view): void
    {
        $view->layoutData([
            'navbarHeading' => $this->navbarHeading,
        ]);
    }

    //    Form fields
    public string $name = '';
    public string $class = '';
    public string $amount = '';
    public string $type = '';
    public string $frequency = '';

    //    helper variables
    public $id;
    public bool $isEditMode = false;
    public string $page = 'FeeStructure';

    //    Table variables
    #[Url(history: true)]
    public $search;

    #[Url(history: true)]
    public $perPage = 10;

    #[Url(history: true)]
    public $sortedBy = 'id';

    public $sortDirection = 'ASC';

    public function with(AccountManagementServices $feeServices): array
    {
        return [
            'fees' => $feeServices->getFeeStructureList(
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

    public function validateFields(): array
    {
        return $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'class' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:10'],
            'type' => ['required', 'string'],
            'frequency' => ['required', 'string'],
        ]);
    }

    public function saveFeeStructure(\App\Services\AccountManagementServices $feeServices): void
    {
        $feeForm = $this->validateFields();
        $fee = $feeServices->saveFeeStructure($feeForm);

        if ($fee === true) {
            $this->dispatch('notify', type: 'success', message: 'Fee Structure added successfully.');
        } else {
            $this->dispatch('notify', type: 'error', message: $fee);
        }

        $this->closeModal();
    }

    public function showFeeStructure(AccountManagementServices $feeServices, $feeId): void
    {
        $fee = $feeServices->getFeeStructureById($feeId);

        if ($fee !== null) {
            $this->id = $fee->id;
            $this->name = $fee->name;
            $this->class = $fee->class;
            $this->amount = $fee->amount;
            $this->type = $fee->type;
            $this->frequency = $fee->frequency;

            $this->isEditMode = true;
            Flux::modal('add-' . $this->page)->show();
        }
    }

    public function updateFeeStructure(AccountManagementServices $feeServices): void
    {
        $feeForm = $this->validateFields();

        $fee = $feeServices->updateFeeStructure($feeForm, $this->id);

        if ($fee > 0) {
            $this->reset();
            $this->isEditMode = false;
            $this->dispatch('notify', type: 'success', message: 'Fee Structure updated successfully.');

        } else {
            $this->dispatch('notify', type: 'error', message: $fee);
        }
        $this->closeModal();
    }

    #[On('delete-fee-structure')]
    public function delete(AccountManagementServices $feeServices, $id): void
    {
        $fee = $feeServices->getFeeStructureById($id);
        if ($fee !== null) {
            $this->id = $fee->id;
            $fee = $feeServices->deleteFeeStructure($this->id);
            if ($fee > 0) {
                $this->dispatch('notify', type: 'success', message: 'Fee Structure deleted successfully.');
            } else {
                $this->dispatch('notify', type: 'error', message: $fee);
            }
            $this->reset();
        }
    }

    public function closeModal()
    {
        $this->reset();
        $this->isEditMode = false;
        Flux::modal('add-' . $this->page)->close();
    }


}; ?>

<section class="mt-12 p-2">
    {{-- Fee Structure Table--}}
    <div class="space-y-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-3">
            <div class="w-full lg:w-52">
                <flux:input wire:model.live.debounce.1000ms="search" icon="magnifying-glass" placeholder="Search orders"
                            class="text-sm"/>
            </div>
            <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto">
                <flux:select wire:model.live.debounce.200ms="perPage">
                    <flux:select.option value="null">Choose Per Page Record...</flux:select.option>
                    <flux:select.option>5</flux:select.option>
                    <flux:select.option>10</flux:select.option>
                    <flux:select.option>15</flux:select.option>
                    <flux:select.option>20</flux:select.option>
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
                {{$fees->links()}}
            </div>
            <table class="w-full text-left text-sm text-on-surface dark:text-on-surface-dark">
                <thead
                    class="border-b border-outline bg-surface-alt text-sm text-on-surface-strong dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark-strong">
                <tr>
                    <th scope="col" class="p-4">ID</th>
                    <th scope="col" class="p-4">Class</th>
                    <th scope="col" class="p-4">Amount</th>
                    <th scope="col" class="p-4">Type</th>
                    <th scope="col" class="p-4">Frequency</th>
                    <th scope="col" class="p-4">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-outline dark:divide-outline-dark">
                @forelse($fees as $fee)
                    <tr key="{{$fee->id}}">
                        <td class="p-4">{{$fee->name}}</td>
                        <td class="p-4">{{$fee->class}}</td>
                        <td class="p-4">{{$fee->amount}}</td>
                        <td class="p-4"><span
                                class="inline-flex overflow-hidden rounded-radius border-success px-1 py-0.5 text-xs font-medium text-success bg-success/10">{{$fee->type}}</span>
                        </td>
                        <td class="p-4">{{$fee->frequency}}</td>
                        <td class="p-4">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="delete-confirmation">
                                        <flux:button variant="primary" color="rose" size="xs" class="cursor-pointer"
                                                     wire:click="$dispatch('confirm-delete',{
                                        id: {{$fee->id}},
                                        dispatchAction: 'delete-fee-structure',
                                        heading: 'Delete Fee Structure Data',
                                        subheading: 'You are deleting data of',
                                        name: '{{$fee->name}}',
                                        confirmButtonText: 'Delete Fee Structure',
                                        })">
                                            <flux:icon.trash variant="solid" class="size-4"/>
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <flux:button variant="primary" color="yellow" size="xs" class="cursor-pointer"
                                                 wire:click="showFeeStructure({{ $fee->id }})">
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
                {{$fees->links()}}
            </div>
            <livewire:common.delete/>
        </div>
    </div>
    @include('livewire.accounts.partials.fee-structure-modal', ['isEditMode' => $isEditMode, 'page' => $page])
</section>

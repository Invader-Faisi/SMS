<?php

namespace App\Livewire\Common;

use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public $id = null;
    public $dispatchAction = null;
    public $heading = null;
    public $subheading = null;
    public $name = null;
    public string $confirmButtonText = 'Delete';

    public function render()
    {
        return view('livewire.common.delete');
    }

    #[On('confirm-delete')]
    public function confirmDelete($id,$dispatchAction,$heading,$subheading,$name,$confirmButtonText = null): void
    {
        $this->id = $id;
        $this->dispatchAction = $dispatchAction;
        $this->heading = $heading;
        $this->subheading = $subheading;
        $this->name = $name;
        $this->confirmButtonText = $confirmButtonText;

    }

    public function delete(): void
    {
        if($this->dispatchAction && $this->id){
            $this->dispatch($this->dispatchAction, $this->id);
            Flux::modal('delete-confirmation')->close();
        }
    }

}

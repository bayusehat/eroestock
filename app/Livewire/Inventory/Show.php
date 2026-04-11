<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Brand;
use Livewire\Component;

class Show extends Component
{
    public Inventory $item;

    public function mount(Inventory $item): void
    {
        $this->item = $item->load(['item.brand']);
    }

    // public function updateStatus(string $status): void
    // {
    //     $data = ['status' => $status];
    //     if ($status === 'completed') $data['completed_date'] = now();
    //     $this->workOrder->update($data);
    //     $this->workOrder->refresh();
    //     session()->flash('success', "Status diperbarui ke {$status}");
    // }

    public function render()
    {
        return view('livewire.inventory.show');
    }
}

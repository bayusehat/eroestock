<?php

namespace App\Livewire\StockOpname;

use App\Models\StockOpname;
use Livewire\Component;

class Show extends Component
{
    public StockOpname $stockOpname;

    public function mount(StockOpname $stockOpname): void
    {
        $this->stockOpname = $stockOpname->load(['user', 'items']);
    }

    public function updateStatus(string $status): void
    {
        $data = ['status' => $status];
        $this->stockOpname->update($data);
        $this->stockOpname->refresh();
        session()->flash('success', "Status diperbarui ke {$status}");
    }

    public function render()
    {
        return view('livewire.stock-opname.show');
    }
}

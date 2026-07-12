<?php

namespace App\Livewire\WorkOrders;

use App\Models\WorkOrder;
use App\Models\ShopeeOrder;
use Livewire\Component;

class Show extends Component
{
    public ShopeeOrder $workOrder;

    public function mount(ShopeeOrder $workOrder): void
    {
        $this->workOrder = $workOrder->load(['details']);
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
        return view('livewire.work-orders.show');
    }
}

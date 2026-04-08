<?php

namespace App\Livewire\WorkOrders;

use App\Models\WorkOrder;
use Livewire\Component;

class Show extends Component
{
    public WorkOrder $workOrder;

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder->load(['client', 'items']);
    }

    public function updateStatus(string $status): void
    {
        $data = ['status' => $status];
        if ($status === 'completed') $data['completed_date'] = now();
        $this->workOrder->update($data);
        $this->workOrder->refresh();
        session()->flash('success', "Status diperbarui ke {$status}");
    }

    public function render()
    {
        return view('livewire.work-orders.show');
    }
}

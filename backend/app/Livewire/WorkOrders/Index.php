<?php

namespace App\Livewire\WorkOrders;

use App\Models\Client;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class Index extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $clientFilter = '';
    public ?WorkOrder $changingStatusWo = null;
    public string $newStatus = '';

    protected $queryString = ['search', 'statusFilter', 'clientFilter'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingClientFilter(): void { $this->resetPage(); }

    public function openStatusModal(int $id): void
    {
        $this->changingStatusWo = WorkOrder::find($id);
        $this->newStatus = '';
    }

    public function closeStatusModal(): void
    {
        $this->changingStatusWo = null;
        $this->newStatus = '';
    }

    public function updateStatus(): void
    {
        if (! $this->changingStatusWo || ! $this->newStatus) return;

        $data = ['status' => $this->newStatus];
        if ($this->newStatus === 'completed') {
            $data['completed_date'] = now();
        }

        $this->changingStatusWo->update($data);
        session()->flash('success', "Status diperbarui ke {$this->newStatus}");
        $this->closeStatusModal();
    }

    public function duplicate(int $id): void
    {
        $wo = WorkOrder::with('items')->findOrFail($id);
        $new = $wo->replicate(['wo_number', 'status', 'completed_date']);
        $new->wo_number = \App\Traits\GeneratesNumber::generateNumber('WO', 'work_orders', 'wo_number', 'Y');
        $new->status = 'draft';
        $new->save();
        foreach ($wo->items as $item) {
            $new->items()->create($item->only(['description','quantity','unit','unit_price','discount','tax_rate','subtotal']));
        }
        session()->flash('success', 'Work order duplikat berhasil dibuat.');
    }

    public function render()
    {
        $query = WorkOrder::query()->with('client:id,name');

        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('wo_number', 'like', "%{$s}%")->orWhere('title', 'like', "%{$s}%"));
        }
        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        if ($this->clientFilter) $query->where('client_id', $this->clientFilter);

        return view('livewire.work-orders.index', [
            'workOrders' => $query->latest('order_date')->paginate(25),
            'clients' => Client::orderBy('name')->get(['id','name']),
            'transitions' => [
                'draft' => ['confirmed', 'cancelled'],
                'confirmed' => ['in_progress', 'cancelled'],
                'in_progress' => ['completed', 'cancelled'],
                'completed' => ['invoiced'],
            ],
        ]);
    }
}

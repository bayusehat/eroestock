<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function updatingSearch(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        Vendor::findOrFail($id)->delete();
        session()->flash('success', 'Vendor berhasil dihapus.');
    }

    public function render()
    {
        $query = Vendor::query();
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }
        return view('livewire.vendors.index', ['vendors' => $query->orderBy('name')->paginate(25)]);
    }
}

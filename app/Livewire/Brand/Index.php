<?php

namespace App\Livewire\Brand;

use App\Models\Brand;
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
        Brand::findOrFail($id)->delete();
        session()->flash('success', 'Brand berhasil dihapus.');
    }

    public function render()
    {
        $query = Brand::query();
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }
        return view('livewire.brands.index', ['brands' => $query->orderBy('name')->paginate(25)]);
    }
}

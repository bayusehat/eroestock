<?php

namespace App\Livewire\Clients;

use App\Models\Client;
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
        Client::findOrFail($id)->delete();
        session()->flash('success', 'Client berhasil dihapus.');
    }

    public function render()
    {
        $query = Client::query();
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }
        return view('livewire.clients.index', ['clients' => $query->orderBy('name')->paginate(25)]);
    }
}

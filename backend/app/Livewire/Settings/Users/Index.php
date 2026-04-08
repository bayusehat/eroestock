<?php

namespace App\Livewire\Settings\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function updatingSearch(): void { $this->resetPage(); }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
        session()->flash('success', 'Status user diperbarui.');
    }

    public function render()
    {
        $query = User::with('roles');
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }
        return view('livewire.settings.users.index', ['users' => $query->paginate(25)])
            ->layout('components.layouts.app', ['title' => 'Users']);
    }
}

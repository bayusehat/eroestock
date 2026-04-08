<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $query = Employee::query();
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('employee_id', 'like', "%{$s}%")->orWhere('position', 'like', "%{$s}%"));
        }
        if ($this->statusFilter) $query->where('status', $this->statusFilter);

        return view('livewire.employees.index', ['employees' => $query->orderBy('name')->paginate(25)]);
    }
}

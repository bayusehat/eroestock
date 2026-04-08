<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use App\Traits\GeneratesNumber;
use Livewire\Component;

class Form extends Component
{
    public ?Employee $employee = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $position = '';
    public string $department = '';
    public string $join_date = '';
    public string $status = 'active';
    public float $base_salary = 0;
    public string $bank_name = '';
    public string $bank_account = '';
    public string $bank_holder = '';
    public string $tax_id = '';
    public string $address = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'join_date' => ['required', 'date'],
            'base_salary' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function mount(?Employee $employee = null): void
    {
        $this->employee = $employee;
        $this->join_date = now()->format('Y-m-d');
        if ($employee && $employee->exists) {
            $this->fill($employee->only(['name','email','phone','position','department','status','base_salary','bank_name','bank_account','bank_holder','tax_id','address']));
            $this->join_date = $employee->join_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        }
    }

    public function save(): void
    {
        $this->validate();
        $data = ['name' => $this->name, 'email' => $this->email ?: null, 'phone' => $this->phone ?: null,
                 'position' => $this->position ?: null, 'department' => $this->department ?: null,
                 'join_date' => $this->join_date, 'status' => $this->status,
                 'base_salary' => $this->base_salary, 'bank_name' => $this->bank_name ?: null,
                 'bank_account' => $this->bank_account ?: null, 'bank_holder' => $this->bank_holder ?: null,
                 'tax_id' => $this->tax_id ?: null, 'address' => $this->address ?: null];

        if ($this->employee && $this->employee->exists) {
            $this->employee->update($data);
        } else {
            $data['employee_id'] = GeneratesNumber::generateNumber('EMP', 'employees', 'employee_id', 'Y');
            Employee::create($data);
        }
        session()->flash('success', 'Karyawan berhasil disimpan.');
        $this->redirect(route('employees.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.employees.form', ['isEditing' => $this->employee && $this->employee->exists]);
    }
}

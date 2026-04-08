<?php

namespace App\Livewire\Settings\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Form extends Component
{
    public ?User $user = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $phone = '';
    public bool $is_active = true;
    public array $roles = [];

    protected function rules(): array
    {
        $emailRule = $this->user && $this->user->exists
            ? Rule::unique('users', 'email')->ignore($this->user->id)
            : Rule::unique('users', 'email');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', $emailRule],
            'password' => $this->user && $this->user->exists ? ['nullable', 'min:8'] : ['required', 'min:8'],
        ];
    }

    public function mount(?User $user = null): void
    {
        $this->user = $user;
        if ($user && $user->exists) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->is_active = (bool) $user->is_active;
            $this->roles = $user->roles->pluck('name')->toArray();
        }
    }

    public function save(): void
    {
        $this->validate();
        $data = ['name' => $this->name, 'email' => $this->email, 'is_active' => $this->is_active, 'phone' => $this->phone ?: null];
        if ($this->password) $data['password'] = Hash::make($this->password);

        if ($this->user && $this->user->exists) {
            $this->user->update($data);
            $this->user->syncRoles($this->roles);
        } else {
            $user = User::create($data);
            $user->syncRoles($this->roles);
        }

        session()->flash('success', 'User berhasil disimpan.');
        $this->redirect(route('settings.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.users.form', [
            'availableRoles' => Role::all(),
        ])->layout('components.layouts.app', ['title' => $this->user ? 'Edit user' : 'Create user']);
    }
}

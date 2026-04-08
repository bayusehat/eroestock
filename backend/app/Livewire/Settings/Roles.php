<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    public bool $rolePanelOpen = false;
    public ?int $editingRoleId = null;
    public string $roleName = '';
    public array $selectedPermissions = [];

    public function openCreate(): void
    {
        $this->reset(['editingRoleId', 'roleName', 'selectedPermissions']);
        $this->rolePanelOpen = true;
    }

    public function openEdit(int $roleId): void
    {
        $this->editingRoleId = $roleId;
        $role = Role::with('permissions')->findOrFail($roleId);
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->rolePanelOpen = true;
    }

    public function closePanel(): void
    {
        $this->rolePanelOpen = false;
        $this->reset(['editingRoleId', 'roleName', 'selectedPermissions']);
    }

    public function createRole(): void
    {
        $this->validate(['roleName' => ['required', 'string', 'max:255', 'unique:roles,name']]);
        $role = Role::create(['name' => $this->roleName, 'guard_name' => 'web']);
        if (! empty($this->selectedPermissions)) {
            $role->syncPermissions($this->selectedPermissions);
        }
        $this->closePanel();
        session()->flash('success', 'Role berhasil dibuat.');
    }

    public function updateRole(): void
    {
        $this->validate(['roleName' => ['required', 'string', 'max:255']]);
        $role = Role::findOrFail($this->editingRoleId);
        $role->update(['name' => $this->roleName]);
        $role->syncPermissions($this->selectedPermissions);
        $this->closePanel();
        session()->flash('success', 'Role berhasil diperbarui.');
    }

    public function deleteRole(int $id): void
    {
        Role::findOrFail($id)->delete();
        session()->flash('success', 'Role berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.settings.roles', [
            'roles' => Role::withCount('permissions')->get(),
            'permissionsGrouped' => Permission::all()->groupBy(fn($p) => explode('-', $p->name)[0]),
        ])->layout('components.layouts.app', ['title' => 'Roles']);
    }
}

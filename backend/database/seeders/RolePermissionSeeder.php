<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'accounts' => ['view', 'create', 'edit', 'delete'],
            'clients' => ['view', 'create', 'edit', 'delete'],
            'vendors' => ['view', 'create', 'edit', 'delete'],
            'work_orders' => ['view', 'create', 'edit', 'delete'],
            'budget_requests' => ['view', 'create', 'edit', 'delete'],
            'invoices' => ['view', 'create', 'edit', 'delete'],
            'transactions' => ['view', 'create', 'edit', 'delete'],
            'journal_entries' => ['view', 'create', 'edit', 'delete'],
            'employees' => ['view', 'create', 'edit', 'delete'],
            'payroll' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view'],
            'settings' => ['view', 'create', 'edit', 'delete'],
            'audit_logs' => ['view'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$module}-{$action}", 'guard_name' => 'web'],
                    ['name' => "{$module}-{$action}", 'guard_name' => 'web']
                );
            }
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(
            Permission::whereNotIn('name', ['roles-view', 'roles-create', 'roles-edit', 'roles-delete'])->pluck('name')
        );

        $accountantPermissions = collect();
        foreach (['work_orders', 'budget_requests', 'invoices', 'transactions', 'journal_entries', 'accounts', 'clients', 'vendors'] as $mod) {
            $accountantPermissions = $accountantPermissions->merge(
                Permission::where('name', 'like', "{$mod}-%")->pluck('name')
            );
        }
        $accountantPermissions = $accountantPermissions->merge(
            Permission::where('name', 'reports-view')->pluck('name')
        )->unique()->values();
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions($accountantPermissions);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions(Permission::where('name', 'like', '%-view')->pluck('name'));
    }
}

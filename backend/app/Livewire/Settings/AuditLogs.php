<?php

namespace App\Livewire\Settings;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $moduleFilter = '';

    protected $queryString = ['search', 'moduleFilter'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingModuleFilter(): void { $this->resetPage(); }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = AuditLog::with('user:id,name');
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('action', 'like', "%{$s}%")->orWhere('module', 'like', "%{$s}%"));
        }
        if ($this->moduleFilter) $query->where('module', $this->moduleFilter);

        $logs = $query->latest()->get();
        $csv = "Date,User,Action,Module,Record ID,IP\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->created_at?->format('Y-m-d H:i:s') ?? '',
                $log->user?->name ?? '',
                $log->action ?? '',
                $log->module ?? '',
                $log->record_id ?? '',
                $log->ip_address ?? '',
            ]) . "\n";
        }

        return response()->streamDownload(function () use ($csv) { echo $csv; }, 'audit-logs.csv');
    }

    public function render()
    {
        $query = AuditLog::with('user:id,name');
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('action', 'like', "%{$s}%")->orWhere('module', 'like', "%{$s}%")->orWhere('ip_address', 'like', "%{$s}%"));
        }
        if ($this->moduleFilter) $query->where('module', $this->moduleFilter);

        return view('livewire.settings.audit-logs', [
            'logs' => $query->latest()->paginate(50),
            'modules' => AuditLog::distinct()->pluck('module')->filter()->sort()->values(),
        ])->layout('components.layouts.app', ['title' => 'Audit logs']);
    }
}

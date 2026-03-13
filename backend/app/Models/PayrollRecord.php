<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payroll_no',
        'employee_id',
        'period_month',
        'period_year',
        'base_salary',
        'overtime_hours',
        'overtime_rate',
        'overtime_amount',
        'allowances',
        'total_allowances',
        'deductions',
        'total_deductions',
        'gross_pay',
        'tax_amount',
        'net_pay',
        'status',
        'paid_date',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'allowances' => 'array',
            'deductions' => 'array',
            'base_salary' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'overtime_rate' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'total_allowances' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'gross_pay' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'paid_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

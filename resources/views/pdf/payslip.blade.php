<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip {{ $payroll->payroll_no }}</title>
</head>
<body style="font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; padding: 20px; color: #333;">
    <div style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 5px 0; font-size: 24px; color: #1a1a1a;">{{ $company['name'] }}</h1>
        @if($company['address'])
            <p style="margin: 0; color: #666;">{{ $company['address'] }}</p>
        @endif
        @if($company['phone'] || $company['email'])
            <p style="margin: 5px 0 0 0; color: #666;">
                @if($company['phone']){{ $company['phone'] }}@endif
                @if($company['phone'] && $company['email']) | @endif
                @if($company['email']){{ $company['email'] }}@endif
            </p>
        @endif
    </div>

    <div style="margin-bottom: 30px;">
        <h2 style="margin: 0 0 20px 0; font-size: 20px; color: #1a1a1a;">PAYSLIP</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 4px 0; width: 140px;">Payslip No:</td>
                <td style="padding: 4px 0; font-weight: bold;">{{ $payroll->payroll_no }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Period:</td>
                <td style="padding: 4px 0;">{{ \Carbon\Carbon::createFromDate($payroll->period_year, $payroll->period_month, 1)->format('F Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Employee:</td>
                <td style="padding: 4px 0; font-weight: bold;">{{ $payroll->employee?->name ?? 'N/A' }}</td>
            </tr>
            @if($payroll->employee?->employee_id)
            <tr>
                <td style="padding: 4px 0;">Employee ID:</td>
                <td style="padding: 4px 0;">{{ $payroll->employee->employee_id }}</td>
            </tr>
            @endif
            @if($payroll->employee?->department)
            <tr>
                <td style="padding: 4px 0;">Department:</td>
                <td style="padding: 4px 0;">{{ $payroll->employee->department }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 4px 0;">Status:</td>
                <td style="padding: 4px 0; text-transform: capitalize;">{{ $payroll->status }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #1a1a1a;">Earnings</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0;">Base Salary</td>
                <td style="padding: 6px 0; text-align: right;">{{ number_format($payroll->base_salary, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @if((float) $payroll->overtime_amount > 0)
            <tr>
                <td style="padding: 6px 0;">Overtime ({{ $payroll->overtime_hours }} hrs)</td>
                <td style="padding: 6px 0; text-align: right;">{{ number_format($payroll->overtime_amount, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @endif
            @if((float) $payroll->total_allowances > 0)
            <tr>
                <td style="padding: 6px 0;">Allowances</td>
                <td style="padding: 6px 0; text-align: right;">{{ number_format($payroll->total_allowances, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @if(is_array($payroll->allowances) && count($payroll->allowances) > 0)
                @foreach($payroll->allowances as $a)
                <tr>
                    <td style="padding: 4px 0 4px 20px; font-size: 11px;">{{ $a['name'] ?? 'Allowance' }}</td>
                    <td style="padding: 4px 0; text-align: right;">{{ number_format($a['amount'] ?? 0, 2) }} {{ $company['currency'] }}</td>
                </tr>
                @endforeach
            @endif
            @endif
            <tr style="font-weight: bold;">
                <td style="padding: 10px 0; border-top: 1px solid #ddd;">Gross Pay</td>
                <td style="padding: 10px 0; text-align: right; border-top: 1px solid #ddd;">{{ number_format($payroll->gross_pay, 2) }} {{ $company['currency'] }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #1a1a1a;">Deductions</h3>
        <table style="width: 100%; border-collapse: collapse;">
            @if((float) $payroll->total_deductions > 0)
            <tr>
                <td style="padding: 6px 0;">Deductions</td>
                <td style="padding: 6px 0; text-align: right;">-{{ number_format($payroll->total_deductions, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @if(is_array($payroll->deductions) && count($payroll->deductions) > 0)
                @foreach($payroll->deductions as $d)
                <tr>
                    <td style="padding: 4px 0 4px 20px; font-size: 11px;">{{ $d['name'] ?? 'Deduction' }}</td>
                    <td style="padding: 4px 0; text-align: right;">-{{ number_format($d['amount'] ?? 0, 2) }} {{ $company['currency'] }}</td>
                </tr>
                @endforeach
            @endif
            @endif
            @if((float) $payroll->tax_amount > 0)
            <tr>
                <td style="padding: 6px 0;">Tax</td>
                <td style="padding: 6px 0; text-align: right;">-{{ number_format($payroll->tax_amount, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5;">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 16px; font-weight: bold;">Net Pay</td>
                <td style="text-align: right; font-size: 18px; font-weight: bold;">{{ number_format($payroll->net_pay, 2) }} {{ $company['currency'] }}</td>
            </tr>
        </table>
    </div>

    @if($payroll->paid_date)
    <p style="margin-top: 30px; font-size: 11px; color: #666;">Paid on: {{ $payroll->paid_date->format('Y-m-d') }}</p>
    @endif

    @if($payroll->notes)
    <div style="margin-top: 20px;">
        <p style="margin: 0; font-size: 11px; color: #666;">{{ $payroll->notes }}</p>
    </div>
    @endif
</body>
</html>

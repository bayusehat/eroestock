<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} Report</title>
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
        <h2 style="margin: 0 0 10px 0; font-size: 20px; color: #1a1a1a;">{{ $title }} Report</h2>
        <p style="margin: 0; color: #666;">
            @if(isset($report['date_from']) && isset($report['date_to']))
                Period: {{ $report['date_from'] }} to {{ $report['date_to'] }}
            @elseif(isset($report['as_of_date']))
                As of: {{ $report['as_of_date'] }}
            @endif
        </p>
    </div>

    @if($type === 'profit-loss')
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="padding: 10px 8px; text-align: left; border-bottom: 2px solid #ddd;">Account</th>
                <th style="padding: 10px 8px; text-align: right; border-bottom: 2px solid #ddd;">Amount ({{ $company['currency'] }})</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="2" style="padding: 8px 0; font-weight: bold;">Revenue</td></tr>
            @foreach($report['revenue_accounts'] ?? [] as $item)
            <tr>
                <td style="padding: 6px 8px 6px 20px;">{{ $item['code'] }} - {{ $item['name'] }}</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td style="padding: 10px 8px; border-top: 1px solid #ddd;">Total Revenue</td>
                <td style="padding: 10px 8px; text-align: right; border-top: 1px solid #ddd;">{{ number_format($report['total_revenue'] ?? 0, 2) }}</td>
            </tr>
            <tr><td colspan="2" style="padding: 15px 0 8px 0; font-weight: bold;">Expenses</td></tr>
            @foreach($report['expense_accounts'] ?? [] as $item)
            <tr>
                <td style="padding: 6px 8px 6px 20px;">{{ $item['code'] }} - {{ $item['name'] }}</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td style="padding: 10px 8px; border-top: 1px solid #ddd;">Total Expenses</td>
                <td style="padding: 10px 8px; text-align: right; border-top: 1px solid #ddd;">{{ number_format($report['total_expenses'] ?? 0, 2) }}</td>
            </tr>
            <tr style="font-weight: bold; font-size: 14px;">
                <td style="padding: 15px 8px; border-top: 2px solid #333;">Net Profit</td>
                <td style="padding: 15px 8px; text-align: right; border-top: 2px solid #333;">{{ number_format($report['net_profit'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @elseif($type === 'balance-sheet')
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="padding: 10px 8px; text-align: left; border-bottom: 2px solid #ddd;">Account</th>
                <th style="padding: 10px 8px; text-align: right; border-bottom: 2px solid #ddd;">Balance ({{ $company['currency'] }})</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="2" style="padding: 8px 0; font-weight: bold;">Assets</td></tr>
            @foreach($report['assets'] ?? [] as $item)
            <tr>
                <td style="padding: 6px 8px 6px 20px;">{{ $item['code'] }} - {{ $item['name'] }}</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($item['balance'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td style="padding: 10px 8px; border-top: 1px solid #ddd;">Total Assets</td>
                <td style="padding: 10px 8px; text-align: right; border-top: 1px solid #ddd;">{{ number_format($report['total_assets'] ?? 0, 2) }}</td>
            </tr>
            <tr><td colspan="2" style="padding: 15px 0 8px 0; font-weight: bold;">Liabilities</td></tr>
            @foreach($report['liabilities'] ?? [] as $item)
            <tr>
                <td style="padding: 6px 8px 6px 20px;">{{ $item['code'] }} - {{ $item['name'] }}</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($item['balance'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td style="padding: 10px 8px; border-top: 1px solid #ddd;">Total Liabilities</td>
                <td style="padding: 10px 8px; text-align: right; border-top: 1px solid #ddd;">{{ number_format($report['total_liabilities'] ?? 0, 2) }}</td>
            </tr>
            <tr><td colspan="2" style="padding: 15px 0 8px 0; font-weight: bold;">Equity</td></tr>
            @foreach($report['equity'] ?? [] as $item)
            <tr>
                <td style="padding: 6px 8px 6px 20px;">{{ $item['code'] }} - {{ $item['name'] }}</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($item['balance'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td style="padding: 10px 8px; border-top: 1px solid #ddd;">Total Equity</td>
                <td style="padding: 10px 8px; text-align: right; border-top: 1px solid #ddd;">{{ number_format($report['total_equity'] ?? 0, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="padding: 10px 8px; border-top: 1px solid #ddd;">Total Liabilities & Equity</td>
                <td style="padding: 10px 8px; text-align: right; border-top: 1px solid #ddd;">{{ number_format($report['total_liabilities_equity'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <p style="margin-top: 30px; font-size: 11px; color: #999;">Generated on {{ now()->format('Y-m-d H:i') }}</p>
</body>
</html>

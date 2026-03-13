<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_no }}</title>
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
        @if($company['tax_id'])
            <p style="margin: 5px 0 0 0; color: #666;">Tax ID: {{ $company['tax_id'] }}</p>
        @endif
    </div>

    <div style="margin-bottom: 30px;">
        <h2 style="margin: 0 0 20px 0; font-size: 20px; color: #1a1a1a;">INVOICE</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 4px 0; width: 140px;">Invoice No:</td>
                <td style="padding: 4px 0; font-weight: bold;">{{ $invoice->invoice_no }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Issue Date:</td>
                <td style="padding: 4px 0;">{{ $invoice->issue_date?->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Due Date:</td>
                <td style="padding: 4px 0;">{{ $invoice->due_date?->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Status:</td>
                <td style="padding: 4px 0; text-transform: capitalize;">{{ $invoice->status }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px;">
        <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #1a1a1a;">Bill To</h3>
        <p style="margin: 0; font-weight: bold;">{{ $invoice->client?->name ?? 'N/A' }}</p>
        @if($invoice->client?->address)
            <p style="margin: 5px 0 0 0;">{{ $invoice->client->address }}</p>
        @endif
        @if($invoice->client?->email)
            <p style="margin: 5px 0 0 0;">{{ $invoice->client->email }}</p>
        @endif
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="padding: 10px 8px; text-align: left; border-bottom: 2px solid #ddd;">Description</th>
                <th style="padding: 10px 8px; text-align: right; border-bottom: 2px solid #ddd;">Qty</th>
                <th style="padding: 10px 8px; text-align: right; border-bottom: 2px solid #ddd;">Unit Price</th>
                <th style="padding: 10px 8px; text-align: right; border-bottom: 2px solid #ddd;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="padding: 10px 8px; border-bottom: 1px solid #eee;">{{ $item->description }}</td>
                <td style="padding: 10px 8px; text-align: right; border-bottom: 1px solid #eee;">{{ number_format($item->quantity, 2) }}</td>
                <td style="padding: 10px 8px; text-align: right; border-bottom: 1px solid #eee;">{{ number_format($item->unit_price, 2) }}</td>
                <td style="padding: 10px 8px; text-align: right; border-bottom: 1px solid #eee;">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-left: auto; width: 280px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0;">Subtotal:</td>
                <td style="padding: 6px 0; text-align: right;">{{ number_format($invoice->subtotal, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @if((float) $invoice->discount_amount > 0)
            <tr>
                <td style="padding: 6px 0;">Discount:</td>
                <td style="padding: 6px 0; text-align: right;">-{{ number_format($invoice->discount_amount, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @endif
            @if((float) $invoice->tax_amount > 0)
            <tr>
                <td style="padding: 6px 0;">Tax:</td>
                <td style="padding: 6px 0; text-align: right;">{{ number_format($invoice->tax_amount, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @endif
            <tr style="font-weight: bold; font-size: 14px;">
                <td style="padding: 10px 0; border-top: 2px solid #333;">Grand Total:</td>
                <td style="padding: 10px 0; text-align: right; border-top: 2px solid #333;">{{ number_format($invoice->grand_total, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @if((float) $invoice->amount_paid > 0)
            <tr>
                <td style="padding: 6px 0;">Amount Paid:</td>
                <td style="padding: 6px 0; text-align: right;">{{ number_format($invoice->amount_paid, 2) }} {{ $company['currency'] }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0;">Balance Due:</td>
                <td style="padding: 6px 0; text-align: right;">{{ number_format($invoice->balance_due, 2) }} {{ $company['currency'] }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if($payment_terms)
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; font-size: 11px; color: #666;">Payment terms: Net {{ $payment_terms }} days</p>
    </div>
    @endif

    @if($invoice->terms)
    <div style="margin-top: 20px;">
        <p style="margin: 0; font-size: 11px; color: #666;">{{ $invoice->terms }}</p>
    </div>
    @endif

    @if($invoice->notes)
    <div style="margin-top: 20px;">
        <p style="margin: 0; font-size: 11px; color: #666;">Notes: {{ $invoice->notes }}</p>
    </div>
    @endif
</body>
</html>

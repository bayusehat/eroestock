<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\PayrollRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function invoicePdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'workOrder']);
        $settings = CompanySetting::all()->keyBy('key')->map(fn ($s) => $s->value)->toArray();

        $data = [
            'invoice' => $invoice,
            'company' => [
                'name' => $settings['company_name'] ?? 'Company',
                'address' => $settings['address'] ?? null,
                'phone' => $settings['phone'] ?? null,
                'email' => $settings['email'] ?? null,
                'tax_id' => $settings['tax_id'] ?? null,
                'currency' => $settings['currency'] ?? 'IDR',
            ],
            'payment_terms' => $settings['default_payment_terms'] ?? '30',
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('invoice-' . $invoice->invoice_no . '.pdf');
    }

    public function payslipPdf(PayrollRecord $payroll)
    {
        $payroll->load('employee');
        $settings = CompanySetting::all()->keyBy('key')->map(fn ($s) => $s->value)->toArray();

        $data = [
            'payroll' => $payroll,
            'company' => [
                'name' => $settings['company_name'] ?? 'Company',
                'address' => $settings['address'] ?? null,
                'phone' => $settings['phone'] ?? null,
                'email' => $settings['email'] ?? null,
                'currency' => $settings['currency'] ?? 'IDR',
            ],
        ];

        $pdf = Pdf::loadView('pdf.payslip', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('payslip-' . $payroll->payroll_no . '.pdf');
    }

    public function reportPdf(Request $request, string $type)
    {
        $allowedTypes = ['profit-loss', 'balance-sheet'];
        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid report type. Allowed: profit-loss, balance-sheet',
            ], 422);
        }

        $year = now()->year;
        $dateFrom = $request->filled('date_from') ? $request->date_from : "{$year}-01-01";
        $dateTo = $request->filled('date_to') ? $request->date_to : "{$year}-12-31";

        $reportController = app(\App\Http\Controllers\Api\V1\ReportController::class);
        $reportRequest = new Request([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        if ($type === 'profit-loss') {
            $response = $reportController->profitLoss($reportRequest);
        } else {
            $response = $reportController->balanceSheet($reportRequest);
        }

        $reportData = json_decode($response->getContent(), true)['data'] ?? [];
        $settings = CompanySetting::all()->keyBy('key')->map(fn ($s) => $s->value)->toArray();

        $data = [
            'type' => $type,
            'title' => ucwords(str_replace('-', ' ', $type)),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'report' => $reportData,
            'company' => [
                'name' => $settings['company_name'] ?? 'Company',
                'address' => $settings['address'] ?? null,
                'phone' => $settings['phone'] ?? null,
                'email' => $settings['email'] ?? null,
                'currency' => $settings['currency'] ?? 'IDR',
            ],
        ];

        $pdf = Pdf::loadView('pdf.report', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('report-' . $type . '.pdf');
    }
}

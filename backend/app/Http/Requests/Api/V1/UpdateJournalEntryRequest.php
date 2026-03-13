<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'lines' => ['nullable', 'array', 'min:2'],
            'lines.*.account_id' => ['required_with:lines', 'exists:accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);
            if (empty($lines)) {
                return;
            }

            $totalDebits = '0';
            $totalCredits = '0';

            foreach ($lines as $index => $line) {
                $debit = isset($line['debit']) ? (string) $line['debit'] : '0';
                $credit = isset($line['credit']) ? (string) $line['credit'] : '0';

                if (bccomp($debit, '0', 2) > 0 && bccomp($credit, '0', 2) > 0) {
                    $validator->errors()->add("lines.{$index}", 'A line cannot have both debit and credit.');
                }

                if (bccomp($debit, '0', 2) === 0 && bccomp($credit, '0', 2) === 0) {
                    $validator->errors()->add("lines.{$index}", 'A line must have either debit or credit.');
                }

                $totalDebits = bcadd($totalDebits, $debit, 2);
                $totalCredits = bcadd($totalCredits, $credit, 2);
            }

            if (bccomp($totalDebits, $totalCredits, 2) !== 0) {
                $validator->errors()->add('lines', 'Total debits must equal total credits.');
            }
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1-0000', 'name' => 'ASSETS', 'type' => 'asset', 'parent_id' => null, 'is_header' => true],
            ['code' => '1-1000', 'name' => 'Cash & Bank', 'type' => 'asset', 'parent_code' => '1-0000', 'is_header' => true],
            ['code' => '1-1001', 'name' => 'Petty Cash', 'type' => 'asset', 'parent_code' => '1-1000', 'is_header' => false],
            ['code' => '1-1002', 'name' => 'Bank Account - Primary', 'type' => 'asset', 'parent_code' => '1-1000', 'is_header' => false],
            ['code' => '1-1003', 'name' => 'Bank Account - Secondary', 'type' => 'asset', 'parent_code' => '1-1000', 'is_header' => false],
            ['code' => '1-2000', 'name' => 'Accounts Receivable', 'type' => 'asset', 'parent_code' => '1-0000', 'is_header' => false],
            ['code' => '1-3000', 'name' => 'Inventory', 'type' => 'asset', 'parent_code' => '1-0000', 'is_header' => false],
            ['code' => '1-4000', 'name' => 'Fixed Assets', 'type' => 'asset', 'parent_code' => '1-0000', 'is_header' => true],
            ['code' => '1-4001', 'name' => 'Equipment', 'type' => 'asset', 'parent_code' => '1-4000', 'is_header' => false],
            ['code' => '1-4002', 'name' => 'Vehicles', 'type' => 'asset', 'parent_code' => '1-4000', 'is_header' => false],
            ['code' => '2-0000', 'name' => 'LIABILITIES', 'type' => 'liability', 'parent_id' => null, 'is_header' => true],
            ['code' => '2-1000', 'name' => 'Accounts Payable', 'type' => 'liability', 'parent_code' => '2-0000', 'is_header' => false],
            ['code' => '2-2000', 'name' => 'Tax Payable', 'type' => 'liability', 'parent_code' => '2-0000', 'is_header' => false],
            ['code' => '2-3000', 'name' => 'Salary Payable', 'type' => 'liability', 'parent_code' => '2-0000', 'is_header' => false],
            ['code' => '2-4000', 'name' => 'Loans', 'type' => 'liability', 'parent_code' => '2-0000', 'is_header' => false],
            ['code' => '3-0000', 'name' => 'EQUITY', 'type' => 'equity', 'parent_id' => null, 'is_header' => true],
            ['code' => '3-1000', 'name' => "Owner's Capital", 'type' => 'equity', 'parent_code' => '3-0000', 'is_header' => false],
            ['code' => '3-2000', 'name' => 'Retained Earnings', 'type' => 'equity', 'parent_code' => '3-0000', 'is_header' => false],
            ['code' => '4-0000', 'name' => 'REVENUE', 'type' => 'revenue', 'parent_id' => null, 'is_header' => true],
            ['code' => '4-1000', 'name' => 'Service Revenue', 'type' => 'revenue', 'parent_code' => '4-0000', 'is_header' => false],
            ['code' => '4-2000', 'name' => 'Sales Revenue', 'type' => 'revenue', 'parent_code' => '4-0000', 'is_header' => false],
            ['code' => '4-9000', 'name' => 'Other Revenue', 'type' => 'revenue', 'parent_code' => '4-0000', 'is_header' => false],
            ['code' => '5-0000', 'name' => 'EXPENSES', 'type' => 'expense', 'parent_id' => null, 'is_header' => true],
            ['code' => '5-1000', 'name' => 'Salary & Wages', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-2000', 'name' => 'Rent Expense', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-3000', 'name' => 'Utilities Expense', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-4000', 'name' => 'Office Supplies', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-5000', 'name' => 'Transportation', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-6000', 'name' => 'Depreciation', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-7000', 'name' => 'Tax Expense', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-9000', 'name' => 'Miscellaneous Expense', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
        ];

        $created = [];
        foreach ($accounts as $data) {
            $parentId = null;
            if (isset($data['parent_code'])) {
                $parentId = $created[$data['parent_code']] ?? Account::where('code', $data['parent_code'])->value('id');
            } elseif (isset($data['parent_id'])) {
                $parentId = $data['parent_id'];
            }

            $account = Account::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'parent_id' => $parentId,
                    'is_header' => $data['is_header'],
                    'is_system' => true,
                ]
            );
            $created[$data['code']] = $account->id;
        }
    }
}

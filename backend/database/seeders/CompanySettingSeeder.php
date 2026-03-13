<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name' => 'My Company',
            'company_logo' => null,
            'address' => null,
            'phone' => null,
            'email' => null,
            'tax_id' => null,
            'currency' => 'IDR',
            'fiscal_year_start' => '1',
            'invoice_prefix' => 'INV',
            'wo_prefix' => 'WO',
            'default_payment_terms' => '30',
            'date_format' => 'Y-m-d',
        ];

        foreach ($settings as $key => $value) {
            CompanySetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ASET
            ['code' => '1-0000', 'name' => 'ASET', 'type' => 'asset', 'parent_id' => null, 'is_header' => true],
            ['code' => '1-1000', 'name' => 'Kas & Bank', 'type' => 'asset', 'parent_code' => '1-0000', 'is_header' => true],
            ['code' => '1-1001', 'name' => 'Kas Kecil', 'type' => 'asset', 'parent_code' => '1-1000', 'is_header' => false],
            ['code' => '1-1002', 'name' => 'Rekening Bank Utama', 'type' => 'asset', 'parent_code' => '1-1000', 'is_header' => false],
            ['code' => '1-1003', 'name' => 'Rekening Bank Lainnya', 'type' => 'asset', 'parent_code' => '1-1000', 'is_header' => false],
            ['code' => '1-2000', 'name' => 'Piutang Usaha', 'type' => 'asset', 'parent_code' => '1-0000', 'is_header' => false],
            ['code' => '1-3000', 'name' => 'Persediaan Barang', 'type' => 'asset', 'parent_code' => '1-0000', 'is_header' => false],
            ['code' => '1-4000', 'name' => 'Aset Tetap', 'type' => 'asset', 'parent_code' => '1-0000', 'is_header' => true],
            ['code' => '1-4001', 'name' => 'Peralatan', 'type' => 'asset', 'parent_code' => '1-4000', 'is_header' => false],
            ['code' => '1-4002', 'name' => 'Kendaraan', 'type' => 'asset', 'parent_code' => '1-4000', 'is_header' => false],
            // KEWAJIBAN
            ['code' => '2-0000', 'name' => 'KEWAJIBAN', 'type' => 'liability', 'parent_id' => null, 'is_header' => true],
            ['code' => '2-1000', 'name' => 'Utang Usaha', 'type' => 'liability', 'parent_code' => '2-0000', 'is_header' => false],
            ['code' => '2-2000', 'name' => 'Utang Pajak', 'type' => 'liability', 'parent_code' => '2-0000', 'is_header' => false],
            ['code' => '2-3000', 'name' => 'Utang Gaji', 'type' => 'liability', 'parent_code' => '2-0000', 'is_header' => false],
            ['code' => '2-4000', 'name' => 'Pinjaman Bank', 'type' => 'liability', 'parent_code' => '2-0000', 'is_header' => false],
            // EKUITAS
            ['code' => '3-0000', 'name' => 'EKUITAS', 'type' => 'equity', 'parent_id' => null, 'is_header' => true],
            ['code' => '3-1000', 'name' => 'Modal Pemilik', 'type' => 'equity', 'parent_code' => '3-0000', 'is_header' => false],
            ['code' => '3-2000', 'name' => 'Laba Ditahan', 'type' => 'equity', 'parent_code' => '3-0000', 'is_header' => false],
            // PENDAPATAN
            ['code' => '4-0000', 'name' => 'PENDAPATAN', 'type' => 'revenue', 'parent_id' => null, 'is_header' => true],
            ['code' => '4-1000', 'name' => 'Pendapatan Jasa', 'type' => 'revenue', 'parent_code' => '4-0000', 'is_header' => false],
            ['code' => '4-2000', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'parent_code' => '4-0000', 'is_header' => false],
            ['code' => '4-9000', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'parent_code' => '4-0000', 'is_header' => false],
            // BEBAN
            ['code' => '5-0000', 'name' => 'BEBAN', 'type' => 'expense', 'parent_id' => null, 'is_header' => true],
            ['code' => '5-1000', 'name' => 'Beban Gaji & Upah', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-2000', 'name' => 'Beban Sewa', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-3000', 'name' => 'Beban Listrik & Air', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-4000', 'name' => 'Beban Perlengkapan Kantor', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-5000', 'name' => 'Beban Transportasi', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-6000', 'name' => 'Beban Penyusutan', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-7000', 'name' => 'Beban Pajak', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
            ['code' => '5-9000', 'name' => 'Beban Lain-lain', 'type' => 'expense', 'parent_code' => '5-0000', 'is_header' => false],
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

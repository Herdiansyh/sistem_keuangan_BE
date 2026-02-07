<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create parent accounts
        $accounts = [
            // Asset Accounts
            [
                'code' => '1000',
                'name' => 'Kas',
                'type' => 'asset',
                'is_active' => true,
                'parent_id' => null,
                'opening_balance' => 10000000,
                'description' => 'Akun kas utama perusahaan',
            ],
            [
                'code' => '1100', 
                'name' => 'Bank',
                'type' => 'asset',
                'is_active' => true,
                'parent_id' => null,
                'opening_balance' => 50000000,
                'description' => 'Akun bank perusahaan',
            ],
            [
                'code' => '2000',
                'name' => 'Piutang Usaha',
                'type' => 'asset', 
                'is_active' => true,
                'parent_id' => null,
                'opening_balance' => 15000000,
                'description' => 'Piutang dari pelanggan',
            ],
            
            // Liability Accounts
            [
                'code' => '3000',
                'name' => 'Utang Usaha',
                'type' => 'liability',
                'is_active' => true,
                'parent_id' => null,
                'opening_balance' => 8000000,
                'description' => 'Utang kepada supplier',
            ],
            [
                'code' => '3100',
                'name' => 'Utang Bank',
                'type' => 'liability',
                'is_active' => true,
                'parent_id' => null,
                'opening_balance' => 25000000,
                'description' => 'Pinjaman dari bank',
            ],
            
            // Equity Accounts
            [
                'code' => '4000',
                'name' => 'Modal Saham',
                'type' => 'equity',
                'is_active' => true,
                'parent_id' => null,
                'opening_balance' => 100000000,
                'description' => 'Modal saam pemilik',
            ],
            
            // Revenue Accounts
            [
                'code' => '5000',
                'name' => 'Pendapatan Usaha',
                'type' => 'revenue',
                'is_active' => true,
                'parent_id' => null,
                'opening_balance' => 0,
                'description' => 'Pendapatan dari penjualan',
            ],
            
            // Expense Accounts
            [
                'code' => '6000',
                'name' => 'Beban Operasional',
                'type' => 'expense',
                'is_active' => true,
                'parent_id' => null,
                'opening_balance' => 0,
                'description' => 'Beban operasional harian',
            ],
        ];

        // Insert parent accounts
        foreach ($accounts as $account) {
            Account::create($account);
        }

        // Create child accounts for Kas
        $parentKas = Account::where('code', '1000')->first();
        
        $childAccounts = [
            [
                'code' => '1001',
                'name' => 'Kas Kecil',
                'type' => 'asset',
                'is_active' => true,
                'parent_id' => $parentKas->id,
                'opening_balance' => 2000000,
                'description' => 'Kas kecil untuk operasional',
            ],
            [
                'code' => '1002',
                'name' => 'Kas Besar',
                'type' => 'asset',
                'is_active' => true,
                'parent_id' => $parentKas->id,
                'opening_balance' => 8000000,
                'description' => 'Kas besar untuk transaksi utama',
            ],
        ];

        // Insert child accounts
        foreach ($childAccounts as $account) {
            Account::create($account);
        }

        $this->command->info('✅ AccountSeeder completed successfully!');
        $this->command->info('📊 Created ' . count($accounts) . ' parent accounts');
        $this->command->info('📊 Created ' . count($childAccounts) . ' child accounts');
        $this->command->info('💰 Total accounts: ' . Account::count());
    }
}

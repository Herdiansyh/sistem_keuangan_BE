<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing accounts
        $accounts = Account::where('is_active', true)->get();
        
        if ($accounts->isEmpty()) {
            $this->command->info('❌ No active accounts found. Please run AccountSeeder first.');
            return;
        }

        $transactions = [];
        
        // Create sample transactions for each account type
        foreach ($accounts as $account) {
            // Create 2-3 transactions per account
            $numTransactions = rand(2, 4);
            
            for ($i = 0; $i < $numTransactions; $i++) {
                $isDebit = rand(0, 1) === 1;
                $amount = rand(100000, 5000000); // 100ribu - 5juta
                
                $transactions[] = [
                    'account_id' => $account->id,
                    'transaction_date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                    'description' => $this->getRandomDescription($account->type, $isDebit),
                    'debit' => $isDebit ? $amount : 0,
                    'credit' => !$isDebit ? $amount : 0,
                    'notes' => 'Sample transaction ' . ($i + 1),
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ];
            }
        }
        
        // Insert transactions in batches
        foreach (array_chunk($transactions, 100) as $chunk) {
            Transaction::insert($chunk);
        }
        
        $this->command->info('✅ TransactionSeeder completed successfully!');
        $this->command->info('📊 Created ' . count($transactions) . ' transactions');
    }
    
    private function getRandomDescription(string $accountType, bool $isDebit): string
    {
        $descriptions = [
            'asset' => [
                'debit' => ['Pembelian kas', 'Penambahan modal', 'Investasi aset', 'Penjualan aset lama'],
                'credit' => ['Pembelian barang', 'Biaya operasional', 'Pengeluaran kas', 'Bayar hutang']
            ],
            'liability' => [
                'debit' => ['Pinjaman diterima', 'Hutang dicicil', 'Penerimaan kas', 'Modal tambahan'],
                'credit' => ['Bayar cicilan', 'Bunga hutang', 'Pengurangan hutang', 'Biaya administrasi']
            ],
            'equity' => [
                'debit' => ['Penambahan modal', 'Laba ditahan', 'Investasi pemilik', 'Setoran modal'],
                'credit' => ['Penarikan modal', 'Dividen dibayar', 'Rugi periode', 'Biaya ekuitas']
            ],
            'revenue' => [
                'debit' => ['Penjualan produk', 'Pendapatan jasa', 'Penerimaan piutang', 'Penjualan aset'],
                'credit' => ['Diskon penjualan', 'Retur penjualan', 'Potongan harga', 'Beban penjualan']
            ],
            'expense' => [
                'debit' => ['Biaya gaji', 'Biaya sewa', 'Biaya marketing', 'Biaya utilitas'],
                'credit' => ['Pembayaran supplier', 'Bayar gaji', 'Bayar sewa', 'Biaya operasional']
            ]
        ];
        
        $typeDescriptions = $descriptions[$accountType] ?? $descriptions['asset'];
        $transactionType = $isDebit ? 'debit' : 'credit';
        $typeDescriptions = $typeDescriptions[$transactionType] ?? $typeDescriptions['debit'];
        
        return $typeDescriptions[array_rand($typeDescriptions)];
    }
}

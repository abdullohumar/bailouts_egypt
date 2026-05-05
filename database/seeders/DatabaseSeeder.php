<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Super Admin ────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@bailouts.id'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // ── Demo Member ────────────────────────────────────────────────────────
        $member = User::firstOrCreate(
            ['email' => 'member@bailouts.id'],
            [
                'name'     => 'Demo Member',
                'password' => Hash::make('password'),
                'role'     => 'member',
            ]
        );

        // ── Sample Transactions ────────────────────────────────────────────────
        $samples = [
            // Income (green)
            [
                'user_id'          => $admin->id,
                'amount'           => 15_000_000,
                'description'      => 'Contract payment — Project Alpha',
                'transaction_date' => now()->subDays(3)->toDateString(),
                'type'             => 'in',
                'source'           => 'contract',
                'status'           => 'settled',
            ],
            // Personal bailout pending (orange)
            [
                'user_id'          => $member->id,
                'amount'           => 2_500_000,
                'description'      => 'Personal advance for office supplies',
                'transaction_date' => now()->subDays(2)->toDateString(),
                'type'             => 'out',
                'source'           => 'personal',
                'status'           => 'pending',
            ],
            // Expense contract (red)
            [
                'user_id'          => $admin->id,
                'amount'           => 750_000,
                'description'      => 'Server hosting fee Q2',
                'transaction_date' => now()->subDay()->toDateString(),
                'type'             => 'out',
                'source'           => 'contract',
                'status'           => 'settled',
            ],
            // Settled expense (red)
            [
                'user_id'          => $member->id,
                'amount'           => 500_000,
                'description'      => 'Travel reimbursement — settled',
                'transaction_date' => now()->toDateString(),
                'type'             => 'out',
                'source'           => 'personal',
                'status'           => 'settled',
            ],
        ];

        foreach ($samples as $data) {
            Transaction::firstOrCreate(
                ['description' => $data['description'], 'transaction_date' => $data['transaction_date']],
                $data
            );
        }
    }
}

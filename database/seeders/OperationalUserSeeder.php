<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperationalUserSeeder extends Seeder
{
    public function run(): void
    {
        $outlet = Outlet::query()->where('code', 'OUT-MAIN')->firstOrFail();

        $users = [
            ['name' => 'Owner ERP', 'email' => 'owner@erp.local', 'phone' => '081200000010', 'role' => UserRole::Owner],
            ['name' => 'Admin ERP', 'email' => 'admin@erp.local', 'phone' => '081200000011', 'role' => UserRole::Admin],
            ['name' => 'Supervisor ERP', 'email' => 'supervisor@erp.local', 'phone' => '081200000012', 'role' => UserRole::Supervisor],
            ['name' => 'Operator ERP', 'email' => 'operator@erp.local', 'phone' => '081200000013', 'role' => UserRole::Operator],
            ['name' => 'Kasir ERP', 'email' => 'kasir@erp.local', 'phone' => '081200000014', 'role' => UserRole::Cashier],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'outlet_id' => $outlet->id,
                    'name' => $user['name'],
                    'phone' => $user['phone'],
                    'role' => $user['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );
        }
    }
}

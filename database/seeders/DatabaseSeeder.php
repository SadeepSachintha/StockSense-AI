<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Tenant;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PosTransaction;
use App\Services\TenantManager;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        // 1. Create Demo Tenant (idempotent)
        $tenant = Tenant::firstOrCreate(
            ['id' => 'tenant_001'],
            [
                'name' => 'Global Goods Retailers',
                'subscription_tier' => 'premium',
                'status' => 'active'
            ]
        );

        // Create tenant_002 as well
        Tenant::firstOrCreate(
            ['id' => 'tenant_002'],
            [
                'name' => 'Apex Retail Store',
                'subscription_tier' => 'basic',
                'status' => 'active'
            ]
        );

        // Seed default owner & cashier users for demo tenants
        $password = \Hash::make('password');

        $demoUsers = [
            [
                'tenant_id' => 'tenant_001',
                'username' => 'owner',
                'password' => $password,
                'role' => 'owner',
                'name' => 'Sadeep',
            ],
            [
                'tenant_id' => 'tenant_001',
                'username' => 'cashier',
                'password' => $password,
                'role' => 'cashier',
                'name' => 'Janith',
            ],
            [
                'tenant_id' => 'tenant_002',
                'username' => 'owner',
                'password' => $password,
                'role' => 'owner',
                'name' => 'Maneesha',
            ],
            [
                'tenant_id' => 'tenant_002',
                'username' => 'cashier',
                'password' => $password,
                'role' => 'cashier',
                'name' => 'Kavindi',
            ]
        ];

        foreach ($demoUsers as $u) {
            \App\Models\TenantUser::firstOrCreate(
                ['tenant_id' => $u['tenant_id'], 'username' => $u['username']],
                [
                    'password' => $u['password'],
                    'role' => $u['role'],
                    'name' => $u['name'],
                ]
            );
        }

        // Resolve TenantManager and bind current session context
        app(TenantManager::class)->setTenantId('tenant_001');

        // 2. Create Demo Suppliers (idempotent)
        $s1 = Supplier::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'AgriBulk Corp',
                'email' => 'orders@agribulk.com',
                'phone' => '+15550199'
            ]
        );
        $s2 = Supplier::firstOrCreate(
            ['id' => 2],
            [
                'name' => 'GrownOrganic Farms',
                'email' => 'orders@grownorganic.com',
                'phone' => '+15550299'
            ]
        );
        $s3 = Supplier::firstOrCreate(
            ['id' => 3],
            [
                'name' => 'VeggieDistributors',
                'email' => 'orders@veggiedist.com',
                'phone' => '+15550399'
            ]
        );

        $supplier = $s1;

        // 3. Create Demo Products (idempotent)
        $rice = Product::firstOrCreate(
            ['sku' => 'SKU-RICE-001'],
            [
                'supplier_id' => $supplier->id,
                'name' => 'Basmati Rice',
                'category' => 'Grains',
                'price' => 15.99,
                'elasticity' => -1.2,
                'current_stock' => 150,
                'safety_stock' => 30,
                'max_stock' => 500,
                'reorder_qty' => 100,
                'lead_time_days' => 5
            ]
        );

        $fertilizer = Product::firstOrCreate(
            ['sku' => 'SKU-FERT-002'],
            [
                'supplier_id' => $supplier->id,
                'name' => 'NPK Fertilizer',
                'category' => 'Agri',
                'price' => 29.99,
                'elasticity' => -0.8,
                'current_stock' => 45,
                'safety_stock' => 40,
                'max_stock' => 200,
                'reorder_qty' => 50,
                'lead_time_days' => 7
            ]
        );

        // 4. Generate daily transactional POS sales history for the last 30 days (if empty)
        if (PosTransaction::where('product_id', $rice->id)->count() === 0) {
            $startDate = now()->subDays(30);
            for ($i = 0; $i < 30; $i++) {
                $date = $startDate->copy()->addDays($i);
                
                // Rice daily sales around 20 units
                PosTransaction::create([
                    'id' => (string) Str::uuid(),
                    'product_id' => $rice->id,
                    'quantity' => rand(15, 25),
                    'price_sold' => 15.99,
                    'transaction_date' => $date
                ]);

                // Fertilizer daily sales around 5 units
                PosTransaction::create([
                    'id' => (string) Str::uuid(),
                    'product_id' => $fertilizer->id,
                    'quantity' => rand(3, 8),
                    'price_sold' => 29.99,
                    'transaction_date' => $date
                ]);
            }
        }
    }
}

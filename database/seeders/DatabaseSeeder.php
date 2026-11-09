<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $roles = [
            ['id' => 1, 'role_name' => 'UKasir'],
            ['id' => 2, 'role_name' => 'UGudang'],
            ['id' => 3, 'role_name' => 'UAdmin'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], ['role_name' => $r['role_name']]);
        }

        // 2. Seed Users
        $users = [
            [
                'id' => 1,
                'role_id' => 1,
                'username' => 'kasir',
                'password' => Hash::make('kasir123'),
                'name' => 'Siti Kasir',
                'is_active' => true,
            ],
            [
                'id' => 2,
                'role_id' => 2,
                'username' => 'gudang',
                'password' => Hash::make('gudang123'),
                'name' => 'Budi Gudang',
                'is_active' => true,
            ],
            [
                'id' => 3,
                'role_id' => 3,
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'name' => 'Agus Admin (Owner)',
                'is_active' => true,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['id' => $u['id']],
                [
                    'role_id' => $u['role_id'],
                    'username' => $u['username'],
                    'password' => $u['password'],
                    'name' => $u['name'],
                    'is_active' => $u['is_active'],
                ]
            );
        }

        // 3. Seed Products
        $products = [
            [
                'id' => 1,
                'barcode_code' => '8992388111018',
                'product_name' => 'Indomie Goreng Spesial 85g',
                'buy_price' => 2600,
                'sell_price' => 3200,
                'stock' => 120,
                'min_stock' => 20,
            ],
            [
                'id' => 2,
                'barcode_code' => '8992388111025',
                'product_name' => 'Indomie Kuah Ayam Bawang 75g',
                'buy_price' => 2500,
                'sell_price' => 3100,
                'stock' => 85,
                'min_stock' => 15,
            ],
            [
                'id' => 3,
                'barcode_code' => '8886008101053',
                'product_name' => 'Aqua Air Mineral 600ml',
                'buy_price' => 3000,
                'sell_price' => 4000,
                'stock' => 60,
                'min_stock' => 10,
            ],
            [
                'id' => 4,
                'barcode_code' => '8886008101091',
                'product_name' => 'Aqua Air Mineral 1500ml',
                'buy_price' => 5500,
                'sell_price' => 7000,
                'stock' => 40,
                'min_stock' => 8,
            ],
            [
                'id' => 5,
                'barcode_code' => '8996001414001',
                'product_name' => 'Teh Botol Sosro Kotak 250ml',
                'buy_price' => 3200,
                'sell_price' => 4500,
                'stock' => 50,
                'min_stock' => 10,
            ],
            [
                'id' => 6,
                'barcode_code' => '8999999052026',
                'product_name' => 'Sabun Lifebuoy Total 10 85g',
                'buy_price' => 3800,
                'sell_price' => 5000,
                'stock' => 35,
                'min_stock' => 10,
            ],
            [
                'id' => 7,
                'barcode_code' => '8998866200234',
                'product_name' => 'Pasta Gigi Pepsodent 120g',
                'buy_price' => 9500,
                'sell_price' => 12500,
                'stock' => 25,
                'min_stock' => 5,
            ],
            [
                'id' => 8,
                'barcode_code' => '8992753123456',
                'product_name' => 'Minyak Goreng Bimoli 1L',
                'buy_price' => 16500,
                'sell_price' => 19500,
                'stock' => 18,
                'min_stock' => 5,
            ],
            [
                'id' => 9,
                'barcode_code' => '8992753654321',
                'product_name' => 'Minyak Goreng Bimoli 2L',
                'buy_price' => 32000,
                'sell_price' => 37500,
                'stock' => 3, // LOW STOCK (< min_stock 5)
                'min_stock' => 5,
            ],
            [
                'id' => 10,
                'barcode_code' => '8993175537554',
                'product_name' => 'Kopi Kapal Api Spesial Mix 10x24g',
                'buy_price' => 11000,
                'sell_price' => 14000,
                'stock' => 4, // LOW STOCK (< min_stock 10)
                'min_stock' => 10,
            ],
            [
                'id' => 11,
                'barcode_code' => '8996001301011',
                'product_name' => 'Susu Ultra Milk Cokelat 250ml',
                'buy_price' => 5800,
                'sell_price' => 7500,
                'stock' => 45,
                'min_stock' => 10,
            ],
            [
                'id' => 12,
                'barcode_code' => '8998888123456',
                'product_name' => 'Beras Sania Premium 5kg',
                'buy_price' => 65000,
                'sell_price' => 74000,
                'stock' => 2, // LOW STOCK (< min_stock 5)
                'min_stock' => 5,
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['id' => $p['id']], $p);

            // Log initial stock as 'in'
            StockLog::updateOrCreate(
                ['product_id' => $p['id'], 'notes' => 'Stok Awal Sistem'],
                [
                    'user_id' => 2, // Gudang user
                    'type' => 'in',
                    'qty' => $p['stock'],
                    'created_at' => Carbon::now()->subDays(2),
                ]
            );
        }

        // 4. Seed Initial Sales (so Admin Dashboard has immediate sample data)
        $sale1 = Sale::updateOrCreate(
            ['invoice_number' => 'INV-20261019-0001'],
            [
                'user_id' => 1, // Kasir
                'total_amount' => 42400,
                'pay_amount' => 50000,
                'change_amount' => 7600,
                'created_at' => Carbon::yesterday()->setHour(14)->setMinute(20),
            ]
        );

        SaleDetail::updateOrCreate(
            ['sale_id' => $sale1->id, 'product_id' => 1],
            [
                'qty' => 5,
                'buy_price' => 2600,
                'sell_price' => 3200,
                'subtotal' => 16000,
                'profit' => (3200 - 2600) * 5, // 3000
            ]
        );

        SaleDetail::updateOrCreate(
            ['sale_id' => $sale1->id, 'product_id' => 3],
            [
                'qty' => 2,
                'buy_price' => 3000,
                'sell_price' => 4000,
                'subtotal' => 8000,
                'profit' => (4000 - 3000) * 2, // 2000
            ]
        );

        SaleDetail::updateOrCreate(
            ['sale_id' => $sale1->id, 'product_id' => 8],
            [
                'qty' => 1,
                'buy_price' => 16500,
                'sell_price' => 19500,
                'subtotal' => 19500,
                'profit' => 19500 - 16500, // 3000
            ]
        );

        $sale2 = Sale::updateOrCreate(
            ['invoice_number' => 'INV-20261020-0002'],
            [
                'user_id' => 1, // Kasir
                'total_amount' => 88000,
                'pay_amount' => 100000,
                'change_amount' => 12000,
                'created_at' => Carbon::today()->setHour(9)->setMinute(15),
            ]
        );

        SaleDetail::updateOrCreate(
            ['sale_id' => $sale2->id, 'product_id' => 12],
            [
                'qty' => 1,
                'buy_price' => 65000,
                'sell_price' => 74000,
                'subtotal' => 74000,
                'profit' => 74000 - 65000, // 9000
            ]
        );

        SaleDetail::updateOrCreate(
            ['sale_id' => $sale2->id, 'product_id' => 10],
            [
                'qty' => 1,
                'buy_price' => 11000,
                'sell_price' => 14000,
                'subtotal' => 14000,
                'profit' => 14000 - 11000, // 3000
            ]
        );
    }
}

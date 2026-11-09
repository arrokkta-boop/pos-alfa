<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlfaPosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Seed database
        $this->artisan('db:seed');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('TOKO RETAIL');
        $response->assertSee('ALFA');
    }

    public function test_kasir_role_authentication_and_access(): void
    {
        $kasir = User::where('username', 'kasir')->first();

        // 1. Can access kasir POS
        $response = $this->actingAs($kasir)->get('/kasir');
        $response->assertStatus(200);
        $response->assertSee('ALFA POS TERMINAL', false);

        // 2. Cannot access gudang (403 Forbidden)
        $responseGudang = $this->actingAs($kasir)->get('/gudang');
        $responseGudang->assertStatus(403);

        // 3. Cannot access admin (403 Forbidden)
        $responseAdmin = $this->actingAs($kasir)->get('/admin');
        $responseAdmin->assertStatus(403);
    }

    public function test_gudang_role_authentication_and_access(): void
    {
        $gudang = User::where('username', 'gudang')->first();

        // 1. Can access gudang
        $response = $this->actingAs($gudang)->get('/gudang');
        $response->assertStatus(200);
        $response->assertSee('Master Data Barang', false);

        // 2. Cannot access kasir (403)
        $responseKasir = $this->actingAs($gudang)->get('/kasir');
        $responseKasir->assertStatus(403);

        // 3. Cannot access admin (403)
        $responseAdmin = $this->actingAs($gudang)->get('/admin');
        $responseAdmin->assertStatus(403);
    }

    public function test_admin_role_authentication_and_access(): void
    {
        $admin = User::where('username', 'admin')->first();

        // 1. Can access admin dashboard (Read-Only)
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Total Omset', false);
        $response->assertSee('Laba Bersih', false);

        // 2. Cannot access kasir (403)
        $responseKasir = $this->actingAs($admin)->get('/kasir');
        $responseKasir->assertStatus(403);

        // 3. Cannot access gudang (403)
        $responseGudang = $this->actingAs($admin)->get('/gudang');
        $responseGudang->assertStatus(403);
    }

    public function test_pos_checkout_deducts_stock_and_creates_records(): void
    {
        $kasir = User::where('username', 'kasir')->first();
        $product = Product::first();
        $initialStock = $product->stock;
        $qtyToBuy = 2;

        $response = $this->actingAs($kasir)->postJson('/kasir/checkout', [
            'items' => [
                ['product_id' => $product->id, 'qty' => $qtyToBuy]
            ],
            'pay_amount' => $qtyToBuy * (float) $product->sell_price + 5000,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify stock decremented
        $product->refresh();
        $this->assertEquals($initialStock - $qtyToBuy, $product->stock);

        // Verify sale record exists
        $this->assertDatabaseHas('sales', [
            'user_id' => $kasir->id,
            'total_amount' => $qtyToBuy * (float) $product->sell_price,
        ]);

        // Verify stock log created with type 'out'
        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'user_id' => $kasir->id,
            'type' => 'out',
            'qty' => $qtyToBuy,
        ]);
    }

    public function test_gudang_stock_in_increases_stock_and_logs(): void
    {
        $gudang = User::where('username', 'gudang')->first();
        $product = Product::first();
        $initialStock = $product->stock;
        $qtyIn = 25;

        $response = $this->actingAs($gudang)->post('/gudang/stock-in', [
            'product_id' => $product->id,
            'qty' => $qtyIn,
            'notes' => 'Supplier Restock Test',
        ]);

        $response->assertRedirect('/gudang');

        $product->refresh();
        $this->assertEquals($initialStock + $qtyIn, $product->stock);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'user_id' => $gudang->id,
            'type' => 'in',
            'qty' => $qtyIn,
            'notes' => 'Supplier Restock Test',
        ]);
    }

    public function test_gudang_stock_adjustment(): void
    {
        $gudang = User::where('username', 'gudang')->first();
        $product = Product::first();
        $newStock = 77;

        $response = $this->actingAs($gudang)->post('/gudang/stock-adjustment', [
            'product_id' => $product->id,
            'actual_stock' => $newStock,
            'notes' => 'Opname Fisik Bulanan',
        ]);

        $response->assertRedirect('/gudang');

        $product->refresh();
        $this->assertEquals($newStock, $product->stock);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'user_id' => $gudang->id,
            'type' => 'adjustment',
        ]);
    }
}

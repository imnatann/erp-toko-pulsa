<?php

namespace Database\Seeders;

use App\Enums\DigitalServiceType;
use App\Models\DigitalService;
use App\Models\ManualChannel;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceCategory;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class CoreMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        Outlet::query()->firstOrCreate(
            ['code' => 'OUT-MAIN'],
            ['name' => 'Outlet Utama', 'address' => 'Jl. Operasional No. 1', 'phone' => '081200000001', 'is_active' => true],
        );

        $categories = [
            ['code' => 'PULSA', 'name' => 'Pulsa', 'service_type' => DigitalServiceType::Pulsa],
            ['code' => 'DATA', 'name' => 'Paket Data', 'service_type' => DigitalServiceType::Data],
            ['code' => 'EWALLET', 'name' => 'Top Up E-Wallet', 'service_type' => DigitalServiceType::EWallet],
            ['code' => 'TRANSFER', 'name' => 'Transfer Uang', 'service_type' => DigitalServiceType::Transfer],
            ['code' => 'CASHOUT', 'name' => 'Tarik Tunai', 'service_type' => DigitalServiceType::CashOut],
        ];

        foreach ($categories as $category) {
            ServiceCategory::query()->updateOrCreate(['code' => $category['code']], $category);
        }

        $services = [
            ['service_category_code' => 'PULSA', 'code' => 'PULSA-10K', 'name' => 'Pulsa Reguler 10K', 'provider' => 'Manual Provider', 'default_nominal_amount' => 10000, 'default_fee_amount' => 1500],
            ['service_category_code' => 'DATA', 'code' => 'DATA-5GB', 'name' => 'Paket Data 5GB', 'provider' => 'Manual Provider', 'default_nominal_amount' => 25000, 'default_fee_amount' => 2500],
            ['service_category_code' => 'EWALLET', 'code' => 'DANA-20K', 'name' => 'Top Up DANA 20K', 'provider' => 'Manual Provider', 'default_nominal_amount' => 20000, 'default_fee_amount' => 2000],
            ['service_category_code' => 'TRANSFER', 'code' => 'TRF-BANK', 'name' => 'Transfer Antar Bank', 'provider' => 'Manual Teller', 'default_nominal_amount' => 50000, 'default_fee_amount' => 6500],
            ['service_category_code' => 'CASHOUT', 'code' => 'CASHOUT-50K', 'name' => 'Tarik Tunai 50K', 'provider' => 'Manual Teller', 'default_nominal_amount' => 50000, 'default_fee_amount' => 5000],
        ];

        foreach ($services as $service) {
            $category = ServiceCategory::query()->where('code', $service['service_category_code'])->firstOrFail();

            DigitalService::query()->updateOrCreate(
                ['code' => $service['code']],
                [
                    'service_category_id' => $category->id,
                    'name' => $service['name'],
                    'provider' => $service['provider'],
                    'default_nominal_amount' => $service['default_nominal_amount'],
                    'default_fee_amount' => $service['default_fee_amount'],
                    'is_active' => true,
                    'requires_reference' => in_array($service['service_category_code'], ['TRANSFER', 'CASHOUT'], true),
                    'requires_destination_name' => in_array($service['service_category_code'], ['TRANSFER'], true),
                ],
            );
        }

        ManualChannel::query()->updateOrCreate(
            ['code' => 'MANUAL-APP'],
            ['name' => 'Aplikasi Manual Operator', 'channel_type' => 'app', 'notes' => 'Dipakai operator untuk proses semi-manual', 'is_active' => true],
        );

        $productCategory = ProductCategory::query()->updateOrCreate(
            ['code' => 'AKSESORIS'],
            ['name' => 'Aksesoris HP', 'is_active' => true],
        );

        $product = Product::query()->updateOrCreate(
            ['sku' => 'CHARGER-FAST-001'],
            [
                'product_category_id' => $productCategory->id,
                'name' => 'Charger Fast Charging',
                'purchase_price_amount' => 45000,
                'selling_price_amount' => 65000,
                'minimum_stock' => 3,
                'is_active' => true,
            ],
        );

        $outlet = Outlet::query()->where('code', 'OUT-MAIN')->firstOrFail();

        Stock::query()->updateOrCreate(
            ['outlet_id' => $outlet->id, 'product_id' => $product->id],
            ['on_hand_qty' => 10, 'reserved_qty' => 0, 'minimum_stock' => 3],
        );
    }
}

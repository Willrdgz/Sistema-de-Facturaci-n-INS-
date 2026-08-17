<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Business::firstOrCreate(['name' => 'Ferretería El Progreso'], ['trade_name' => 'El Progreso', 'phone' => '2222-0000', 'email' => 'ventas@elprogreso.test', 'address' => 'San Salvador, El Salvador', 'tax_rate' => 13]);
        $tools = Category::firstOrCreate(['name' => 'Herramientas'], ['description' => 'Herramientas manuales y eléctricas']);
        $construction = Category::firstOrCreate(['name' => 'Construcción'], ['description' => 'Materiales para obra']);
        Customer::firstOrCreate(['document' => '00000000-0'], ['name' => 'Consumidor final', 'phone' => '7000-0000']);
        Product::firstOrCreate(['code' => 'HER-001'], ['category_id' => $tools->id, 'name' => 'Martillo de uña 16 oz', 'cost' => 6.50, 'price' => 9.95, 'stock' => 18, 'minimum_stock' => 5]);
        Product::firstOrCreate(['code' => 'HER-002'], ['category_id' => $tools->id, 'name' => 'Destornillador plano', 'cost' => 2.25, 'price' => 3.75, 'stock' => 4, 'minimum_stock' => 5]);
        Product::firstOrCreate(['code' => 'CON-001'], ['category_id' => $construction->id, 'name' => 'Cemento gris 42.5 kg', 'cost' => 7.10, 'price' => 9.25, 'stock' => 30, 'minimum_stock' => 10]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'address' => 'Admin Address',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'active' => true,
        ]);

         // Create 10 customers
        User::factory(10)->create();

        // Create categories
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and gadgets', 'active' => true],
            ['name' => 'Clothing', 'description' => 'Fashion and apparel', 'active' => true],
            ['name' => 'Books', 'description' => 'Books and magazines', 'active' => true],
            ['name' => 'Home & Garden', 'description' => 'Home decor and garden supplies', 'active' => true],
            ['name' => 'Sports', 'description' => 'Sports equipment and accessories', 'active' => true],
        ];
         foreach ($categories as $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'description' => $cat['description'],
                'active' => $cat['active'],
            ]);
        }
        // Create products for each category
        $categories = Category::all();
        foreach ($categories as $category) {
            for ($i = 1; $i <= 5; $i++) {
                Product::create([
                    'category_id' => $category->id,
                    'sku' => Str::upper(Str::random(8)),
                    'name' => "{$category->name} Product {$i}",
                    'slug' => Str::slug("{$category->name} Product {$i}"),
                    'description' => "This is a sample product from the {$category->name} category.",
                    'price' => rand(10, 500),
                    'cost_price' => rand(5, 400),
                    'stock_qty' => rand(0, 100),
                    'active' => true,
                ]);
            }
        }
        

    }

    
}

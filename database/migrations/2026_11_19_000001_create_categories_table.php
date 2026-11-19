<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('icon')->nullable(); // URL icon / photo path
                $table->text('description')->nullable();
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Add category_id to products if not exists
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->after('category');
            });
        }

        // Seed initial default categories and migrate existing categories from products table
        $defaultCategories = [
            [
                'name'        => 'Pakaian',
                'slug'        => 'pakaian',
                'icon'        => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=300&auto=format&fit=crop&q=80',
                'description' => 'Koleksi busana pria, wanita, dan anak-anak modern.',
                'order'       => 1,
            ],
            [
                'name'        => 'Sepatu',
                'slug'        => 'sepatu',
                'icon'        => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=300&auto=format&fit=crop&q=80',
                'description' => 'Aneka sepatu sneakers, formal, dan olahraga.',
                'order'       => 2,
            ],
            [
                'name'        => 'Aksesoris',
                'slug'        => 'aksesoris',
                'icon'        => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=300&auto=format&fit=crop&q=80',
                'description' => 'Jam tangan, tas, kacamata, dan aksesoris penunjang gaya.',
                'order'       => 3,
            ],
            [
                'name'        => 'Gadget',
                'slug'        => 'gadget',
                'icon'        => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&auto=format&fit=crop&q=80',
                'description' => 'Perangkat elektronik pintar, aksesoris HP, dan audio.',
                'order'       => 4,
            ],
            [
                'name'        => 'Rumah Tangga',
                'slug'        => 'rumah-tangga',
                'icon'        => 'https://images.unsplash.com/photo-1585515320310-259814833e62?w=300&auto=format&fit=crop&q=80',
                'description' => 'Perlengkapan dekorasi dan kebutuhan rumah tangga.',
                'order'       => 5,
            ],
            [
                'name'        => 'ATK',
                'slug'        => 'atk',
                'icon'        => 'https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?w=300&auto=format&fit=crop&q=80',
                'description' => 'Alat tulis kantor, perlengkapan sekolah, dan kertas.',
                'order'       => 6,
            ],
        ];

        foreach ($defaultCategories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                [
                    'name'        => $cat['name'],
                    'icon'        => $cat['icon'],
                    'description' => $cat['description'],
                    'order'       => $cat['order'],
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        // Import any distinct existing product categories into categories table
        if (Schema::hasTable('products')) {
            $existingCategories = DB::table('products')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category');

            foreach ($existingCategories as $catName) {
                $slug = Str::slug($catName);
                if (empty($slug)) continue;

                $catId = DB::table('categories')->where('name', $catName)->orWhere('slug', $slug)->value('id');
                if (!$catId) {
                    $catId = DB::table('categories')->insertGetId([
                        'name'        => trim($catName),
                        'slug'        => $slug,
                        'description' => 'Kategori produk ' . trim($catName),
                        'order'       => 10,
                        'is_active'   => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                // Link category_id to products
                DB::table('products')
                    ->where('category', $catName)
                    ->update(['category_id' => $catId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('category_id');
            });
        }
        Schema::dropIfExists('categories');
    }
};

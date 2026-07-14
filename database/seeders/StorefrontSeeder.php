<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds: a handful of brands, banners, a sample coupon, and flips
 * existing categories+products to be visible on the storefront.
 *
 * Idempotent: rerunning won't create duplicates (firstOrCreate).
 */
class StorefrontSeeder extends Seeder
{
    public function run(): void
    {
        // ── Brands ──────────────────────────────────────────────────────────
        $brands = [
            ['name' => 'Almufeed Premium', 'description' => 'Our flagship in-house line.',     'is_featured' => true,  'sort_order' => 1],
            ['name' => 'Sajda',            'description' => 'Prayer and worship essentials.',  'is_featured' => true,  'sort_order' => 2],
            ['name' => 'Halal Goods',      'description' => 'Trusted halal-certified items.',  'is_featured' => true,  'sort_order' => 3],
            ['name' => 'Daily Use',        'description' => 'Everyday household must-haves.',  'is_featured' => true,  'sort_order' => 4],
            ['name' => 'Imported',         'description' => 'Curated imported quality.',       'is_featured' => false, 'sort_order' => 5],
        ];
        foreach ($brands as $b) {
            Brand::firstOrCreate(['slug' => Str::slug($b['name'])], array_merge($b, ['is_active' => true]));
        }

        // ── Banners ────────────────────────────────────────────────────────
        $banners = [
            [
                'title'     => 'Quality you can trust',
                'subtitle'  => 'New season',
                'cta_text'  => 'Shop the collection',
                'cta_url'   => '/shop',
                'image'     => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=1600&q=80',
                'position'  => 'hero',
                'sort_order'=> 1,
            ],
            [
                'title'     => 'Festive picks',
                'subtitle'  => 'Limited edition',
                'cta_text'  => 'Discover',
                'cta_url'   => '/shop',
                'image'     => 'https://images.unsplash.com/photo-1607083206869-4c7672e72a8a?auto=format&fit=crop&w=1200&q=80',
                'position'  => 'hero',
                'sort_order'=> 2,
            ],
            [
                'title'     => 'Free delivery on orders above Rs. 5,000',
                'subtitle'  => 'For a limited time',
                'cta_text'  => 'Shop now',
                'cta_url'   => '/shop',
                'image'     => 'https://images.unsplash.com/photo-1607082349566-187342175e2f?auto=format&fit=crop&w=1600&q=80',
                'position'  => 'mid',
                'sort_order'=> 1,
            ],
            [
                'title'     => 'Fresh new arrivals',
                'subtitle'  => 'Just in',
                'cta_text'  => 'Explore',
                'cta_url'   => '/shop?sort=newest',
                'image'     => 'https://images.unsplash.com/photo-1604176354204-9268737828e4?auto=format&fit=crop&w=1600&q=80',
                'position'  => 'mid',
                'sort_order'=> 2,
            ],
        ];
        foreach ($banners as $b) {
            Banner::firstOrCreate(['title' => $b['title']], array_merge($b, ['is_active' => true]));
        }

        // ── Coupon (sample) ────────────────────────────────────────────────
        Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type'             => 'percent',
                'value'            => 10,
                'min_order_amount' => 1000,
                'max_discount'     => 500,
                'is_active'        => true,
            ]
        );

        // ── Flip categories visible + feature 4 of them ────────────────────
        Category::query()->update(['is_active' => true]);
        Category::query()->take(4)->get()->each(fn ($c) => $c->update(['is_featured' => true, 'sort_order' => $c->id]));

        // ── Make all products visible on storefront ────────────────────────
        Product::query()->update(['show_on_website' => true]);

        // Feature ~10 products so the home page has rich content
        Product::query()->inRandomOrder()->limit(10)->get()
            ->each(fn ($p) => $p->update(['is_featured' => true]));

        $this->command->info('Storefront seeded:');
        $this->command->info('  Brands:     ' . Brand::count());
        $this->command->info('  Banners:    ' . Banner::count());
        $this->command->info('  Coupons:    ' . Coupon::count());
        $this->command->info('  Featured products: ' . Product::featured()->count());
        $this->command->info('  Featured categories: ' . Category::where('is_featured', true)->count());
    }
}

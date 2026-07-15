<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Seeds the starter Salal Collection content — a "Skin Care" category, three
 * demo products and the two homepage hero banners. Idempotent, so it is safe to
 * re-run. Images live under storage/app/public/{products,banners} (committed).
 */
class SalalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::firstOrCreate(
            ['code' => 'SLC'],
            ['name' => 'Salal Collection', 'is_active' => 1, 'show_on_website' => 1, 'order_start_number' => 1000]
        );
        $unitId = Unit::orderBy('id')->value('id');

        $category = Category::updateOrCreate(
            ['slug' => 'skin-care'],
            ['name' => 'Skin Care', 'branch_id' => $branch->id, 'is_active' => 1,
             'show_on_website' => 1, 'is_featured' => 1, 'sort_order' => 1]
        );

        $products = [
            ['salal-rice-creamy-face-wash', 'Salal Rice Creamy Face Wash', 950, 'products/prod1.png',
                'Rice-enriched creamy face wash for bright, clean, oil-free skin — for men & women.',
                'Salal Rice Creamy Face Wash gently cleanses deep into the pores while the goodness of rice extract brightens and softens your skin. It controls excess oil, helps clear acne and blackheads, and refreshes tired skin — leaving a smooth, healthy glow. Suitable for both men and women, for daily use.'],
            ['salal-beauty-whitening-cream', 'Salal Beauty & Whitening Cream (30g)', 1050, 'products/prod2.png',
                'Whitening & beauty cream for an even tone and natural glow — 30g.',
                'Salal Beauty & Whitening Cream evens out your skin tone and fades dark spots, pigmentation and blemishes for a brighter, fairer complexion. Its lightweight, fast-absorbing formula deeply hydrates and nourishes the skin, revealing a smooth, radiant and youthful glow. Net weight: 30g.'],
            ['salal-double-action-serum', 'Salal Double Action Serum — Vitamin C (30ml)', 1250, 'products/prod3.png',
                'Vitamin C double-action serum for radiance & anti-ageing — 30ml.',
                'Salal Double Action Serum is powered by Vitamin C to brighten dull skin and defend against the signs of ageing. It reduces dark spots and fine lines, boosts radiance and improves skin texture, giving you a firmer, glowing and even-toned complexion. Lightweight and fast-absorbing. Volume: 30ml.'],
        ];
        foreach ($products as $i => [$slug, $name, $price, $img, $summary, $desc]) {
            Product::updateOrCreate(
                ['slug' => $slug],
                ['branch_id' => $branch->id, 'category_id' => $category->id, 'unit_id' => $unitId,
                 'name' => $name, 'price' => $price, 'sale_price' => $price, 'resale_price' => $price,
                 'wholesale_price' => $price, 'cost_price' => round($price * 0.6),
                 'summary' => $summary, 'description' => $desc,
                 'image' => $img, 'stock_quantity' => 50, 'track_inventory' => 0,
                 'is_active' => 1, 'show_on_website' => 1, 'is_featured' => 1, 'sort_order' => $i + 1]
            );
        }

        Banner::where('position', 'hero')->orWhere('position', 'mid')->delete();
        foreach ([['banners/banner1.jpeg', 1], ['banners/banner2.jpeg', 2]] as [$img, $so]) {
            Banner::create(['title' => '', 'subtitle' => '', 'cta_text' => '', 'cta_url' => '',
                'image' => $img, 'position' => 'hero', 'is_active' => 1, 'sort_order' => $so]);
        }
    }
}

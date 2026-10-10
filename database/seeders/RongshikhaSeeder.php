<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Services\SiteSettings;
use Illuminate\Database\Seeder;

/**
 * Starter data for the Rongshikha painting shop (php artisan db:seed --class=RongshikhaSeeder).
 *
 * The paintings, sizes and prices come from the two pricing sheets the owner sent. It creates NO fake
 * customers, orders, reviews or questions. Safe to run twice.
 *
 * NOTE: the product photos are placeholders (cropped from the reference screenshots the owner sent).
 * Replace each one with a photo of the real painting in Admin > Products.
 */
class RongshikhaSeeder extends Seeder
{
    /** [slug, name] */
    public const CATEGORIES = [
        ['calligraphy-with-texture-painting', 'Calligraphy with texture painting'],
        ['colour-painting', 'Colour painting'],
        ['colour-painting-round-shape', 'Colour painting round shape'],
        ['gypsum-painting', 'Gypsum painting'],
        ['gypsum-painting-round-shape', 'Gypsum painting round shape'],
        ['gypsum-products', 'Gypsum products'],
        ['candle', 'Candle'],
        ['exclusive-picks', 'Exclusive Picks'],
    ];

    /** The shop is one artist, so there is one "brand": it shows as the artist's name on every card. */
    public const BRAND = ['rongshikha', 'Rongshikha'];

    /** Sizes in inches. */
    private const LANDSCAPE_SIZES = [['10×14 in', 500], ['12×14 in', 550], ['12×16 in', 600]];

    /**
     * [slug, name, bengali name, category slug, sizes [[label, price], ...], featured, image|null, extra spec line|null]
     * Price of the product is its cheapest size; the other sizes add the difference.
     * A painting with no price on the sheet (Golden Leaves) is created hidden with price 0: set the price, then show it.
     *
     * @var list<array<int, mixed>>
     */
    public const PRODUCTS = [
        // Landscapes (square shape sheet): 10×14 ৳500, 12×14 ৳550, 12×16 ৳600
        ['dream-waterfall', 'Dream Waterfall', 'স্বপ্ন ঝরনা', 'colour-painting', self::LANDSCAPE_SIZES, true, 'dream-waterfall', null],
        ['pink-blossom-path', 'Pink Blossom Path', 'বসন্তের গোলাপি পথ', 'colour-painting', self::LANDSCAPE_SIZES, false, 'pink-blossom-path', null],
        ['serenity-of-the-waterfall', 'Serenity of the Waterfall', 'পাহাড়ি ঝরনার সুর', 'colour-painting', self::LANDSCAPE_SIZES, true, 'serenity-of-the-waterfall', null],
        ['blue-coastal-escape', 'Blue Coastal Escape', 'নীল সমুদ্রের উপকূল', 'colour-painting', self::LANDSCAPE_SIZES, true, 'blue-coastal-escape', null],
        ['rainy-city-reflections', 'Rainy City Reflections', 'বৃষ্টিভেজা শহর', 'colour-painting', self::LANDSCAPE_SIZES, false, 'rainy-city-reflections', null],
        ['hidden-tropical-paradise', 'Hidden Tropical Paradise', 'গুহার ওপারে নীল সৈকত', 'colour-painting', self::LANDSCAPE_SIZES, false, 'hidden-tropical-paradise', null],
        ['golden-wetland-bird', 'Golden Wetland Bird', 'সোনালি জলাভূমির পাখি', 'colour-painting', self::LANDSCAPE_SIZES, false, 'golden-wetland-bird', null],
        ['pathway-to-the-golden-sunrise', 'Pathway to the Golden Sunrise', 'পাহাড়ের বুক চিরে আলোর পথ', 'colour-painting', self::LANDSCAPE_SIZES, false, 'pathway-to-the-golden-sunrise', null],
        ['golden-sunset-memories', 'Golden Sunset Memories', 'সোনালি গোধূলির স্মৃতি', 'colour-painting', self::LANDSCAPE_SIZES, true, 'golden-sunset-memories', null],
        ['tropical-blue-paradise', 'Tropical Blue Paradise', 'নীল সমুদ্রের স্বপ্ন', 'colour-painting', self::LANDSCAPE_SIZES, false, 'tropical-blue-paradise', null],
        ['golden-sunset-reflections', 'Golden Sunset Reflections', 'সোনালি সূর্যাস্তের প্রতিচ্ছবি', 'colour-painting', self::LANDSCAPE_SIZES, false, 'golden-sunset-reflections', null],
        ['golden-forest-blue-mountains', 'Golden Forest & Blue Mountains', 'নীল পাহাড়ের সোনালি বন', 'colour-painting', self::LANDSCAPE_SIZES, false, 'golden-forest-blue-mountains', null],

        // Textured art (square shape sheet)
        ['golden-leaves', 'Golden Leaves', 'সোনালি পাতা', 'gypsum-painting', [['Size on request', 0]], false, 'golden-leaves', null],
        ['natures-touch', "Nature's Touch", 'প্রকৃতির ছোঁয়া', 'gypsum-painting', [['16×20 in', 2500], ['20×24 in', 3000]], true, 'natures-touch', null],
        ['floral-charm', 'Floral Charm', 'ফুলের মায়া', 'gypsum-painting', [['20×24 in', 3000]], true, 'floral-charm', null],
        ['golden-foliage', 'Golden Foliage', 'গোল্ডেন ফোলিয়াজ', 'gypsum-painting', [['24×24 in', 3500]], true, 'golden-foliage', null],
        ['floral-grace', 'Floral Grace', 'ফুলের কোমলতা', 'gypsum-painting', [['20×24 in', 3000]], false, 'floral-grace', null],
        ['magnolia-bloom', 'Magnolia Bloom', 'ম্যাগনোলিয়া ব্লুম', 'gypsum-painting', [['20×24 in', 3000]], false, 'magnolia-bloom', null],
        ['emerald-bloom', 'Emerald Bloom', 'এমেরাল্ড ব্লুম', 'gypsum-painting', [['20×24 in', 3000]], false, 'emerald-bloom', null],
        ['golden-petal', 'Golden Petal', 'গোল্ডেন পেটাল', 'gypsum-painting', [['16×20 in', 2000]], true, 'golden-petal', null],
        ['golden-bloom-serenity', 'Golden Bloom Serenity', null, 'gypsum-painting', [['20×24 in', 3000]], false, 'golden-bloom-serenity', null],
        ['texture-art-with-subhanallah', 'Texture Art with Subhanallah', null, 'calligraphy-with-texture-painting', [['16×20 in', 2000]], false, null, null],

        // Combos: three canvases sold together
        ['botanical-trio', 'Botanical Trio', 'বোটানিক্যাল ট্রিও', 'gypsum-painting', [['Set of 3 · 16×20 in each', 5000]], true, 'botanical-trio', 'Combo of 3 canvases'],
        ['golden-branches', 'Golden Branches', 'গোল্ডেন ব্রাঞ্চ', 'gypsum-painting', [['Set of 3 · 16×20 in each', 5000]], true, 'golden-branches', 'Combo of 3 canvases'],

        // Round shape sheet
        ['sunset-in-round-shape', 'Sunset in Round Shape', null, 'colour-painting-round-shape', [['10 in', 500], ['12 in', 600], ['14 in', 750]], true, 'sunset-in-round-shape', 'Round canvas'],
        ['calligraphy-with-texture-painting', 'Calligraphy with Texture Painting', null, 'calligraphy-with-texture-painting', [['12×12 in', 1150], ['14×14 in', 1350]], false, null, 'Round canvas'],
    ];

    /** Stock per size. These are made by hand, so a healthy number keeps every size orderable: change it in Admin > Inventory. */
    private const STOCK = 10;

    /** The first four categories this seeder made, before the owner's 6-category list: emptied, then removed. */
    private const FIRST_CATEGORIES = ['landscape-paintings', 'textured-art', 'round-paintings', 'combo-sets'];

    public function run(): void
    {
        $this->settings();

        [$brandSlug, $brandName] = self::BRAND;
        $brand = Brand::updateOrCreate(['slug' => $brandSlug], ['name' => $brandName]);

        // The sheet's last row ("Gypsum products, Candle") is now two categories: keep the first as it is, add Candle.
        Category::where('slug', 'gypsum-products-candle')->whereDoesntHave('products')->delete();

        foreach (self::CATEGORIES as $position => [$slug, $name]) {
            // The picture is only set when the category is first made, and the order only while it is still the
            // automatic one (1000+), so changes made in Admin > Categories survive a re-run.
            $category = Category::firstOrNew(['slug' => $slug]);
            $category->name = $name;
            if (! $category->exists) {
                $category->image = "/images/rongshikha/categories/{$slug}.jpg";
            }
            // 30 / 40 are the positions of the first version of this list (Gypsum before Colour round shape).
            $oldPosition = ['gypsum-painting' => 30, 'colour-painting-round-shape' => 40][$slug] ?? null;
            if (! $category->exists || (int) $category->sort_order >= 1000 || (int) $category->sort_order === $oldPosition) {
                $category->sort_order = ($position + 1) * 10;
            }
            $category->save();
        }

        $categories = Category::pluck('id', 'slug');

        foreach (self::PRODUCTS as [$slug, $name, $bengali, $category, $sizes, $featured, $image, $extraSpec]) {
            $base = (int) min(array_column($sizes, 1));
            $priced = $base > 0;
            $sizeList = implode(', ', array_column($sizes, 0));

            $specs = [['label' => 'Artist', 'value' => $brandName], ['label' => 'Made', 'value' => 'Handmade']];
            if ($extraSpec) {
                $specs[] = ['label' => 'Format', 'value' => $extraSpec];
            }
            $specs[] = ['label' => count($sizes) > 1 ? 'Sizes' : 'Size', 'value' => $sizeList];

            $product = Product::updateOrCreate(['slug' => $slug], [
                'category_id' => $categories[$category],
                'brand_id' => $brand->id,
                'name' => $name,
                'description' => $this->describe($category, $name, $sizes),
                'specifications' => $specs,
                'price' => $base,
                'discount_price' => null,
                'image' => $image ? "/images/rongshikha/products/{$image}.jpg" : '/images/rongshikha/coming-soon.jpg',
                'stock' => self::STOCK,
                'is_featured' => $featured,
                // No price on the sheet yet: stays hidden until the owner sets one.
                'is_active' => $priced,
            ]);

            if ($product->variants()->count() === 0) {
                foreach ($sizes as $i => [$label, $price]) {
                    $product->variants()->create([
                        'size' => $label,
                        'extra_price' => $priced ? $price - $base : 0,
                        'stock' => self::STOCK,
                        'sort_order' => $i,
                    ]);
                }
                $product->syncStockFromVariants();
            }
        }

        // Home page banners: any old ones go, the three Rongshikha banners (3:1, 1792x592) take their place.
        // Later changes (add, replace, reorder, hide) are made in Admin > Sliders.
        Slider::query()
            ->where('image', 'like', '/images/sliders/%')
            ->orWhere('image', 'like', '/images/rongshikha/banners/banner-landscape%')
            ->orWhere('image', 'like', '/images/rongshikha/banners/banner-textured%')
            ->delete();

        // The paintings now sit in the owner's 6 categories; drop the first four once nothing is left in them.
        foreach (Category::whereIn('slug', self::FIRST_CATEGORIES)->get() as $old) {
            if (! $old->products()->exists()) {
                $old->delete();
            }
        }

        foreach ([
            ['Rongshikha - Where art meets soul & story', '/images/rongshikha/banners/banner-1.jpg', '/products', 1],
            ['Textured gold wall art - Shop now', '/images/rongshikha/banners/banner-2.jpg', '/products?category=gypsum-painting', 2],
            ['Gold leaf branches - Shop now', '/images/rongshikha/banners/banner-3.jpg', '/products?category=gypsum-painting', 3],
        ] as [$title, $image, $link, $order]) {
            Slider::updateOrCreate(
                ['image' => $image],
                ['title' => $title, 'subtitle' => null, 'link' => $link, 'sort_order' => $order, 'is_active' => true],
            );
        }
    }

    /** One honest line of description per painting (the owner can write her own in Admin > Products). */
    protected function describe(string $category, string $name, array $sizes): string
    {
        $count = count($sizes);
        $sizeText = $count > 1 ? 'Available in '.implode(', ', array_column($sizes, 0)).'.' : 'Size: '.$sizes[0][0].'.';

        return match ($category) {
            'colour-painting' => "{$name}: a hand-painted colour painting by Rongshikha. {$sizeText}",
            'gypsum-painting' => str_contains($sizes[0][0], 'Set of 3')
                ? "{$name}: a set of three handmade gypsum canvases that hang together. {$sizeText}"
                : "{$name}: handmade gypsum wall art by Rongshikha. {$sizeText}",
            'calligraphy-with-texture-painting' => "{$name}: handmade calligraphy with texture by Rongshikha. {$sizeText}",
            default => "{$name}: a handmade round painting by Rongshikha. {$sizeText}",
        };
    }

    /** Shop name, tagline and the contact details the owner gave us. Saved here on purpose (this is a new shop, not a copy of the old one). */
    protected function settings(): void
    {
        SiteSettings::save([
            'site_name' => 'Rongshikha',
            'tagline' => 'Handmade paintings and textured wall art by Rongshikha. Homemade elegance for your walls.',
            'meta_description' => 'Rongshikha: handmade landscape paintings, textured gold art and round canvases. Order online, pay by bKash, delivery across Bangladesh.',
            'phone' => '+880 1907-852281',
            'whatsapp' => '8801907852281',
            'email' => 'homemadeelegance4050@gmail.com',
            'bkash_number' => '01907852281',
            'free_shipping_over' => '0',
            'premium_from' => '2000',
        ]);
    }
}

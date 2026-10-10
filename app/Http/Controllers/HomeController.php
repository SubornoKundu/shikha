<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Services\StorefrontCache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /** Only what a product card shows is loaded for the brand and category (keeps the page data small). */
    private const CARD_RELATIONS = ['brand:id,name,slug', 'category:id,name,slug'];

    /** Long texts a product card never shows: left out of the page data. */
    private const CARD_HIDDEN = ['description', 'specifications', 'shipping_info', 'created_at', 'updated_at'];

    /**
     * The storefront home page: hero slider, a row for each collection,
     * statement pieces and best sellers.
     *
     * Everything on it is the same for every visitor, so it is cached for a
     * short time (see StorefrontCache) instead of being rebuilt on every hit.
     */
    public function index(): Response
    {
        $data = StorefrontCache::remember('home', fn () => [
            'sliders' => Slider::active()->orderBy('sort_order')->get(),
            // Order, visibility and headings of the home sections, the FAQ and the Why-us points (Admin > Page content).
            'homeSections' => \App\Services\SiteContent::sections(),
            'faqItems' => \App\Services\SiteContent::faq(),
            // One row of paintings for each collection (the owner's six categories).
            'categorySections' => $this->categorySections(),
            // Statement pieces: paintings at or above shop.premium_from, dearest first.
            'premiumFrom' => (int) config('shop.premium_from'),
            'premiumProducts' => $this->priceTier('>='),
            'categories' => Category::inShopOrder(Category::withCount('products')->whereNull('parent_id')->get()),
            // latestProducts is not shown on the home page; featuredProducts is the Featured row (8).
            'latestProducts' => $this->lean(Product::with(self::CARD_RELATIONS)
                ->active()
                ->latest()
                ->orderByDesc('id')
                ->take(2)
                ->get()),
            'popularProducts' => $this->popularProducts(),
            'featuredProducts' => $this->lean(Product::with(self::CARD_RELATIONS)
                ->active()
                ->featured()
                ->latest()
                ->orderByDesc('id')
                ->take(8)
                ->get()),
        ]);

        // The first banner picture is announced in the page head (see app.blade.php) so the browser starts
        // downloading it straight away instead of waiting for the page scripts to run.
        $first = collect($data['sliders'] ?? [])->first();
        $image = is_object($first) ? (string) ($first->image ?? '') : '';
        if ($image !== '' && preg_match('#^(/[^/\\]|https?://)#', $image)) {
            request()->attributes->set('preload_image', $image);

            if (preg_match('#^/images/[^?\#]+\.(jpe?g|png|webp)$#i', $image)) {
                // Same list as resources/js/thumb.js builds for the banner picture (WebP copies), or the browser would fetch twice.
                $ext = preg_match('/\.webp$/i', $image) ? '' : '.webp';
                request()->attributes->set('preload_srcset', "/thumbs/800{$image}{$ext} 800w, /thumbs/1200{$image}{$ext} 1200w, {$image} 1800w");
            }
        }

        return Inertia::render('Home', $data);
    }

    /**
     * The painting collections with their newest paintings (empty ones are skipped).
     *
     * @return list<array{name: string, slug: string, products: \Illuminate\Support\Collection<int, Product>}>
     */
    protected function categorySections(): array
    {
        $sections = [];

        foreach (Category::inShopOrder(Category::whereNull('parent_id')->get()) as $category) {
            $products = Product::with(self::CARD_RELATIONS)
                ->active()
                ->whereIn('category_id', $category->withDescendantIds())
                ->latest()
                ->orderByDesc('id')
                ->take(5)
                ->get();

            $this->lean($products);

            if ($products->isNotEmpty()) {
                $sections[] = ['name' => $category->name, 'slug' => $category->slug, 'products' => $products];
            }
        }

        return $sections;
    }

    /**
     * Five paintings from one side of the premium price line ('>=' statement pieces, '<' small canvases).
     *
     * @return \Illuminate\Support\Collection<int, Product>
     */
    protected function priceTier(string $operator)
    {
        $line = (float) config('shop.premium_from');
        $price = 'COALESCE(discount_price, price)';

        return $this->lean(Product::with(self::CARD_RELATIONS)
            ->active()
            ->whereRaw("{$price} {$operator} CAST(? AS DECIMAL(10,2))", [$line])
            ->orderByRaw($operator === '>=' ? "{$price} desc" : 'id desc')
            ->orderByDesc('id')
            ->take(5)
            ->get());
    }

    /**
     * Leaves the long texts out of the products sent to the page (a card never shows them).
     *
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     * @return \Illuminate\Support\Collection<int, Product>
     */
    protected function lean($products)
    {
        $products->each(fn (Product $product) => $product->makeHidden(self::CARD_HIDDEN));

        return $products;
    }

    /**
     * The 8 best sellers that are still on sale, best first.
     *
     * @return \Illuminate\Support\Collection<int, Product>
     */
    protected function popularProducts()
    {
        // A little more than 8 ids, in case some of the top sellers have been hidden since.
        $ids = array_slice(StorefrontCache::popularProductIds(), 0, 60);

        if ($ids === []) {
            return collect();
        }

        $rank = array_flip($ids);

        return $this->lean(Product::with(self::CARD_RELATIONS)
            ->active()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Product $p) => $rank[$p->id])
            ->take(8)
            ->values());
    }
}

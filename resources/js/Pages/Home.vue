<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import LocationMap from '@/Components/LocationMap.vue';
import Faq from '@/Components/Faq.vue';
import WhyUs from '@/Components/WhyUs.vue';
import { formatPrice } from '@/currency';
import { routes } from '@/routes';
import { srcset } from '@/thumb';

const props = defineProps({
    sliders: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    latestProducts: { type: Array, default: () => [] },
    popularProducts: { type: Array, default: () => [] },
    featuredProducts: { type: Array, default: () => [] },
    categorySections: { type: Array, default: () => [] },
    premiumFrom: { type: Number, default: 0 },
    premiumProducts: { type: Array, default: () => [] },
    homeSections: { type: Array, default: () => [] },
    faqItems: { type: Array, default: () => [] },
});

// Order, visibility and headings of the home sections come from Admin > Page content.
const FALLBACK_HEADING = { categories: 'Shop by category', featured: 'Featured paintings', faq: 'Questions & answers', why_us: 'Why Rongshikha' };
const sectionOf = (key) => props.homeSections.find((s) => s.key === key);
const isOn = (key) => (props.homeSections.length ? !!sectionOf(key)?.visible : true);
const place = (key) => {
    const i = props.homeSections.findIndex((s) => s.key === key);
    return i === -1 ? 99 : i;
};
const heading = (key) => sectionOf(key)?.title || FALLBACK_HEADING[key] || '';

// Categories sit in one row; when there are more than fit, arrow buttons scroll to the rest.
const catRow = ref(null);
const canPrev = ref(false);
const canNext = ref(false);
function updateCats() {
    const el = catRow.value;
    if (!el) return;
    canPrev.value = el.scrollLeft > 4;
    canNext.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 4;
}
function scrollCats(direction) {
    const el = catRow.value;
    if (el) el.scrollBy({ left: direction * el.clientWidth * 0.85, behavior: 'smooth' });
}
onMounted(() => {
    updateCats();
    window.addEventListener('resize', updateCats);
});
onBeforeUnmount(() => window.removeEventListener('resize', updateCats));

const SLIDE_MS = 6500;
const activeSlide = ref(0);

// Only the first banner picture loads at once; the others wait until the page has finished loading,
// so they never compete with it for the connection (this is what keeps phones fast).
const restReady = ref(false);
onMounted(() => {
    const go = () => window.setTimeout(() => { restReady.value = true; }, 300);
    if (document.readyState === 'complete') go();
    else window.addEventListener('load', go, { once: true });
});

// On computers the banner box takes the shape of the picture showing, so the whole picture is always
// visible (wide 3:1 banners, or an older 16:9 one). Phones keep the fixed 16:9 box.
const ratios = ref({});
const heroRatio = computed(() => {
    const r = ratios.value[props.sliders[activeSlide.value]?.id];
    return r ? Math.min(3.2, Math.max(1.6, r)) : 3;
});

function noteRatio(slide, event) {
    const img = event.target;
    if (img.naturalWidth && img.naturalHeight) {
        ratios.value = { ...ratios.value, [slide.id]: img.naturalWidth / img.naturalHeight };
    }
}
let timer = null;

function goTo(index) {
    activeSlide.value = (index + props.sliders.length) % props.sliders.length;
}

function next() {
    goTo(activeSlide.value + 1);
}

function prev() {
    goTo(activeSlide.value - 1);
}

function resetTimer() {
    window.clearInterval(timer);
    if (props.sliders.length > 1) {
        timer = window.setInterval(next, SLIDE_MS);
    }
}

onMounted(resetTimer);

onBeforeUnmount(() => window.clearInterval(timer));

function onNavClick(fn) {
    fn();
    resetTimer();
}

const shopName = computed(() => usePage().props.site?.name || 'Rongshikha');
const bkashNumber = computed(() => usePage().props.shop?.bkash || '');

const hasSliders = computed(() => props.sliders.length > 0);

// Swipe left/right on touch screens to change slides.
let touchX = null;
let touchY = null;

function onTouchStart(event) {
    const touch = event.touches[0];
    touchX = touch.clientX;
    touchY = touch.clientY;
}

function onTouchEnd(event) {
    if (touchX === null) return;
    const touch = event.changedTouches[0];
    const dx = touch.clientX - touchX;
    const dy = touch.clientY - touchY;
    touchX = null;
    if (props.sliders.length > 1 && Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.5) {
        onNavClick(dx < 0 ? next : prev);
    }
}
</script>

<template>
    <Head title="Handmade paintings" />

    <MainLayout>
        <!-- Banners managed in Admin > Sliders. They replace the static hero below; with none active the hero shows. Banners are wide (3:1). On computers the banner sits at the same width as the content below it and the whole picture shows (the box takes the picture's own shape, so nothing is cut). On phones it runs edge to edge in a 3:1 box, the banner's own shape, so the whole picture shows (the banners carry the shop name on the right, which a centre crop would cut). -->
        <section v-if="hasSliders">
            <h1 class="sr-only">Handmade paintings by Rongshikha</h1>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- The one orchestrated moment of the page: the banner opens from the middle on load, then a gold line counts down to the next picture. -->
            <div class="relative aspect-[3/1] w-full overflow-hidden bg-green-950 md:aspect-(--hero-ratio)" :style="{ '--hero-ratio': heroRatio }" @touchstart.passive="onTouchStart" @touchend.passive="onTouchEnd">
                <template v-for="(slide, index) in sliders" :key="slide.id">
                    <a
                        v-if="/^https?:\/\//i.test(slide.link || '')"
                        :href="slide.link"
                        class="absolute inset-0 transition-opacity duration-[900ms]"
                        :class="index === activeSlide ? 'opacity-100' : 'pointer-events-none opacity-0'"
                    >
                        <img v-if="index === 0 || restReady" :src="slide.image" :srcset="srcset(slide.image, [800, 1200], 1800)" sizes="(min-width: 1280px) 1200px, 100vw" :alt="slide.title" :fetchpriority="index === 0 ? 'high' : 'low'" :decoding="index === 0 ? 'sync' : 'async'" class="relative h-full w-full object-cover" @load="noteRatio(slide, $event)">
                    </a>
                    <Link
                        v-else
                        :href="slide.link || routes.productsIndex()"
                        class="absolute inset-0 transition-opacity duration-[900ms]"
                        :class="index === activeSlide ? 'opacity-100' : 'pointer-events-none opacity-0'"
                    >
                        <img v-if="index === 0 || restReady" :src="slide.image" :srcset="srcset(slide.image, [800, 1200], 1800)" sizes="(min-width: 1280px) 1200px, 100vw" :alt="slide.title" :fetchpriority="index === 0 ? 'high' : 'low'" :decoding="index === 0 ? 'sync' : 'async'" class="relative h-full w-full object-cover" @load="noteRatio(slide, $event)">
                    </Link>
                </template>

                <template v-if="sliders.length > 1">
                    <button type="button" class="absolute top-1/2 left-3 hidden h-10 w-10 -translate-y-1/2 items-center justify-center border border-gold-500/50 bg-black/55 text-gold-300 backdrop-blur-sm transition hover:bg-green-950 hover:text-gold-300 sm:flex" aria-label="Previous slide" @click="onNavClick(prev)"><ChevronLeft class="h-5 w-5" /></button>
                    <button type="button" class="absolute top-1/2 right-3 hidden h-10 w-10 -translate-y-1/2 items-center justify-center border border-gold-500/50 bg-black/55 text-gold-300 backdrop-blur-sm transition hover:bg-green-950 hover:text-gold-300 sm:flex" aria-label="Next slide" @click="onNavClick(next)"><ChevronRight class="h-5 w-5" /></button>
                    <div class="absolute inset-x-0 bottom-0 flex justify-end gap-1 px-3 pb-0 sm:px-6 sm:pb-3">
                        <button v-for="(slide, index) in sliders" :key="slide.id" type="button" class="group flex h-6 w-8 items-center sm:w-12" :aria-label="`Go to slide ${index + 1}`" @click="onNavClick(() => goTo(index))">
                            <span class="relative block h-[3px] w-full overflow-hidden bg-white/35">
                                <!-- Restarts with each slide, so it always shows the time left on the current one. -->
                                <span v-if="index === activeSlide" :key="activeSlide" class="hero-count absolute inset-y-0 left-0 block w-full origin-left bg-gold-400" :style="{ animationDuration: SLIDE_MS + 'ms' }" />
                            </span>
                        </button>
                    </div>
                </template>
            </div>
            </div>
        </section>

        <!-- Hero (shown when no banner is active): the pitch on the left, three paintings on the right. -->
        <section v-if="!hasSliders" class="bg-green-950 text-white">
            <div class="mx-auto grid max-w-7xl items-center gap-8 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1.1fr_1fr] lg:gap-10 lg:px-8 lg:py-16">
                <div class="order-2 lg:order-1">
                    <p class="text-xs font-semibold tracking-[0.3em] text-gold-400">HANDMADE PAINTINGS</p>
                    <h1 class="font-display mt-4 text-5xl leading-[1.04] font-medium tracking-tight text-balance sm:text-6xl lg:text-7xl">Colour for every wall</h1>
                    <p class="mt-5 max-w-md text-base leading-relaxed text-green-100/80 sm:text-lg">Landscapes, textured gold art and round canvases, painted by hand by {{ shopName }}. Pay a small advance by bKash or Nagad and we deliver across Bangladesh.</p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <Link :href="routes.productsIndex()" class="inline-flex min-h-12 items-center justify-center bg-gold-500 px-7 py-3 text-sm font-semibold tracking-wide text-green-950 hover:bg-gold-400">Shop paintings</Link>
                        <Link :href="routes.productsIndex({ sort: 'popular' })" class="inline-flex min-h-12 items-center justify-center border border-green-100/30 px-7 py-3 text-sm font-semibold tracking-wide text-white hover:border-gold-400 hover:text-gold-300">Best sellers</Link>
                    </div>
                </div>

                <div class="order-1 mx-auto grid w-full max-w-md grid-cols-3 items-end gap-3 lg:order-2">
                    <img src="/images/rongshikha/products/floral-charm.jpg" alt="Floral Charm textured painting" class="aspect-[3/4] w-full border-4 border-[#f6efe0] object-cover shadow-2xl" width="320" height="427">
                    <img src="/images/rongshikha/products/blue-coastal-escape.jpg" alt="Blue Coastal Escape painting" class="aspect-square w-full -translate-y-6 border-4 border-[#f6efe0] object-cover shadow-2xl" width="320" height="320">
                    <img src="/images/rongshikha/products/golden-sunset-memories.jpg" alt="Golden Sunset Memories painting" class="aspect-[3/4] w-full border-4 border-[#f6efe0] object-cover shadow-2xl" width="320" height="427">
                </div>
            </div>
        </section>

        <!-- The sections below follow the order, visibility and headings set in Admin > Page content. -->
        <div class="flex flex-col pb-6 sm:pb-8">
            <div v-if="isOn('categories') && categories.length" :style="{ order: place('categories') }" class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8">
                <!-- Categories, each with its picture -->
            <section aria-labelledby="cat-heading">
                <h2 id="cat-heading" class="mb-4 font-display text-3xl font-medium text-green-900 sm:text-4xl">{{ heading('categories') }}</h2>
                <div class="relative">
                    <ul ref="catRow" class="flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pb-1 [scrollbar-width:none] lg:gap-4 [&::-webkit-scrollbar]:hidden" @scroll.passive="updateCats">
                        <li v-for="category in categories" :key="category.id" class="flex w-[42%] shrink-0 snap-start sm:w-[calc((100%-2.25rem)/4)] lg:w-[calc((100%-5rem)/6)]">
                            <Link :href="routes.productsIndex({ category: category.slug })" class="group flex h-full w-full flex-col overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                                <div class="aspect-square w-full shrink-0 overflow-hidden bg-gray-100">
                                    <img v-if="category.image" :src="category.image" :srcset="srcset(category.image, [360], 480)" sizes="(min-width: 1024px) 190px, 45vw" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                </div>
                                <p class="flex flex-1 items-center justify-center px-2 py-3 text-center font-display text-base leading-snug font-medium text-green-900 sm:text-lg">{{ category.name }}</p>
                            </Link>
                        </li>
                    </ul>
                    <button v-if="canPrev" type="button" class="absolute top-[calc(50%-1.5rem)] left-1 z-10 flex h-10 w-10 items-center justify-center bg-green-950 text-gold-300 shadow-lg ring-1 ring-gold-500/60 hover:bg-green-900" aria-label="Previous categories" @click="scrollCats(-1)"><ChevronLeft class="h-5 w-5" /></button>
                    <button v-if="canNext" type="button" class="absolute top-[calc(50%-1.5rem)] right-1 z-10 flex h-10 w-10 items-center justify-center bg-green-950 text-gold-300 shadow-lg ring-1 ring-gold-500/60 hover:bg-green-900" aria-label="Next categories" @click="scrollCats(1)"><ChevronRight class="h-5 w-5" /></button>
                </div>
            </section>
            </div>

            <div v-if="isOn('featured') && featuredProducts.length" :style="{ order: place('featured') }" class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8">
                <!-- Featured -->
            <section>
                <div class="mb-4 flex items-end justify-between gap-4">
                    <h2 class="font-display text-3xl font-medium text-green-900 sm:text-4xl">{{ heading('featured') }}</h2>
                    <Link :href="routes.productsIndex()" class="inline-block shrink-0 py-1.5 text-sm font-semibold text-green-700 underline-offset-4 hover:underline">View all</Link>
                </div>
                <div v-reveal class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:gap-4">
                    <ProductCard v-for="product in featuredProducts" :key="product.id" :product="product" />
                </div>
            </section>
            </div>

            <div v-if="isOn('collections') && categorySections.length" :style="{ order: place('collections') }" class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8">
                <!-- One row for each collection. -->
            <section v-for="section in categorySections" :key="section.slug" class="mb-8 last:mb-0 sm:mb-10">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <h2 class="font-display text-3xl font-medium text-green-900 sm:text-4xl">{{ section.name }}</h2>
                    <Link :href="routes.productsIndex({ category: section.slug })" class="inline-block shrink-0 py-1.5 text-sm font-semibold text-green-700 underline-offset-4 hover:underline">View all</Link>
                </div>
                <div v-reveal class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-5 max-xl:[&>*:nth-child(5)]:hidden lg:gap-4">
                    <ProductCard v-for="product in section.products" :key="product.id" :product="product" />
                </div>
            </section>
            </div>

            <div v-if="isOn('faq') && faqItems.length" :style="{ order: place('faq') }" class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8">
                <Faq :items="faqItems" :title="heading('faq')" />
            </div>

            <div v-if="isOn('why_us')" :style="{ order: place('why_us') }" class="mt-6 w-full sm:mt-8">
                <WhyUs variant="band" :title="heading('why_us')" />
            </div>

            <div v-if="isOn('map')" :style="{ order: place('map') }" class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8">
                <LocationMap />
            </div>
        </div>
    </MainLayout>
</template>

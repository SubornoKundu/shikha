<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Frame, Gift, HandHeart, PackageOpen, Paintbrush, Phone, ShieldCheck, Sparkles, Star, Truck } from '@lucide/vue';

// The reassurance points shown at the end of every painting's description and on the home page.
// The wording, icons and order are edited in Admin > Page content.
const props = defineProps({
    // 'plain' sits inside a white card (product page); 'band' is the full home-page section.
    variant: { type: String, default: 'plain' },
    title: { type: String, default: 'Why Rongshikha' },
});

const ICONS = { 'package-open': PackageOpen, frame: Frame, 'hand-heart': HandHeart, truck: Truck, 'shield-check': ShieldCheck, gift: Gift, star: Star, paintbrush: Paintbrush, sparkles: Sparkles, phone: Phone };

const page = usePage();
const items = computed(() => page.props.shop?.whyUs ?? []);
const whatsappUrl = computed(() => {
    const number = String(page.props.shop?.whatsapp ?? '').replace(/\D/g, '');
    return number ? `https://wa.me/${number}` : null;
});
</script>

<template>
    <section v-if="items.length" :class="variant === 'band' ? 'border-y border-gray-200 bg-[#f6efe0]' : 'mt-6 border-t border-gray-200 pt-5'" aria-label="Why choose us">
        <div :class="variant === 'band' ? 'mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8' : ''">
            <h2 v-if="variant === 'band'" class="mb-6 font-display text-3xl font-medium text-green-900 sm:text-4xl">{{ props.title }}</h2>
            <ul class="grid grid-cols-1 gap-x-8 gap-y-6 min-[480px]:grid-cols-2" :class="variant === 'band' ? 'lg:grid-cols-4' : ''">
                <li v-for="(item, index) in items" :key="index" class="flex gap-3">
                    <component :is="ICONS[item.icon] || Star" class="mt-0.5 h-6 w-6 shrink-0 text-gold-600" aria-hidden="true" />
                    <div class="min-w-0">
                        <p class="font-semibold break-words text-gray-900">{{ item.title }}</p>
                        <p class="mt-1 text-sm leading-relaxed break-words text-gray-600">{{ item.text }}</p>
                        <a v-if="item.whatsapp && whatsappUrl" :href="whatsappUrl" target="_blank" rel="noopener" class="mt-1.5 inline-block py-1 text-sm font-semibold text-green-700 underline-offset-4 hover:underline">Send on WhatsApp</a>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

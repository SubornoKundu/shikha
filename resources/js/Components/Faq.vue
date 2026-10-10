<script setup>
import { ChevronDown } from '@lucide/vue';

// Questions and answers shown on the home page. Edited in Admin > Page content.
defineProps({
    items: { type: Array, default: () => [] },
    title: { type: String, default: 'Questions & answers' },
});
</script>

<template>
    <section v-if="items.length" aria-labelledby="faq-heading">
        <h2 id="faq-heading" class="mb-4 font-display text-3xl font-medium text-green-900 sm:text-4xl">{{ title }}</h2>
        <div class="divide-y divide-gray-200 border-y border-gray-200 bg-white">
            <details v-for="(item, index) in items" :key="index" class="group px-4 sm:px-5">
                <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-4 py-3 font-semibold text-gray-900 [&::-webkit-details-marker]:hidden">
                    <span class="min-w-0 break-words">{{ item.q }}</span>
                    <ChevronDown class="h-5 w-5 shrink-0 text-gray-400 transition-transform group-open:rotate-180" aria-hidden="true" />
                </summary>
                <p class="pb-4 text-sm leading-relaxed break-words whitespace-pre-line text-gray-600 sm:text-base">{{ item.a }}</p>
            </details>
        </div>
    </section>
</template>

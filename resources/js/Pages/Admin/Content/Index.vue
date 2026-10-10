<script setup>
import { computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Plus, Trash2 } from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    sections: Array,
    sectionLabels: Object,
    whyUs: Array,
    icons: Array,
    faq: Array,
    pages: Array,
});

const form = useForm({
    sections: props.sections.map((s) => ({ ...s })),
    whyUs: props.whyUs.map((i) => ({ ...i })),
    faq: props.faq.map((i) => ({ ...i })),
    pages: Object.fromEntries(props.pages.map((p) => [p.slug, p.text])),
});

const input = 'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none';
const label = 'mb-1 block text-sm font-medium text-gray-700';
const card = 'rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6';
const iconBtn = 'flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30';

function move(list, index, delta) {
    const target = index + delta;
    if (target < 0 || target >= list.length) return;
    [list[index], list[target]] = [list[target], list[index]];
}

const hasHeading = (key) => key !== 'collections' && key !== 'map';

function addWhy() {
    form.whyUs.push({ icon: 'star', title: '', text: '', whatsapp: false });
}
function addFaq() {
    form.faq.push({ q: '', a: '' });
}
function remove(list, index) {
    list.splice(index, 1);
}

const errors = computed(() => Object.values(form.errors));

function resetPart(part, name) {
    if (!window.confirm(`Put "${name}" back to the standard wording? Your own text for it will be lost, and any unsaved changes on this page too.`)) return;
    router.post('/admin/content/reset', { part }, { preserveScroll: true, onSuccess: () => window.location.reload() });
}

function save() {
    form.post('/admin/content', { preserveScroll: true });
}
</script>

<template>
    <Head title="Page content" />

    <AdminLayout title="Page content">
        <form class="space-y-6" @submit.prevent="save">
            <p class="max-w-3xl text-sm text-gray-500">Everything here changes the shop straight away. The banner always stays at the top of the home page; the pictures in the banner are in Banners, the categories in Categories, the contact details and map in Settings.</p>

            <div v-if="errors.length" class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700" role="alert">
                <p class="font-semibold">Please fix this:</p>
                <ul class="mt-1 list-disc pl-5"><li v-for="e in errors.slice(0, 6)" :key="e">{{ e }}</li></ul>
            </div>

            <!-- Home page sections -->
            <section :class="card">
                <div class="flex items-center justify-between gap-3"><h2 class="font-bold text-gray-900">Home page sections</h2><button type="button" class="shrink-0 text-xs font-medium text-gray-500 underline hover:text-rose-600" @click="resetPart('sections', 'Home page sections')">Reset to standard</button></div>
                <p class="mt-1 mb-4 text-sm text-gray-500">Use the arrows to change the order, untick to hide a section. A heading left empty uses the standard one.</p>
                <ul class="space-y-2">
                    <li v-for="(s, i) in form.sections" :key="s.key" class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 p-3 sm:flex-nowrap">
                        <div class="flex shrink-0 gap-1">
                            <button type="button" :class="iconBtn" :disabled="i === 0" :aria-label="`Move ${sectionLabels[s.key]} up`" @click="move(form.sections, i, -1)"><ArrowUp class="h-4 w-4" /></button>
                            <button type="button" :class="iconBtn" :disabled="i === form.sections.length - 1" :aria-label="`Move ${sectionLabels[s.key]} down`" @click="move(form.sections, i, 1)"><ArrowDown class="h-4 w-4" /></button>
                        </div>
                        <label class="flex min-h-9 min-w-0 flex-1 items-center gap-2 text-sm font-medium text-gray-900">
                            <input v-model="s.visible" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-green-600">
                            <span class="min-w-0 break-words">{{ sectionLabels[s.key] }}</span>
                        </label>
                        <input v-if="hasHeading(s.key)" v-model="s.title" type="text" maxlength="80" :aria-label="`Heading for ${sectionLabels[s.key]}`" placeholder="Heading" :class="[input, 'sm:max-w-xs']">
                    </li>
                </ul>
            </section>

            <!-- Why us -->
            <section :class="card">
                <div class="flex items-center justify-between gap-3"><h2 class="font-bold text-gray-900">"Why us" points</h2><button type="button" class="shrink-0 text-xs font-medium text-gray-500 underline hover:text-rose-600" @click="resetPart('why_us', 'Why us')">Reset to standard</button></div>
                <p class="mt-1 mb-4 text-sm text-gray-500">Shown at the end of every painting's description and in the "Why us" home section.</p>
                <ul class="space-y-3">
                    <li v-for="(item, i) in form.whyUs" :key="i" class="rounded-lg border border-gray-200 p-3 sm:p-4">
                        <div class="grid gap-3 sm:grid-cols-[10rem_1fr]">
                            <div>
                                <label :class="label" :for="`why-icon-${i}`">Icon</label>
                                <select :id="`why-icon-${i}`" v-model="item.icon" :class="input">
                                    <option v-for="icon in icons" :key="icon" :value="icon">{{ icon }}</option>
                                </select>
                            </div>
                            <div>
                                <label :class="label" :for="`why-title-${i}`">Title</label>
                                <input :id="`why-title-${i}`" v-model="item.title" type="text" maxlength="100" :class="input">
                            </div>
                        </div>
                        <label :class="[label, 'mt-3']" :for="`why-text-${i}`">Text</label>
                        <textarea :id="`why-text-${i}`" v-model="item.text" rows="2" maxlength="400" :class="input" />
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                            <label class="flex min-h-9 items-center gap-2 text-sm text-gray-700"><input v-model="item.whatsapp" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-green-600"> Add a "Send on WhatsApp" link</label>
                            <div class="flex gap-1">
                                <button type="button" :class="iconBtn" :disabled="i === 0" aria-label="Move up" @click="move(form.whyUs, i, -1)"><ArrowUp class="h-4 w-4" /></button>
                                <button type="button" :class="iconBtn" :disabled="i === form.whyUs.length - 1" aria-label="Move down" @click="move(form.whyUs, i, 1)"><ArrowDown class="h-4 w-4" /></button>
                                <button type="button" :class="[iconBtn, 'text-rose-600']" aria-label="Remove this point" @click="remove(form.whyUs, i)"><Trash2 class="h-4 w-4" /></button>
                            </div>
                        </div>
                    </li>
                </ul>
                <button v-if="form.whyUs.length < 8" type="button" class="mt-3 inline-flex min-h-10 items-center gap-1.5 rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50" @click="addWhy"><Plus class="h-4 w-4" /> Add a point</button>
            </section>

            <!-- FAQ -->
            <section :class="card">
                <div class="flex items-center justify-between gap-3"><h2 class="font-bold text-gray-900">FAQ</h2><button type="button" class="shrink-0 text-xs font-medium text-gray-500 underline hover:text-rose-600" @click="resetPart('faq', 'FAQ')">Reset to standard</button></div>
                <p class="mt-1 mb-4 text-sm text-gray-500">In an answer you can type <code class="rounded bg-gray-100 px-1">{advance}</code> (advance percent), <code class="rounded bg-gray-100 px-1">{rest}</code> (paid on delivery) and <code class="rounded bg-gray-100 px-1">{fee}</code> (delivery charge). They are filled in from Settings.</p>
                <ul class="space-y-3">
                    <li v-for="(item, i) in form.faq" :key="i" class="rounded-lg border border-gray-200 p-3 sm:p-4">
                        <label :class="label" :for="`faq-q-${i}`">Question</label>
                        <input :id="`faq-q-${i}`" v-model="item.q" type="text" maxlength="200" :class="input">
                        <label :class="[label, 'mt-3']" :for="`faq-a-${i}`">Answer</label>
                        <textarea :id="`faq-a-${i}`" v-model="item.a" rows="3" maxlength="1000" :class="input" />
                        <div class="mt-3 flex justify-end gap-1">
                            <button type="button" :class="iconBtn" :disabled="i === 0" aria-label="Move up" @click="move(form.faq, i, -1)"><ArrowUp class="h-4 w-4" /></button>
                            <button type="button" :class="iconBtn" :disabled="i === form.faq.length - 1" aria-label="Move down" @click="move(form.faq, i, 1)"><ArrowDown class="h-4 w-4" /></button>
                            <button type="button" :class="[iconBtn, 'text-rose-600']" aria-label="Remove this question" @click="remove(form.faq, i)"><Trash2 class="h-4 w-4" /></button>
                        </div>
                    </li>
                </ul>
                <button v-if="form.faq.length < 20" type="button" class="mt-3 inline-flex min-h-10 items-center gap-1.5 rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50" @click="addFaq"><Plus class="h-4 w-4" /> Add a question</button>
            </section>

            <!-- Information pages -->
            <section :class="card">
                <h2 class="font-bold text-gray-900">Pages: About, Contact, Returns, Privacy, Terms</h2>
                <p class="mt-1 mb-4 text-sm text-gray-500">Each box starts with the wording the page shows now. Change it freely. A line starting with <code class="rounded bg-gray-100 px-1">## </code> begins a new heading, a line starting with <code class="rounded bg-gray-100 px-1">- </code> is a bullet point, and a blank line starts a new paragraph. The first lines before any heading are the introduction.</p>
                <div class="space-y-5">
                    <div v-for="page in pages" :key="page.slug">
                        <label :class="label" :for="`page-${page.slug}`">{{ page.title }} <span v-if="page.custom" class="text-xs font-normal text-green-700">(edited)</span>
                            <button v-if="page.custom" type="button" class="ml-2 text-xs font-medium text-gray-500 underline hover:text-rose-600" @click="resetPart('page:' + page.slug, page.title)">Reset to standard</button></label>
                        <textarea :id="`page-${page.slug}`" v-model="form.pages[page.slug]" rows="10" maxlength="10000" :class="[input, 'font-mono leading-relaxed']" />
                    </div>
                </div>
            </section>

            <div class="sticky bottom-0 -mx-3 flex items-center justify-end gap-3 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border">
                <button type="submit" :disabled="form.processing" class="min-h-11 rounded-lg bg-green-600 px-6 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-60">{{ form.processing ? 'Saving…' : 'Save changes' }}</button>
            </div>
        </form>
    </AdminLayout>
</template>

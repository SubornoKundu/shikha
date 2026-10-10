<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ settingsOpen: { type: Boolean, default: true } });

// Plain "how do I..." answers. Each step is one short sentence; `go` is the page where it is done.
const guides = [
    {
        title: 'Add a new painting',
        go: { href: '/admin/products', label: 'Open Products' },
        steps: [
            'Click Products in the left menu, then "Add product".',
            'Type the name and price, choose the category, and pick a clear photo. You can add more photos below.',
            'Write the description. The "Why us" points are added under it automatically.',
            'Set the stock (how many you have) and tick "Active". Press Save.',
        ],
    },
    {
        title: 'Change a price or mark a painting as sold out',
        go: { href: '/admin/products', label: 'Open Products' },
        steps: [
            'Open Products and click the pencil next to the painting.',
            'Change the price, or set the stock to 0 for sold out.',
            'Press Save. The shop updates straight away.',
        ],
    },
    {
        title: 'Add a category, or change the order of categories',
        go: { href: '/admin/categories', label: 'Open Categories' },
        steps: [
            'Click Categories, then "Add category". Type the name and choose a picture (best size 1200 x 900).',
            '"Display order": the smallest number shows first on the home page. Leave it empty to put the category last.',
            'To move one, click its pencil and change its number. Press Save.',
            'On the home page, more categories than fit in one row are reached with the arrow buttons.',
        ],
    },
    {
        title: 'Change the big banner pictures',
        go: { href: '/admin/sliders', label: 'Open Banners' },
        steps: [
            'Click Banners, then "Add". Choose a wide picture (best size 1800 x 600).',
            'Optional: write a title and a link, for example /products?category=colour-painting.',
            'Use "Display order" if you have more than one, and untick "Show on home page" to hide one.',
        ],
    },
    {
        title: 'Change the order of the home page, or hide a part',
        go: { href: '/admin/content', label: 'Open Page content' },
        steps: [
            'Open Page content. At the top is "Home page sections".',
            'Use the up and down arrows to move a section. Untick the box to hide it.',
            'You can also type your own heading for a section.',
            'Press "Save changes" at the bottom.',
        ],
    },
    {
        title: 'Edit the "Why us" points or the FAQ',
        go: { href: '/admin/content', label: 'Open Page content' },
        steps: [
            'Open Page content and scroll to "Why us" or "FAQ".',
            'Change the text, add a new one, or remove one with the bin button.',
            'Press "Save changes". Made a mess? "Reset to standard" brings back the original wording.',
        ],
    },
    {
        title: 'Change the About, Contact, Returns, Privacy or Terms text',
        go: { href: '/admin/content', label: 'Open Page content' },
        steps: [
            'Open Page content and scroll to "Pages". Each box already holds the current wording.',
            'Edit it. A line starting with ## is a heading, a line starting with - is a bullet point, a blank line starts a new paragraph.',
            'Press "Save changes". "Reset to standard" next to the box restores the original.',
        ],
    },
    {
        title: 'Handle a new order',
        go: { href: '/admin/orders', label: 'Open Orders' },
        steps: [
            'New orders show a red number beside Orders in the menu.',
            'Open the order. Check the bKash / Nagad transaction ID against your phone.',
            'In the payment box, mark the advance as paid. Then change the status: Processing, Shipped, Delivered.',
            'To cancel, choose Cancelled. The stock goes back automatically.',
        ],
    },
    {
        title: 'A customer forgot the password',
        go: { href: '/admin/password-requests', label: 'Open Password requests' },
        steps: [
            'The customer asks from the login page. You see it under Password requests.',
            'Call the number shown to make sure it is really them.',
            'Press Approve. The password they chose starts working at once. Press Reject if you are not sure.',
        ],
    },
    {
        title: 'Create a discount coupon',
        go: { href: '/admin/coupons', label: 'Open Coupons' },
        steps: [
            'Click Coupons, then "Add". Type a code, for example WELCOME10.',
            'Choose percent or fixed amount and the value. Add a minimum order or an end date if you want.',
            'Press Save. Customers type the code when they order.',
        ],
    },
    {
        title: 'Answer a customer question or hide a review',
        go: { href: '/admin/questions', label: 'Open Q&A' },
        steps: [
            'Q&A shows questions asked on product pages. Type your answer and save: it appears on the product page.',
            'Reviews: you can hide or delete a review you do not want shown.',
        ],
    },
    {
        title: 'Change phone, address, map, payment numbers or shipping fee',
        go: { href: '/admin/settings', label: 'Open Settings' },
        steps: [
            'Open Settings. The boxes are grouped: General, Announcement, Contact, Shipping and payments, Home page and map.',
            'Fill in the address under "Shop location for the map" to show the map on the home page.',
            'Press Save at the bottom.',
        ],
        needsSettings: true,
    },
    {
        title: 'Add another person who can use this admin panel',
        go: { href: '/admin/admins', label: 'Open Admins' },
        steps: [
            'Open Admins and fill in their name, email and a strong password (10 or more characters, with a capital letter, a small letter and a number).',
            'They sign in at your-site/admin/login. Only give this to people you trust.',
        ],
    },
];

const visible = guides.filter((g) => !g.needsSettings || props.settingsOpen);
</script>

<template>
    <Head title="Help" />

    <AdminLayout title="Help">
        <p class="mb-4 max-w-3xl text-sm text-gray-500">Short answers to "how do I...?". Every change is live on the shop as soon as you press Save. Nothing here can break the website.</p>

        <nav class="mb-6 flex flex-wrap gap-2" aria-label="Jump to">
            <a v-for="(g, i) in visible" :key="g.title" :href="`#g${i}`" class="rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:border-green-500 hover:text-green-700">{{ g.title }}</a>
        </nav>

        <div class="grid gap-4 lg:grid-cols-2">
            <section v-for="(g, i) in visible" :id="`g${i}`" :key="g.title" class="scroll-mt-20 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="font-bold text-gray-900">{{ g.title }}</h2>
                <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm leading-relaxed text-gray-700">
                    <li v-for="step in g.steps" :key="step">{{ step }}</li>
                </ol>
                <Link :href="g.go.href" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-green-600 px-4 text-sm font-semibold text-white hover:bg-green-700">{{ g.go.label }}</Link>
            </section>
        </div>

        <section class="mt-6 rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm sm:p-5">
            <h2 class="font-bold text-gray-900">Good to know</h2>
            <ul class="mt-2 list-disc space-y-1.5 pl-5 leading-relaxed">
                <li>If a change does not show on the shop, refresh the page once (Ctrl + Shift + R on a computer).</li>
                <li>Pictures: JPG, PNG or WebP. Clear, well-lit photos sell best. Phone photos are fine.</li>
                <li>Deleting always asks "Are you sure?" first. A deleted product or category cannot be brought back.</li>
                <li>The big banner always stays at the very top of the home page.</li>
            </ul>
        </section>
    </AdminLayout>
</template>

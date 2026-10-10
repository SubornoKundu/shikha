<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Phone } from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ pending: Array, recent: Array });

const when = (d) => (d ? new Date(d).toLocaleString() : '');

function approve(r) {
    if (window.confirm(`Did you call ${r.phone} and confirm it is ${r.customer.name}? The new password will start working now.`)) {
        router.patch(`/admin/password-requests/${r.id}/approve`, {}, { preserveScroll: true });
    }
}

function reject(r) {
    if (window.confirm('Reject this request? The password stays as it is.')) {
        router.patch(`/admin/password-requests/${r.id}/reject`, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Password requests" />

    <AdminLayout title="Password requests">
        <p class="mb-4 max-w-2xl text-sm text-gray-500">A customer only appears here when the email and phone number they typed belong to the same account. Call the number, check it is really them, then press Approve. To choose a password yourself, open the customer's page.</p>

        <div class="space-y-3">
            <div v-for="r in pending" :key="r.id" class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="min-w-0 text-sm">
                    <Link :href="`/admin/customers/${r.customer.id}`" class="font-semibold text-green-700 hover:underline">{{ r.customer.name }}</Link>
                    <p class="break-all text-gray-500">{{ r.customer.email }}</p>
                    <a :href="`tel:${r.phone}`" class="mt-1 inline-flex min-h-10 items-center gap-1.5 font-semibold text-gray-900"><Phone class="h-4 w-4 text-green-600" /> {{ r.phone }}</a>
                    <p class="text-xs text-gray-400">Asked {{ when(r.created_at) }}</p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <button type="button" class="rounded-full bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-700" @click="approve(r)">Approve</button>
                    <button type="button" class="rounded-full border border-rose-200 px-5 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50" @click="reject(r)">Reject</button>
                </div>
            </div>
            <p v-if="!pending.length" class="rounded-2xl bg-white p-8 text-center text-sm text-gray-400 shadow-sm">No password requests waiting.</p>
        </div>

        <template v-if="recent.length">
            <h2 class="mt-8 mb-3 font-bold text-gray-900">Handled</h2>
            <ul class="divide-y divide-gray-100 rounded-2xl bg-white shadow-sm">
                <li v-for="r in recent" :key="r.id" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                    <span class="min-w-0"><span class="font-medium text-gray-900">{{ r.customer.name }}</span> <span class="break-all text-gray-400">{{ r.customer.email }}</span></span>
                    <span class="text-xs" :class="r.status === 'approved' ? 'text-green-700' : 'text-rose-600'">{{ r.status === 'approved' ? 'Approved' : 'Rejected' }}<template v-if="r.handled_by"> by {{ r.handled_by }}</template>, {{ when(r.handled_at) }}</span>
                </li>
            </ul>
        </template>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthCard from '@/Components/AuthCard.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { routes } from '@/routes';

const form = useForm({ email: '', phone: '', password: '', password_confirmation: '' });
const input = 'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none';

function submit() {
    form.post(routes.forgotPassword(), { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <Head title="Forgot password" />

    <AuthCard title="Forgot your password?" subtitle="Give the email and phone number of your account and the new password you want. We will call you to confirm, then it starts working.">
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label for="fp-email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input id="fp-email" v-model="form.email" type="email" autocomplete="email" autofocus required placeholder="The email of your account" :class="input">
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>
            <div>
                <label for="fp-phone" class="mb-1 block text-sm font-medium text-gray-700">Phone number</label>
                <input id="fp-phone" v-model="form.phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="01XXXXXXXXX" :class="input">
                <p class="mt-1 text-xs text-gray-500">The number you gave when you made the account. We will call this number.</p>
                <p v-if="form.errors.phone" class="mt-1 text-xs text-rose-600">{{ form.errors.phone }}</p>
            </div>
            <div>
                <label for="fp-password" class="mb-1 block text-sm font-medium text-gray-700">New password</label>
                <PasswordInput id="fp-password" v-model="form.password" placeholder="At least 8 characters" autocomplete="new-password" required :class="input" />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>
            <div>
                <label for="fp-password2" class="mb-1 block text-sm font-medium text-gray-700">Type the new password again</label>
                <PasswordInput id="fp-password2" v-model="form.password_confirmation" placeholder="Same password" autocomplete="new-password" required :class="input" />
            </div>
            <button type="submit" :disabled="form.processing" class="w-full rounded-full bg-green-600 py-2.5 font-semibold text-white hover:bg-green-700 disabled:opacity-60">
                {{ form.processing ? 'Sending...' : 'Send request' }}
            </button>
        </form>

        <template #footer>
            Remembered it?
            <Link :href="routes.login()" class="font-semibold text-green-700 hover:underline">Back to sign in</Link>
        </template>
    </AuthCard>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
});

function submit() {
    form.post('/forgot-password');
}
</script>

<template>
    <GuestLayout title="Quên mật khẩu">
        <p class="mb-4 text-sm text-gray-500">
            Nhập email đã đăng ký, chúng tôi sẽ gửi liên kết đặt lại mật khẩu.
        </p>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
            >
                Gửi liên kết đặt lại mật khẩu
            </button>

            <p class="text-center text-sm text-gray-500">
                <a href="/login" class="text-emerald-600 hover:underline">Quay lại đăng nhập</a>
            </p>
        </form>
    </GuestLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b bg-white">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <Link href="/" class="text-lg font-bold text-emerald-600">MilkMart</Link>
                <div class="flex items-center gap-6 text-sm text-gray-600">
                    <Link href="/products">Sản phẩm</Link>
                    <Link href="/cart">Giỏ hàng</Link>

                    <template v-if="user">
                        <Link v-if="user.role === 'Admin'" href="/admin/register">Tạo tài khoản nội bộ</Link>
                        <Link href="/invoices">Đơn hàng</Link>
                        <span class="text-gray-400">{{ user.fullname || user.email }}</span>
                        <Link href="/logout" method="post" as="button" class="text-gray-600 hover:text-emerald-600">
                            Đăng xuất
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login">Đăng nhập</Link>
                        <Link href="/register">Đăng ký</Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <div v-if="successMessage" class="mb-6 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ successMessage }}
            </div>
            <div v-if="errorMessage" class="mb-6 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ errorMessage }}
            </div>

            <h1 v-if="title" class="mb-6 text-2xl font-semibold text-gray-900">{{ title }}</h1>
            <slot />
        </main>
    </div>
</template>

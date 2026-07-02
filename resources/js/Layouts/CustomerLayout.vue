<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b bg-white">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <Link href="/" class="text-lg font-bold text-emerald-600">MilkMart</Link>
                <div class="flex gap-6 text-sm text-gray-600">
                    <Link href="/products">Sản phẩm</Link>
                    <Link href="/cart">Giỏ hàng</Link>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <h1 v-if="title" class="mb-6 text-2xl font-semibold text-gray-900">{{ title }}</h1>
            <slot />
        </main>
    </div>
</template>

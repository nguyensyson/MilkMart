<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Người dùng</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.users ?? 0 }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Sản phẩm</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.products ?? 0 }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Đơn hàng</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.orders ?? 0 }}</p>
            </div>
        </div>
    </AdminLayout>
</template>

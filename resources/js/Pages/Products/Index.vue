<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <CustomerLayout title="Sản phẩm">
        <div v-if="products.length === 0" class="text-gray-500">
            Chưa có sản phẩm nào. Hãy chạy migration + seeder để có dữ liệu mẫu.
        </div>
        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="product in products"
                :key="product.id"
                class="rounded-lg border bg-white p-4 shadow-sm"
            >
                <h2 class="font-semibold text-gray-900">{{ product.name }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ product.description }}</p>
            </div>
        </div>
    </CustomerLayout>
</template>

<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    cart: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <CustomerLayout title="Giỏ hàng">
        <div v-if="!cart || !cart.items?.length" class="text-gray-500">
            Giỏ hàng đang trống.
        </div>
        <ul v-else class="divide-y rounded-lg border bg-white">
            <li v-for="item in cart.items" :key="item.id" class="flex items-center justify-between p-4">
                <span>{{ item.variant?.product?.name }}</span>
                <span class="text-sm text-gray-500">x{{ item.quantity }}</span>
            </li>
        </ul>
    </CustomerLayout>
</template>

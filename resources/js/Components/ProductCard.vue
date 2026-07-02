<script setup>
defineProps({
    product: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="rounded-lg border bg-white p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900">{{ product.name }}</h3>
        <p v-if="product.description" class="mt-1 text-sm text-gray-500">{{ product.description }}</p>
    </div>
</template>

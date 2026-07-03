<script setup>
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/currency';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const image = props.product.cheapest_variant?.image_url;
</script>

<template>
    <Link
        :href="`/products/${product.id}`"
        class="block overflow-hidden rounded-lg border bg-white shadow-sm transition hover:shadow-md"
    >
        <div class="flex aspect-square items-center justify-center bg-gray-50">
            <img
                v-if="image"
                :src="image"
                :alt="product.name"
                class="h-full w-full object-contain p-4"
                loading="lazy"
            />
            <span v-else class="text-sm text-gray-300">Không có ảnh</span>
        </div>
        <div class="p-4">
            <p class="text-xs text-gray-400">{{ product.brand?.name }}</p>
            <h3 class="mt-1 line-clamp-2 font-semibold text-gray-900">{{ product.name }}</h3>
            <p class="mt-2 text-sm font-medium text-emerald-600">
                {{ product.min_price !== null ? `Từ ${formatCurrency(product.min_price)}` : 'Liên hệ' }}
            </p>
        </div>
    </Link>
</template>

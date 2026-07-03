<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { computed, ref } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    variants: {
        type: Array,
        required: true,
    },
});

const selectedVariantId = ref(props.variants[0]?.id);
const activeImageIndex = ref(0);

const selectedVariant = computed(
    () => props.variants.find((variant) => variant.id === selectedVariantId.value) ?? props.variants[0],
);

const gallery = computed(() => {
    const variant = selectedVariant.value;
    if (!variant) {
        return [];
    }
    if (variant.images?.length) {
        return variant.images.map((image) => image.image_url);
    }
    return variant.image_url ? [variant.image_url] : [];
});

const inStock = computed(() => (selectedVariant.value?.stock_quantity ?? 0) > 0);

function selectVariant(variantId) {
    selectedVariantId.value = variantId;
    activeImageIndex.value = 0;
}

function variantLabel(variant) {
    if (variant.weight) {
        return `${variant.weight} g`;
    }
    return variant.sku ?? `Phân loại #${variant.id}`;
}
</script>

<template>
    <CustomerLayout :title="product.name">
        <div v-if="!selectedVariant" class="rounded-lg border bg-white p-8 text-center text-gray-500">
            Sản phẩm hiện không có phân loại nào còn bán.
        </div>

        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <div>
                <div class="flex aspect-square items-center justify-center overflow-hidden rounded-lg border bg-gray-50">
                    <img
                        v-if="gallery[activeImageIndex]"
                        :src="gallery[activeImageIndex]"
                        :alt="product.name"
                        class="h-full w-full object-contain p-6"
                    />
                    <span v-else class="text-sm text-gray-300">Không có ảnh</span>
                </div>
                <div v-if="gallery.length > 1" class="mt-3 flex gap-2">
                    <button
                        v-for="(image, index) in gallery"
                        :key="image + index"
                        type="button"
                        class="h-16 w-16 overflow-hidden rounded border"
                        :class="index === activeImageIndex ? 'border-emerald-600' : 'border-gray-200'"
                        @click="activeImageIndex = index"
                    >
                        <img :src="image" :alt="product.name" class="h-full w-full object-contain p-1" />
                    </button>
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-400">{{ product.brand?.name }} · {{ product.category?.name }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">{{ product.name }}</h1>
                <p class="mt-3 text-2xl font-bold text-emerald-600">{{ formatCurrency(selectedVariant.price) }}</p>

                <span
                    class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="inStock ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                >
                    {{ inStock ? `Còn hàng (${selectedVariant.stock_quantity})` : 'Hết hàng' }}
                </span>

                <div v-if="variants.length > 1" class="mt-6">
                    <p class="mb-2 text-sm font-medium text-gray-700">Phân loại</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="variant in variants"
                            :key="variant.id"
                            type="button"
                            class="rounded border px-3 py-2 text-sm"
                            :class="
                                variant.id === selectedVariant.id
                                    ? 'border-emerald-600 bg-emerald-50 text-emerald-700'
                                    : 'border-gray-300 text-gray-600 hover:bg-gray-50'
                            "
                            @click="selectVariant(variant.id)"
                        >
                            {{ variantLabel(variant) }}
                        </button>
                    </div>
                </div>

                <p v-if="product.description" class="mt-6 whitespace-pre-line text-sm text-gray-600">
                    {{ product.description }}
                </p>
            </div>
        </div>
    </CustomerLayout>
</template>

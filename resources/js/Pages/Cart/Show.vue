<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cart: {
        type: Object,
        required: true,
    },
});

const updatingId = ref(null);

function variantLabel(variant) {
    if (variant.weight) {
        return `${variant.weight} g`;
    }
    return variant.sku ?? `Phân loại #${variant.id}`;
}

function updateQuantity(item, quantity) {
    if (quantity < 1 || quantity > item.variant.stock_quantity || quantity === item.quantity || updatingId.value) {
        return;
    }

    updatingId.value = item.id;
    router.put(
        `/cart/items/${item.id}`,
        { quantity },
        {
            preserveScroll: true,
            onFinish: () => {
                updatingId.value = null;
            },
        },
    );
}

function removeItem(item) {
    if (!confirm(`Xóa "${item.variant.product?.name}" khỏi giỏ hàng?`)) {
        return;
    }

    router.delete(`/cart/items/${item.id}`, { preserveScroll: true });
}
</script>

<template>
    <CustomerLayout title="Giỏ hàng">
        <div v-if="!cart.items.length" class="rounded-lg border bg-white p-10 text-center">
            <p class="text-gray-500">Giỏ hàng của bạn đang trống.</p>
            <Link
                href="/products"
                class="mt-4 inline-block rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
            >
                Tiếp tục mua sắm
            </Link>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <ul class="divide-y rounded-lg border bg-white">
                    <li v-for="item in cart.items" :key="item.id" class="flex flex-wrap items-center gap-4 p-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded border bg-gray-50">
                            <img
                                v-if="item.variant.image_url"
                                :src="item.variant.image_url"
                                :alt="item.variant.product.name"
                                class="h-full w-full object-contain p-1"
                            />
                            <span v-else class="text-xs text-gray-300">—</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <Link :href="`/products/${item.variant.product.id}`" class="truncate font-medium text-gray-900 hover:text-emerald-600">
                                {{ item.variant.product.name }}
                            </Link>
                            <p class="text-sm text-gray-500">{{ variantLabel(item.variant) }} · {{ formatCurrency(item.unit_price) }}</p>
                        </div>

                        <div class="flex items-center rounded border border-gray-300">
                            <button
                                type="button"
                                class="px-2 py-1 text-gray-600 hover:bg-gray-50 disabled:opacity-40"
                                :disabled="updatingId === item.id || item.quantity <= 1"
                                @click="updateQuantity(item, item.quantity - 1)"
                            >
                                −
                            </button>
                            <span class="w-10 text-center text-sm">{{ item.quantity }}</span>
                            <button
                                type="button"
                                class="px-2 py-1 text-gray-600 hover:bg-gray-50 disabled:opacity-40"
                                :disabled="updatingId === item.id || item.quantity >= item.variant.stock_quantity"
                                @click="updateQuantity(item, item.quantity + 1)"
                            >
                                +
                            </button>
                        </div>

                        <div class="w-28 shrink-0 text-right font-medium text-gray-900">{{ formatCurrency(item.subtotal) }}</div>

                        <button type="button" class="shrink-0 text-sm text-red-600 hover:underline" @click="removeItem(item)">
                            Xóa
                        </button>
                    </li>
                </ul>
            </div>

            <div class="h-fit rounded-lg border bg-white p-6">
                <div class="flex items-center justify-between text-lg font-semibold text-gray-900">
                    <span>Tổng cộng</span>
                    <span>{{ formatCurrency(cart.total) }}</span>
                </div>
                <Link
                    href="/checkout"
                    class="mt-6 block w-full rounded bg-emerald-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-emerald-700"
                >
                    Tiến hành thanh toán
                </Link>
            </div>
        </div>
    </CustomerLayout>
</template>

<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { ORDER_STATUS_BADGE, orderStatusLabel, paymentStatusLabel } from '@/utils/orderStatus';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    cancellableStatuses: {
        type: Array,
        default: () => [],
    },
});

const cancelling = ref(false);
const canCancel = computed(() => props.cancellableStatuses.includes(props.order.order_status));

function cancelOrder() {
    if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
        return;
    }

    cancelling.value = true;
    router.post(
        `/orders/${props.order.id}/cancel`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                cancelling.value = false;
            },
        },
    );
}
</script>

<template>
    <CustomerLayout :title="`Đơn hàng ${order.invoice_code}`">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-lg border bg-white p-4">
                    <h2 class="mb-3 font-semibold text-gray-900">Sản phẩm</h2>
                    <ul class="divide-y">
                        <li v-for="detail in order.details" :key="detail.id" class="flex items-center justify-between py-3 text-sm">
                            <div>
                                <p class="font-medium text-gray-900">{{ detail.variant?.product?.name ?? 'Sản phẩm đã ngừng bán' }}</p>
                                <p class="text-gray-500">SL: {{ detail.quantity }} × {{ formatCurrency(detail.unit_price) }}</p>
                            </div>
                            <span class="font-medium text-gray-900">{{ formatCurrency(detail.total_price) }}</span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-lg border bg-white p-4 text-sm">
                    <h2 class="mb-2 font-semibold text-gray-900">Địa chỉ giao hàng</h2>
                    <p class="whitespace-pre-line text-gray-600">{{ order.shipping_address }}</p>
                </div>
            </div>

            <div class="h-fit space-y-4 rounded-lg border bg-white p-6">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Trạng thái đơn</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="ORDER_STATUS_BADGE[order.order_status]">
                        {{ orderStatusLabel(order.order_status) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Thanh toán</span>
                    <span>{{ paymentStatusLabel(order.payment_status) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Tạm tính</span>
                    <span>{{ formatCurrency(order.subtotal) }}</span>
                </div>
                <div v-if="order.discount_amount > 0" class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Giảm giá{{ order.voucher ? ` (${order.voucher.code})` : '' }}</span>
                    <span>-{{ formatCurrency(order.discount_amount) }}</span>
                </div>
                <div class="flex items-center justify-between border-t pt-3 text-base font-semibold text-gray-900">
                    <span>Tổng cộng</span>
                    <span>{{ formatCurrency(order.total_amount) }}</span>
                </div>

                <button
                    v-if="canCancel"
                    type="button"
                    class="w-full rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                    :disabled="cancelling"
                    @click="cancelOrder"
                >
                    {{ cancelling ? 'Đang hủy...' : 'Hủy đơn hàng' }}
                </button>
            </div>
        </div>

        <Link href="/orders" class="mt-6 inline-block text-sm text-emerald-600 hover:underline">← Quay lại danh sách đơn hàng</Link>
    </CustomerLayout>
</template>

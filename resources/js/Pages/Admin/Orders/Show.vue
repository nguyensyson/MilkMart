<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { orderStatusLabel, paymentStatusLabel } from '@/utils/orderStatus';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    orderTransitions: {
        type: Object,
        default: () => ({}),
    },
    paymentStatuses: {
        type: Array,
        default: () => [],
    },
});

const nextOrderStatus = ref('');
const nextPaymentStatus = ref(props.order.payment_status);
const updatingOrderStatus = ref(false);
const updatingPaymentStatus = ref(false);

const allowedNextStatuses = computed(() => props.orderTransitions[props.order.order_status] ?? []);

function submitOrderStatus() {
    if (!nextOrderStatus.value || updatingOrderStatus.value) {
        return;
    }

    updatingOrderStatus.value = true;
    router.put(
        `/admin/orders/${props.order.id}/order-status`,
        { order_status: nextOrderStatus.value },
        {
            preserveScroll: true,
            onFinish: () => {
                updatingOrderStatus.value = false;
                nextOrderStatus.value = '';
            },
        },
    );
}

function submitPaymentStatus() {
    if (!nextPaymentStatus.value || nextPaymentStatus.value === props.order.payment_status || updatingPaymentStatus.value) {
        return;
    }

    updatingPaymentStatus.value = true;
    router.put(
        `/admin/orders/${props.order.id}/payment-status`,
        { payment_status: nextPaymentStatus.value },
        {
            preserveScroll: true,
            onFinish: () => {
                updatingPaymentStatus.value = false;
            },
        },
    );
}
</script>

<template>
    <AdminLayout :title="`Đơn hàng ${order.invoice_code}`">
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
                    <h2 class="mb-2 font-semibold text-gray-900">Khách hàng</h2>
                    <p class="text-gray-900">{{ order.user?.fullname }}</p>
                    <p class="text-gray-500">{{ order.user?.email }} · {{ order.user?.phone }}</p>
                </div>

                <div class="rounded-lg border bg-white p-4 text-sm">
                    <h2 class="mb-2 font-semibold text-gray-900">Địa chỉ giao hàng</h2>
                    <p class="whitespace-pre-line text-gray-600">{{ order.shipping_address }}</p>
                </div>
            </div>

            <div class="h-fit space-y-5 rounded-lg border bg-white p-6">
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Tạm tính</span>
                        <span>{{ formatCurrency(order.subtotal) }}</span>
                    </div>
                    <div v-if="order.discount_amount > 0" class="flex items-center justify-between">
                        <span class="text-gray-500">Giảm giá{{ order.voucher ? ` (${order.voucher.code})` : '' }}</span>
                        <span>-{{ formatCurrency(order.discount_amount) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t pt-2 text-base font-semibold text-gray-900">
                        <span>Tổng cộng</span>
                        <span>{{ formatCurrency(order.total_amount) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Trạng thái đơn hàng</label>
                    <p class="mt-1 text-sm text-gray-500">
                        Hiện tại: <strong>{{ orderStatusLabel(order.order_status) }}</strong>
                    </p>
                    <div v-if="allowedNextStatuses.length" class="mt-2 flex gap-2">
                        <select
                            v-model="nextOrderStatus"
                            class="flex-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="" disabled>Chuyển sang...</option>
                            <option v-for="s in allowedNextStatuses" :key="s" :value="s">{{ orderStatusLabel(s) }}</option>
                        </select>
                        <button
                            type="button"
                            class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="!nextOrderStatus || updatingOrderStatus"
                            @click="submitOrderStatus"
                        >
                            Cập nhật
                        </button>
                    </div>
                    <p v-else class="mt-2 text-sm text-gray-400">Đơn hàng đã ở trạng thái cuối cùng.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Trạng thái thanh toán</label>
                    <div class="mt-2 flex gap-2">
                        <select
                            v-model="nextPaymentStatus"
                            class="flex-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        >
                            <option v-for="s in paymentStatuses" :key="s" :value="s">{{ paymentStatusLabel(s) }}</option>
                        </select>
                        <button
                            type="button"
                            class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="nextPaymentStatus === order.payment_status || updatingPaymentStatus"
                            @click="submitPaymentStatus"
                        >
                            Cập nhật
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Link href="/admin/orders" class="mt-6 inline-block text-sm text-emerald-600 hover:underline">← Quay lại danh sách đơn hàng</Link>
    </AdminLayout>
</template>

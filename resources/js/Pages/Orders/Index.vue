<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { ORDER_STATUS_BADGE, orderStatusLabel, paymentStatusLabel } from '@/utils/orderStatus';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    orders: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const status = ref(props.filters.status ?? '');

function applyFilters() {
    router.get('/orders', { status: status.value || undefined }, { preserveState: true, replace: true });
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('vi-VN');
}
</script>

<template>
    <CustomerLayout title="Đơn hàng của tôi">
        <div class="mb-4 flex items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <select
                    v-model="status"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="">Tất cả</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="shipping">Đang giao</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
        </div>

        <div v-if="orders.data.length === 0" class="rounded-lg border bg-white p-10 text-center text-gray-500">
            Bạn chưa có đơn hàng nào.
        </div>
        <table v-else class="w-full rounded-lg border bg-white text-left text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="p-3">Mã đơn</th>
                    <th class="p-3">Ngày đặt</th>
                    <th class="p-3">Tổng tiền</th>
                    <th class="p-3">Trạng thái</th>
                    <th class="p-3">Thanh toán</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="order in orders.data" :key="order.id" class="border-b last:border-0">
                    <td class="p-3 font-medium text-gray-900">{{ order.invoice_code }}</td>
                    <td class="p-3">{{ formatDate(order.created_at) }}</td>
                    <td class="p-3">{{ formatCurrency(order.total_amount) }}</td>
                    <td class="p-3">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="ORDER_STATUS_BADGE[order.order_status]">
                            {{ orderStatusLabel(order.order_status) }}
                        </span>
                    </td>
                    <td class="p-3">{{ paymentStatusLabel(order.payment_status) }}</td>
                    <td class="p-3 text-right">
                        <Link :href="`/orders/${order.id}`" class="text-emerald-600 hover:underline">Xem chi tiết</Link>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="orders.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <template v-for="link in orders.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-state
                    class="rounded border px-3 py-1 text-sm"
                    :class="link.active ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                    v-html="link.label"
                />
                <span v-else class="rounded border border-gray-200 px-3 py-1 text-sm text-gray-300" v-html="link.label" />
            </template>
        </div>
    </CustomerLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
    orderStatuses: {
        type: Array,
        default: () => [],
    },
    paymentStatuses: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search ?? '');
const orderStatus = ref(props.filters.order_status ?? '');
const paymentStatus = ref(props.filters.payment_status ?? '');

function applyFilters() {
    router.get(
        '/admin/orders',
        {
            search: search.value || undefined,
            order_status: orderStatus.value || undefined,
            payment_status: paymentStatus.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('vi-VN');
}
</script>

<template>
    <AdminLayout title="Quản lý đơn hàng">
        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Mã đơn hoặc tên khách hàng"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @keyup.enter="applyFilters"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Trạng thái đơn</label>
                <select
                    v-model="orderStatus"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="">Tất cả</option>
                    <option v-for="s in orderStatuses" :key="s" :value="s">{{ orderStatusLabel(s) }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Thanh toán</label>
                <select
                    v-model="paymentStatus"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="">Tất cả</option>
                    <option v-for="s in paymentStatuses" :key="s" :value="s">{{ paymentStatusLabel(s) }}</option>
                </select>
            </div>
            <button type="button" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="applyFilters">
                Lọc
            </button>
        </div>

        <div v-if="orders.data.length === 0" class="text-gray-500">Không tìm thấy đơn hàng nào.</div>
        <table v-else class="w-full rounded-lg border bg-white text-left text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="p-3">Mã đơn</th>
                    <th class="p-3">Khách hàng</th>
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
                    <td class="p-3">{{ order.user?.fullname || order.user?.email }}</td>
                    <td class="p-3">{{ formatDate(order.created_at) }}</td>
                    <td class="p-3">{{ formatCurrency(order.total_amount) }}</td>
                    <td class="p-3">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="ORDER_STATUS_BADGE[order.order_status]">
                            {{ orderStatusLabel(order.order_status) }}
                        </span>
                    </td>
                    <td class="p-3">{{ paymentStatusLabel(order.payment_status) }}</td>
                    <td class="p-3 text-right">
                        <Link :href="`/admin/orders/${order.id}`" class="text-emerald-600 hover:underline">Xem chi tiết</Link>
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
    </AdminLayout>
</template>

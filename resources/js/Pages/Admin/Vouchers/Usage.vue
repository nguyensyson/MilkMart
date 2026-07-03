<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { orderStatusLabel } from '@/utils/orderStatus';
import { discountLabel } from '@/utils/voucherStatus';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    voucher: {
        type: Object,
        required: true,
    },
    usages: {
        type: Object,
        required: true,
    },
});

function formatDate(value) {
    return value ? new Date(value).toLocaleString('vi-VN') : '—';
}
</script>

<template>
    <AdminLayout :title="`Lịch sử sử dụng · ${voucher.code}`">
        <div class="mb-6 rounded-lg border bg-white p-4 text-sm">
            <p class="text-gray-500">
                Giảm
                <span v-if="discountLabel(voucher)">{{ discountLabel(voucher) }}</span>
                <span v-else>{{ formatCurrency(voucher.discount_value) }}</span>
                · Tổng lượt: {{ voucher.quantity ?? 'Không giới hạn' }}
            </p>
        </div>

        <div v-if="usages.data.length === 0" class="text-gray-500">Voucher này chưa được sử dụng lần nào.</div>
        <div v-else class="overflow-x-auto rounded-lg border bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">Khách hàng</th>
                        <th class="p-3">Đơn hàng</th>
                        <th class="p-3">Thời điểm dùng</th>
                        <th class="p-3">Số tiền giảm</th>
                        <th class="p-3">Trạng thái đơn</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="usage in usages.data" :key="usage.id" class="border-b last:border-0">
                        <td class="p-3">{{ usage.user?.fullname || usage.user?.email }}</td>
                        <td class="p-3">
                            <Link v-if="usage.invoice" :href="`/admin/orders/${usage.invoice_id}`" class="text-emerald-600 hover:underline">
                                {{ usage.invoice.invoice_code }}
                            </Link>
                        </td>
                        <td class="p-3 text-gray-600">{{ formatDate(usage.used_at) }}</td>
                        <td class="p-3 text-gray-900">{{ formatCurrency(usage.invoice?.discount_amount) }}</td>
                        <td class="p-3 text-gray-600">
                            {{ usage.invoice ? orderStatusLabel(usage.invoice.order_status) : '—' }}
                            <span v-if="usage.invoice?.order_status === 'cancelled'" class="text-xs text-gray-400">
                                (không tính vào lượt dùng)
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="usages.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <template v-for="link in usages.links" :key="link.label">
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

        <Link href="/admin/vouchers" class="mt-6 inline-block text-sm text-emerald-600 hover:underline">← Quay lại danh sách voucher</Link>
    </AdminLayout>
</template>

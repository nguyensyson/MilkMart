<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { discountLabel } from '@/utils/voucherStatus';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import ReportTabs from './Partials/ReportTabs.vue';

const props = defineProps({
    vouchers: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const range = reactive({ from: props.filters.from ?? '', to: props.filters.to ?? '' });

function apply() {
    router.get(
        '/admin/reports/vouchers',
        { from: range.from || undefined, to: range.to || undefined },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <AdminLayout title="Báo cáo & thống kê">
        <ReportTabs />

        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Từ ngày</label>
                <input
                    v-model="range.from"
                    type="date"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="apply"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Đến ngày</label>
                <input
                    v-model="range.to"
                    type="date"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="apply"
                />
            </div>
            <button
                type="button"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                @click="apply"
            >
                Lọc
            </button>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Tổng số voucher</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.total_vouchers }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Tổng lượt sử dụng</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.total_usage_count }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Tổng tiền đã giảm</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.total_discount_given) }}</p>
            </div>
        </div>

        <div v-if="vouchers.length === 0" class="mt-6 text-gray-500">Chưa có voucher nào.</div>
        <div v-else class="mt-6 overflow-x-auto rounded-lg border bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">Mã</th>
                        <th class="p-3">Giảm giá</th>
                        <th class="p-3">Lượt dùng</th>
                        <th class="p-3">Giá trị đơn phát sinh</th>
                        <th class="p-3">Tổng tiền đã giảm</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="voucher in vouchers" :key="voucher.id" class="border-b last:border-0">
                        <td class="p-3 font-medium text-gray-900">{{ voucher.code }}</td>
                        <td class="p-3 text-gray-600">
                            <span v-if="discountLabel(voucher)">{{ discountLabel(voucher) }}</span>
                            <span v-else>{{ formatCurrency(voucher.discount_value) }}</span>
                        </td>
                        <td class="p-3 text-gray-600">{{ voucher.usage_count }}</td>
                        <td class="p-3 text-gray-600">{{ formatCurrency(voucher.total_order_value) }}</td>
                        <td class="p-3 text-gray-600">{{ formatCurrency(voucher.total_discount) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

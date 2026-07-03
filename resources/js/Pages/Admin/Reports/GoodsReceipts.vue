<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import DateRangeFilter from './Partials/DateRangeFilter.vue';
import ReportTabs from './Partials/ReportTabs.vue';
import SimpleBarChart from './Partials/SimpleBarChart.vue';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
    bySupplier: {
        type: Array,
        default: () => [],
    },
    chartData: {
        type: Array,
        default: () => [],
    },
    groupBy: {
        type: String,
        default: 'day',
    },
    suppliers: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const range = reactive({ from: props.filters.from, to: props.filters.to });
const supplierId = ref(props.filters.supplier_id ?? '');

function apply() {
    router.get(
        '/admin/reports/goods-receipts',
        { from: range.from, to: range.to, supplier_id: supplierId.value || undefined },
        { preserveState: true, replace: true },
    );
}

const chartItems = computed(() => props.chartData.map((row) => ({ label: row.period, value: row.total_value })));

const groupByLabel = computed(() => ({ day: 'ngày', week: 'tuần', month: 'tháng' })[props.groupBy] ?? props.groupBy);
</script>

<template>
    <AdminLayout title="Báo cáo & thống kê">
        <ReportTabs />

        <div class="flex flex-wrap items-end gap-3">
            <DateRangeFilter v-model="range" @apply="apply" />
            <div>
                <label class="block text-sm font-medium text-gray-700">Nhà cung cấp</label>
                <select
                    v-model="supplierId"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="apply"
                >
                    <option value="">Tất cả</option>
                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                </select>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Tổng giá trị nhập hàng</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.total_value) }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Số phiếu nhập</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.total_receipts }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-lg border bg-white p-4">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Giá trị nhập hàng theo {{ groupByLabel }}</h2>
            <SimpleBarChart :items="chartItems" :format-value="formatCurrency" />
        </div>

        <div class="mt-6 overflow-x-auto rounded-lg border bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">Nhà cung cấp</th>
                        <th class="p-3">Số phiếu nhập</th>
                        <th class="p-3">Tổng giá trị</th>
                    </tr>
                </thead>
                <tbody v-if="bySupplier.length">
                    <tr v-for="row in bySupplier" :key="row.supplier_id" class="border-b last:border-0">
                        <td class="p-3 font-medium text-gray-900">{{ row.supplier_name }}</td>
                        <td class="p-3 text-gray-600">{{ row.receipts_count }}</td>
                        <td class="p-3 text-gray-600">{{ formatCurrency(row.total_value) }}</td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="3" class="p-6 text-center text-gray-400">Không có dữ liệu trong khoảng thời gian đã chọn.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

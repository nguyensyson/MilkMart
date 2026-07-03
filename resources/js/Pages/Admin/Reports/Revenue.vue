<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import DateRangeFilter from './Partials/DateRangeFilter.vue';
import ReportTabs from './Partials/ReportTabs.vue';
import SimpleBarChart from './Partials/SimpleBarChart.vue';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
    chartData: {
        type: Array,
        default: () => [],
    },
    groupBy: {
        type: String,
        default: 'day',
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const range = reactive({ from: props.filters.from, to: props.filters.to });

function apply() {
    router.get('/admin/reports/revenue', { from: range.from, to: range.to }, { preserveState: true, replace: true });
}

const chartItems = computed(() => props.chartData.map((row) => ({ label: row.period, value: row.revenue })));

const groupByLabel = computed(() => ({ day: 'ngày', week: 'tuần', month: 'tháng' })[props.groupBy] ?? props.groupBy);
</script>

<template>
    <AdminLayout title="Báo cáo & thống kê">
        <ReportTabs />

        <DateRangeFilter v-model="range" @apply="apply" />

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Tổng doanh thu</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.total_revenue) }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Tổng số đơn hàng</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.total_orders }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Giá trị đơn trung bình</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.average_order_value) }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-lg border bg-white p-4">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Doanh thu theo {{ groupByLabel }}</h2>
            <SimpleBarChart :items="chartItems" :format-value="formatCurrency" />
        </div>

        <div class="mt-6 overflow-x-auto rounded-lg border bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">Khoảng thời gian</th>
                        <th class="p-3">Số đơn</th>
                        <th class="p-3">Doanh thu</th>
                    </tr>
                </thead>
                <tbody v-if="chartData.length">
                    <tr v-for="row in chartData" :key="row.period" class="border-b last:border-0">
                        <td class="p-3">{{ row.period }}</td>
                        <td class="p-3">{{ row.orders_count }}</td>
                        <td class="p-3">{{ formatCurrency(row.revenue) }}</td>
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

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SimpleBarChart from '@/Pages/Admin/Reports/Partials/SimpleBarChart.vue';
import { formatCurrency } from '@/utils/currency';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    bestSellers: {
        type: Array,
        default: () => [],
    },
    monthlyRevenue: {
        type: Array,
        default: () => [],
    },
    revenueByBrand: {
        type: Array,
        default: () => [],
    },
});

const revenueChartItems = computed(() =>
    props.monthlyRevenue.map((item) => ({
        label: `Th${item.month}`,
        value: item.revenue,
    })),
);

const brandChartItems = computed(() =>
    props.revenueByBrand.map((item) => ({
        label: item.name,
        value: item.revenue,
    })),
);
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Người dùng</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.users ?? 0 }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Sản phẩm</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.products ?? 0 }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Đơn hàng</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.orders ?? 0 }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-lg border bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Doanh thu theo tháng ({{ new Date().getFullYear() }})</h2>
            <SimpleBarChart :items="revenueChartItems" :format-value="formatCurrency" />
        </div>

        <div class="mt-6 rounded-lg border bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Doanh thu theo thương hiệu</h2>
            <SimpleBarChart :items="brandChartItems" :format-value="formatCurrency" />
        </div>

        <div class="mt-6 rounded-lg border bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Top 5 sản phẩm bán chạy nhất</h2>
            <div v-if="bestSellers.length === 0" class="text-sm text-gray-500">Chưa có dữ liệu bán hàng.</div>
            <table v-else class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Sản phẩm</th>
                        <th class="p-3">Số lượng bán</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in bestSellers" :key="item.id" class="border-b last:border-0">
                        <td class="p-3 text-gray-400">{{ index + 1 }}</td>
                        <td class="p-3 font-medium text-gray-900">{{ item.name }}</td>
                        <td class="p-3 text-gray-600">{{ item.quantity_sold }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

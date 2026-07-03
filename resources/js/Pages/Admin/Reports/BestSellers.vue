<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import DateRangeFilter from './Partials/DateRangeFilter.vue';
import ReportTabs from './Partials/ReportTabs.vue';
import SimpleBarChart from './Partials/SimpleBarChart.vue';

const props = defineProps({
    bestSellers: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const range = reactive({ from: props.filters.from, to: props.filters.to });
const limit = ref(props.filters.limit ?? 10);
const sortBy = ref(props.filters.sort_by ?? 'quantity');

function apply() {
    router.get(
        '/admin/reports/best-sellers',
        { from: range.from, to: range.to, limit: limit.value, sort_by: sortBy.value },
        { preserveState: true, replace: true },
    );
}

const chartItems = computed(() =>
    props.bestSellers.map((item) => ({
        label: item.sku,
        value: sortBy.value === 'revenue' ? item.revenue : item.quantity_sold,
    })),
);

const valueFormatter = computed(() => (sortBy.value === 'revenue' ? formatCurrency : (value) => value));
</script>

<template>
    <AdminLayout title="Báo cáo & thống kê">
        <ReportTabs />

        <div class="flex flex-wrap items-end gap-3">
            <DateRangeFilter v-model="range" @apply="apply" />
            <div>
                <label class="block text-sm font-medium text-gray-700">Top</label>
                <input
                    v-model.number="limit"
                    type="number"
                    min="1"
                    max="100"
                    class="mt-1 w-20 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="apply"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sắp xếp theo</label>
                <select
                    v-model="sortBy"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="apply"
                >
                    <option value="quantity">Số lượng bán</option>
                    <option value="revenue">Doanh thu</option>
                </select>
            </div>
        </div>

        <div class="mt-6 rounded-lg border bg-white p-4">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Xếp hạng sản phẩm bán chạy</h2>
            <SimpleBarChart :items="chartItems" :format-value="valueFormatter" />
        </div>

        <div v-if="bestSellers.length === 0" class="mt-6 text-gray-500">Không có dữ liệu trong khoảng thời gian đã chọn.</div>
        <div v-else class="mt-6 overflow-x-auto rounded-lg border bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Sản phẩm</th>
                        <th class="p-3">SKU</th>
                        <th class="p-3">Số lượng bán</th>
                        <th class="p-3">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in bestSellers" :key="item.product_variant_id" class="border-b last:border-0">
                        <td class="p-3 text-gray-400">{{ index + 1 }}</td>
                        <td class="p-3 font-medium text-gray-900">{{ item.product_name }}</td>
                        <td class="p-3 text-gray-600">{{ item.sku }}</td>
                        <td class="p-3 text-gray-600">{{ item.quantity_sold }}</td>
                        <td class="p-3 text-gray-600">{{ formatCurrency(item.revenue) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

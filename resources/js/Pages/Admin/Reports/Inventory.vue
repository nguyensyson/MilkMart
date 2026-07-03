<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ReportTabs from './Partials/ReportTabs.vue';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
    details: {
        type: Object,
        required: true,
    },
    lowStockThreshold: {
        type: Number,
        default: 100,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const categoryId = ref(props.filters.category_id ?? '');
const brandId = ref(props.filters.brand_id ?? '');
const lowStockOnly = ref(Boolean(props.filters.low_stock));

function applyFilters() {
    router.get(
        '/admin/reports/inventory',
        {
            category_id: categoryId.value || undefined,
            brand_id: brandId.value || undefined,
            low_stock: lowStockOnly.value ? 1 : undefined,
        },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <AdminLayout title="Báo cáo & thống kê">
        <ReportTabs />

        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Danh mục</label>
                <select
                    v-model="categoryId"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="">Tất cả</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Thương hiệu</label>
                <select
                    v-model="brandId"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="">Tất cả</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                </select>
            </div>
            <label class="flex items-center gap-2 pb-2 text-sm text-gray-700">
                <input v-model="lowStockOnly" type="checkbox" @change="applyFilters" />
                Chỉ hiện sắp hết hàng (≤ {{ lowStockThreshold }})
            </label>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Số biến thể</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.total_variants }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Tổng số lượng tồn</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.total_stock_quantity }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Giá trị tồn kho</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.total_stock_value) }}</p>
            </div>
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Sắp hết hàng</p>
                <p class="mt-1 text-2xl font-semibold text-red-600">{{ summary.low_stock_count }}</p>
            </div>
        </div>

        <div v-if="details.data.length === 0" class="text-gray-500">Không có biến thể nào phù hợp.</div>
        <div v-else class="overflow-x-auto rounded-lg border bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">Sản phẩm</th>
                        <th class="p-3">SKU</th>
                        <th class="p-3">Danh mục</th>
                        <th class="p-3">Thương hiệu</th>
                        <th class="p-3">Giá</th>
                        <th class="p-3">Tồn kho</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="variant in details.data"
                        :key="variant.id"
                        class="border-b last:border-0"
                        :class="variant.stock_quantity <= lowStockThreshold ? 'bg-red-50' : ''"
                    >
                        <td class="p-3 font-medium text-gray-900">{{ variant.product?.name }}</td>
                        <td class="p-3 text-gray-600">{{ variant.sku }}</td>
                        <td class="p-3 text-gray-600">{{ variant.product?.category?.name || '—' }}</td>
                        <td class="p-3 text-gray-600">{{ variant.product?.brand?.name || '—' }}</td>
                        <td class="p-3 text-gray-600">{{ formatCurrency(variant.price) }}</td>
                        <td class="p-3">
                            <span :class="variant.stock_quantity <= lowStockThreshold ? 'font-semibold text-red-600' : 'text-gray-700'">
                                {{ variant.stock_quantity }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="details.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <template v-for="link in details.links" :key="link.label">
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

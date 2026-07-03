<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: {
        type: Object,
        required: true,
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

const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? '');
const brandId = ref(props.filters.brand_id ?? '');
const sort = ref(props.filters.sort ?? 'newest');
const loading = ref(false);

function applyFilters() {
    router.get(
        '/products',
        {
            search: search.value || undefined,
            category_id: categoryId.value || undefined,
            brand_id: brandId.value || undefined,
            sort: sort.value !== 'newest' ? sort.value : undefined,
        },
        {
            preserveState: true,
            replace: true,
            onStart: () => (loading.value = true),
            onFinish: () => (loading.value = false),
        },
    );
}
</script>

<template>
    <CustomerLayout title="Sản phẩm">
        <div class="mb-6 flex flex-wrap items-end gap-3">
            <div class="min-w-[200px] flex-1">
                <label class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tên sản phẩm..."
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @keyup.enter="applyFilters"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Danh mục</label>
                <select
                    v-model="categoryId"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="">Tất cả</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
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
            <div>
                <label class="block text-sm font-medium text-gray-700">Sắp xếp</label>
                <select
                    v-model="sort"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="newest">Mới nhất</option>
                    <option value="price_asc">Giá tăng dần</option>
                    <option value="price_desc">Giá giảm dần</option>
                    <option value="name">Tên A-Z</option>
                </select>
            </div>
            <button
                type="button"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                @click="applyFilters"
            >
                Lọc
            </button>
        </div>

        <div v-if="loading" class="text-sm text-gray-400">Đang tải...</div>

        <div v-else-if="products.data.length === 0" class="rounded-lg border bg-white p-8 text-center text-gray-500">
            Không tìm thấy sản phẩm nào phù hợp.
        </div>

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
        </div>

        <div v-if="!loading && products.data.length > 0 && products.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <template v-for="link in products.links" :key="link.label">
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

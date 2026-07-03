<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
const status = ref(props.filters.status ?? '');

function applyFilters() {
    router.get(
        '/admin/products',
        {
            search: search.value || undefined,
            category_id: categoryId.value || undefined,
            brand_id: brandId.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function destroyProduct(product) {
    if (!confirm(`Xóa sản phẩm "${product.name}"? Toàn bộ biến thể và hình ảnh liên quan cũng sẽ bị xóa.`)) {
        return;
    }

    router.delete(`/admin/products/${product.id}`);
}
</script>

<template>
    <AdminLayout title="Quản lý sản phẩm">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">Danh sách toàn bộ sản phẩm, bao gồm cả sản phẩm đã ẩn/ngừng bán.</p>
            <Link
                href="/admin/products/create"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
            >
                Thêm sản phẩm
            </Link>
        </div>

        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tên sản phẩm"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
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
            <div>
                <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <select
                    v-model="status"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="">Tất cả</option>
                    <option value="visible">Đang hiển thị</option>
                    <option value="hidden">Đã ẩn/ngừng bán</option>
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

        <div v-if="products.data.length === 0" class="text-gray-500">Không tìm thấy sản phẩm nào.</div>
        <table v-else class="w-full rounded-lg border bg-white text-left text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="p-3">Ảnh</th>
                    <th class="p-3">Tên sản phẩm</th>
                    <th class="p-3">Danh mục</th>
                    <th class="p-3">Thương hiệu</th>
                    <th class="p-3">Biến thể</th>
                    <th class="p-3">Trạng thái</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products.data" :key="product.id" class="border-b last:border-0">
                    <td class="p-3">
                        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded border bg-gray-50">
                            <img
                                v-if="product.primary_image?.image_url"
                                :src="product.primary_image.image_url"
                                :alt="product.name"
                                class="h-full w-full object-contain"
                            />
                            <span v-else class="text-xs text-gray-300">—</span>
                        </div>
                    </td>
                    <td class="p-3 font-medium text-gray-900">{{ product.name }}</td>
                    <td class="p-3 text-gray-600">{{ product.category?.name }}</td>
                    <td class="p-3 text-gray-600">{{ product.brand?.name }}</td>
                    <td class="p-3 text-gray-600">{{ product.variants_count }}</td>
                    <td class="p-3">
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="product.active_variants_count > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                        >
                            {{ product.active_variants_count > 0 ? 'Đang hiển thị' : 'Đã ẩn/ngừng bán' }}
                        </span>
                    </td>
                    <td class="p-3 text-right">
                        <div class="flex justify-end gap-3">
                            <Link :href="`/admin/products/${product.id}/edit`" class="text-emerald-600 hover:underline">Sửa</Link>
                            <button type="button" class="text-red-600 hover:underline" @click="destroyProduct(product)">Xóa</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="products.links.length > 3" class="mt-4 flex flex-wrap gap-1">
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
    </AdminLayout>
</template>

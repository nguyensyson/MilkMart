<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import VariantRow from './Partials/VariantRow.vue';

const props = defineProps({
    product: {
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
});

const productForm = useForm({
    name: props.product.name,
    category_id: props.product.category_id,
    brand_id: props.product.brand_id,
    description: props.product.description ?? '',
});

function saveProduct() {
    productForm.put(`/admin/products/${props.product.id}`);
}

const variantForm = useForm({
    sku: '',
    weight: '',
    price: '',
    stock_quantity: 0,
    status: 'active',
});

function addVariant() {
    variantForm.post(`/admin/products/${props.product.id}/variants`, {
        preserveScroll: true,
        onSuccess: () => variantForm.reset(),
    });
}
</script>

<template>
    <AdminLayout title="Sửa sản phẩm">
        <div class="mb-4">
            <Link href="/admin/products" class="text-sm text-gray-500 hover:underline">← Quay lại danh sách sản phẩm</Link>
        </div>

        <div class="rounded-lg border bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Thông tin sản phẩm</h2>
            <form class="max-w-lg space-y-4" @submit.prevent="saveProduct">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tên sản phẩm</label>
                    <input
                        v-model="productForm.name"
                        type="text"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                    <p v-if="productForm.errors.name" class="mt-1 text-sm text-red-600">{{ productForm.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Danh mục</label>
                    <select
                        v-model="productForm.category_id"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    >
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <p v-if="productForm.errors.category_id" class="mt-1 text-sm text-red-600">{{ productForm.errors.category_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Thương hiệu</label>
                    <select
                        v-model="productForm.brand_id"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    >
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                    </select>
                    <p v-if="productForm.errors.brand_id" class="mt-1 text-sm text-red-600">{{ productForm.errors.brand_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Mô tả</label>
                    <textarea
                        v-model="productForm.description"
                        rows="4"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    ></textarea>
                </div>

                <button
                    type="submit"
                    :disabled="productForm.processing"
                    class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                >
                    Lưu thay đổi
                </button>
            </form>
        </div>

        <div class="mt-6 rounded-lg border bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Biến thể</h2>

            <div v-if="!product.variants?.length" class="mb-4 text-sm text-gray-400">Sản phẩm chưa có biến thể nào.</div>
            <table v-else class="mb-6 w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">SKU</th>
                        <th class="p-3">Khối lượng</th>
                        <th class="p-3">Giá</th>
                        <th class="p-3">Tồn kho</th>
                        <th class="p-3">Trạng thái</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <VariantRow v-for="variant in product.variants" :key="variant.id" :variant="variant" />
                </tbody>
            </table>

            <h3 class="mb-2 text-sm font-semibold text-gray-700">Thêm biến thể mới</h3>
            <form class="flex flex-wrap items-end gap-3" @submit.prevent="addVariant">
                <div>
                    <label class="block text-xs font-medium text-gray-700">SKU</label>
                    <input
                        v-model="variantForm.sku"
                        type="text"
                        class="mt-1 w-32 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                    <p v-if="variantForm.errors.sku" class="mt-1 text-xs text-red-600">{{ variantForm.errors.sku }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Khối lượng (g)</label>
                    <input
                        v-model="variantForm.weight"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 w-28 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                    <p v-if="variantForm.errors.weight" class="mt-1 text-xs text-red-600">{{ variantForm.errors.weight }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Giá</label>
                    <input
                        v-model="variantForm.price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 w-28 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                    <p v-if="variantForm.errors.price" class="mt-1 text-xs text-red-600">{{ variantForm.errors.price }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Tồn kho</label>
                    <input
                        v-model="variantForm.stock_quantity"
                        type="number"
                        min="0"
                        class="mt-1 w-20 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                    <p v-if="variantForm.errors.stock_quantity" class="mt-1 text-xs text-red-600">{{ variantForm.errors.stock_quantity }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Trạng thái</label>
                    <select
                        v-model="variantForm.status"
                        class="mt-1 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                    >
                        <option value="active">Đang bán</option>
                        <option value="inactive">Ngừng bán</option>
                    </select>
                </div>
                <button
                    type="submit"
                    :disabled="variantForm.processing"
                    class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                >
                    Thêm biến thể
                </button>
            </form>
        </div>
    </AdminLayout>
</template>

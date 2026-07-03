<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    category_id: '',
    brand_id: '',
    description: '',
});

function submit() {
    form.post('/admin/products');
}
</script>

<template>
    <AdminLayout title="Thêm sản phẩm">
        <form class="max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tên sản phẩm</label>
                <input
                    v-model="form.name"
                    type="text"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Danh mục</label>
                <select
                    v-model="form.category_id"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                >
                    <option value="" disabled>Chọn danh mục</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                </select>
                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Thương hiệu</label>
                <select
                    v-model="form.brand_id"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                >
                    <option value="" disabled>Chọn thương hiệu</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                </select>
                <p v-if="form.errors.brand_id" class="mt-1 text-sm text-red-600">{{ form.errors.brand_id }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Mô tả</label>
                <textarea
                    v-model="form.description"
                    rows="4"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                ></textarea>
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
            >
                Tạo sản phẩm
            </button>
        </form>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    brands: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search ?? '');
const editingId = ref(null);

const form = useForm({
    name: '',
    description: '',
    logo_url: '',
});

function applyFilters() {
    router.get('/admin/brands', { search: search.value || undefined }, { preserveState: true, replace: true });
}

function startCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
}

function startEdit(brand) {
    editingId.value = brand.id;
    form.name = brand.name;
    form.description = brand.description ?? '';
    form.logo_url = brand.logo_url ?? '';
    form.clearErrors();
}

function submit() {
    if (editingId.value) {
        form.put(`/admin/brands/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: startCreate,
        });
    } else {
        form.post('/admin/brands', {
            preserveScroll: true,
            onSuccess: startCreate,
        });
    }
}

function destroyBrand(brand) {
    if (!confirm(`Xóa thương hiệu "${brand.name}"?`)) {
        return;
    }

    router.delete(`/admin/brands/${brand.id}`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout title="Quản lý thương hiệu">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="mb-4 flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Tên thương hiệu"
                            class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <button
                        type="button"
                        class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                        @click="applyFilters"
                    >
                        Lọc
                    </button>
                </div>

                <div v-if="brands.data.length === 0" class="text-gray-500">Không tìm thấy thương hiệu nào.</div>
                <table v-else class="w-full rounded-lg border bg-white text-left text-sm">
                    <thead class="border-b bg-gray-50">
                        <tr>
                            <th class="p-3">Tên thương hiệu</th>
                            <th class="p-3">Mô tả</th>
                            <th class="p-3">Sản phẩm</th>
                            <th class="p-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="brand in brands.data" :key="brand.id" class="border-b last:border-0">
                            <td class="p-3 font-medium text-gray-900">{{ brand.name }}</td>
                            <td class="p-3 text-gray-600">{{ brand.description || '—' }}</td>
                            <td class="p-3 text-gray-600">{{ brand.products_count }}</td>
                            <td class="p-3 text-right">
                                <div class="flex justify-end gap-3">
                                    <button type="button" class="text-emerald-600 hover:underline" @click="startEdit(brand)">Sửa</button>
                                    <button type="button" class="text-red-600 hover:underline" @click="destroyBrand(brand)">Xóa</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="brands.links.length > 3" class="mt-4 flex flex-wrap gap-1">
                    <template v-for="link in brands.links" :key="link.label">
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
            </div>

            <div class="rounded-lg border bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ editingId ? 'Sửa thương hiệu' : 'Thêm thương hiệu' }}
                </h2>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tên thương hiệu</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mô tả</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Logo (URL)</label>
                        <input
                            v-model="form.logo_url"
                            type="text"
                            placeholder="https://..."
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.logo_url" class="mt-1 text-sm text-red-600">{{ form.errors.logo_url }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ editingId ? 'Lưu thay đổi' : 'Tạo thương hiệu' }}
                        </button>
                        <button v-if="editingId" type="button" class="text-sm text-gray-500 hover:underline" @click="startCreate">
                            Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

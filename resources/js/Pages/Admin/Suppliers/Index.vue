<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: {
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
    phone: '',
    email: '',
    address: '',
});

function applyFilters() {
    router.get('/admin/suppliers', { search: search.value || undefined }, { preserveState: true, replace: true });
}

function startCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
}

function startEdit(supplier) {
    editingId.value = supplier.id;
    form.name = supplier.name ?? '';
    form.phone = supplier.phone ?? '';
    form.email = supplier.email ?? '';
    form.address = supplier.address ?? '';
    form.clearErrors();
}

function submit() {
    if (editingId.value) {
        form.put(`/admin/suppliers/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: startCreate,
        });
    } else {
        form.post('/admin/suppliers', {
            preserveScroll: true,
            onSuccess: startCreate,
        });
    }
}

function destroySupplier(supplier) {
    if (!confirm(`Xóa nhà cung cấp "${supplier.name}"?`)) {
        return;
    }

    router.delete(`/admin/suppliers/${supplier.id}`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout title="Quản lý nhà cung cấp">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="mb-4 flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Tên nhà cung cấp"
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

                <div v-if="suppliers.data.length === 0" class="text-gray-500">Không tìm thấy nhà cung cấp nào.</div>
                <div v-else class="overflow-x-auto rounded-lg border bg-white">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50">
                            <tr>
                                <th class="p-3">Tên</th>
                                <th class="p-3">Điện thoại</th>
                                <th class="p-3">Email</th>
                                <th class="p-3">Địa chỉ</th>
                                <th class="p-3">Phiếu nhập</th>
                                <th class="p-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="supplier in suppliers.data" :key="supplier.id" class="border-b last:border-0">
                                <td class="p-3 font-medium text-gray-900">{{ supplier.name }}</td>
                                <td class="p-3 text-gray-600">{{ supplier.phone || '—' }}</td>
                                <td class="p-3 text-gray-600">{{ supplier.email || '—' }}</td>
                                <td class="p-3 text-gray-600">{{ supplier.address || '—' }}</td>
                                <td class="p-3 text-gray-600">{{ supplier.goods_receipts_count }}</td>
                                <td class="p-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="text-emerald-600 hover:underline" @click="startEdit(supplier)">
                                            Sửa
                                        </button>
                                        <button type="button" class="text-red-600 hover:underline" @click="destroySupplier(supplier)">
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="suppliers.links.length > 3" class="mt-4 flex flex-wrap gap-1">
                    <template v-for="link in suppliers.links" :key="link.label">
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

            <div class="h-fit rounded-lg border bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ editingId ? 'Sửa nhà cung cấp' : 'Thêm nhà cung cấp' }}
                </h2>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tên nhà cung cấp</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Điện thoại</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                        <textarea
                            v-model="form.address"
                            rows="3"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        ></textarea>
                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ editingId ? 'Lưu thay đổi' : 'Thêm nhà cung cấp' }}
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

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search ?? '');
const roleId = ref(props.filters.role_id ?? '');
const status = ref(props.filters.status ?? '');

function applyFilters() {
    router.get(
        '/admin/users',
        {
            search: search.value || undefined,
            role_id: roleId.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <AdminLayout title="Danh sách người dùng">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">Quản lý tài khoản khách hàng và nhân sự nội bộ.</p>
            <Link
                href="/admin/register"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
            >
                Tạo tài khoản nội bộ
            </Link>
        </div>

        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tên hoặc email"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @keyup.enter="applyFilters"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Vai trò</label>
                <select
                    v-model="roleId"
                    class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    @change="applyFilters"
                >
                    <option value="">Tất cả</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
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
                    <option value="active">Đang hoạt động</option>
                    <option value="locked">Đã khóa</option>
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

        <div v-if="users.data.length === 0" class="text-gray-500">Không tìm thấy người dùng nào.</div>
        <table v-else class="w-full rounded-lg border bg-white text-left text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="p-3">Họ tên</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Vai trò</th>
                    <th class="p-3">Trạng thái</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users.data" :key="user.id" class="border-b last:border-0">
                    <td class="p-3">{{ user.fullname || '—' }}</td>
                    <td class="p-3">{{ user.email }}</td>
                    <td class="p-3">{{ user.role?.name }}</td>
                    <td class="p-3">
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="user.status === 'locked' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'"
                        >
                            {{ user.status === 'locked' ? 'Đã khóa' : 'Đang hoạt động' }}
                        </span>
                    </td>
                    <td class="p-3 text-right">
                        <Link :href="`/admin/users/${user.id}`" class="text-emerald-600 hover:underline">Xem chi tiết</Link>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="users.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <template v-for="link in users.links" :key="link.label">
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

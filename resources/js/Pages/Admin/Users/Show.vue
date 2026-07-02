<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const isSelf = computed(() => page.props.auth.user?.id === props.user.id);

function toggleStatus() {
    const nextStatus = props.user.status === 'locked' ? 'active' : 'locked';
    const message =
        nextStatus === 'locked'
            ? `Khóa tài khoản "${props.user.fullname || props.user.email}"?`
            : `Mở khóa tài khoản "${props.user.fullname || props.user.email}"?`;

    if (!confirm(message)) {
        return;
    }

    router.put(`/admin/users/${props.user.id}/status`, { status: nextStatus });
}
</script>

<template>
    <AdminLayout title="Chi tiết người dùng">
        <div class="max-w-lg space-y-4 rounded-lg border bg-white p-6">
            <div class="flex items-center gap-4">
                <img
                    :src="user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.fullname || user.email)}`"
                    alt="Avatar"
                    class="h-16 w-16 rounded-full border object-cover"
                />
                <div>
                    <p class="font-semibold text-gray-900">{{ user.fullname || '—' }}</p>
                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                </div>
            </div>

            <dl class="grid grid-cols-3 gap-y-2 text-sm">
                <dt class="text-gray-500">Vai trò</dt>
                <dd class="col-span-2 text-gray-900">{{ user.role?.name }}</dd>

                <dt class="text-gray-500">Số điện thoại</dt>
                <dd class="col-span-2 text-gray-900">{{ user.phone || '—' }}</dd>

                <dt class="text-gray-500">Địa chỉ</dt>
                <dd class="col-span-2 text-gray-900">{{ user.address || '—' }}</dd>

                <dt class="text-gray-500">Trạng thái</dt>
                <dd class="col-span-2">
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="user.status === 'locked' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'"
                    >
                        {{ user.status === 'locked' ? 'Đã khóa' : 'Đang hoạt động' }}
                    </span>
                </dd>
            </dl>

            <div class="flex items-center gap-3 pt-2">
                <button
                    v-if="!isSelf"
                    type="button"
                    class="rounded px-4 py-2 text-sm font-medium text-white"
                    :class="user.status === 'locked' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'"
                    @click="toggleStatus"
                >
                    {{ user.status === 'locked' ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}
                </button>
                <p v-else class="text-sm text-gray-400">Đây là tài khoản của bạn.</p>
                <Link href="/admin/users" class="text-sm text-gray-500 hover:underline">Quay lại danh sách</Link>
            </div>
        </div>
    </AdminLayout>
</template>

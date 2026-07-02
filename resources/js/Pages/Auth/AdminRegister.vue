<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    fullname: '',
    email: '',
    phone: '',
    role_id: props.roles[0]?.id ?? null,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/admin/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AdminLayout title="Tạo tài khoản nội bộ">
        <form class="max-w-md space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium text-gray-700">Họ tên</label>
                <input
                    v-model="form.fullname"
                    type="text"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                />
                <p v-if="form.errors.fullname" class="mt-1 text-sm text-red-600">{{ form.errors.fullname }}</p>
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
                <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input
                    v-model="form.phone"
                    type="text"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                />
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Vai trò</label>
                <select
                    v-model="form.role_id"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                >
                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
                <p v-if="form.errors.role_id" class="mt-1 text-sm text-red-600">{{ form.errors.role_id }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <input
                    v-model="form.password"
                    type="password"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Xác nhận mật khẩu</label>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
            >
                Tạo tài khoản
            </button>
        </form>
    </AdminLayout>
</template>

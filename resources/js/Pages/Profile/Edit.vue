<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    fullname: props.user.fullname ?? '',
    phone: props.user.phone ?? '',
    address: props.user.address ?? '',
    avatar: props.user.avatar ?? '',
});

function submit() {
    form.put('/profile');
}
</script>

<template>
    <CustomerLayout title="Hồ sơ cá nhân">
        <form class="max-w-md space-y-4" @submit.prevent="submit">
            <div class="flex items-center gap-4">
                <img
                    :src="form.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.fullname || user.email)}`"
                    alt="Avatar"
                    class="h-16 w-16 rounded-full border object-cover"
                />
                <div class="text-sm text-gray-500">{{ user.email }}</div>
            </div>

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
                <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input
                    v-model="form.phone"
                    type="text"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                />
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                <textarea
                    v-model="form.address"
                    rows="2"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                ></textarea>
                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Ảnh đại diện (URL)</label>
                <input
                    v-model="form.avatar"
                    type="text"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                />
                <p v-if="form.errors.avatar" class="mt-1 text-sm text-red-600">{{ form.errors.avatar }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
            >
                Lưu thay đổi
            </button>
        </form>
    </CustomerLayout>
</template>

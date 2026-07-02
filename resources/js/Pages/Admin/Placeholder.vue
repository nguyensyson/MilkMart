<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <AdminLayout :title="title">
        <div class="rounded-lg border border-dashed bg-white p-10 text-center text-gray-400">
            Chức năng "{{ title }}" đang được phát triển.
        </div>
    </AdminLayout>
</template>

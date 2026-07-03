<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    variant: {
        type: Object,
        required: true,
    },
});

const fileInput = ref(null);
const form = useForm({ images: [] });

function onFilesSelected(event) {
    form.images = Array.from(event.target.files ?? []);
}

function upload() {
    if (form.images.length === 0) {
        return;
    }

    form.post(`/admin/variants/${props.variant.id}/images`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('images');
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
}

function setPrimary(image) {
    router.put(`/admin/images/${image.id}/primary`, {}, { preserveScroll: true });
}

function deleteImage(image) {
    if (!confirm('Xóa hình ảnh này?')) {
        return;
    }

    router.delete(`/admin/images/${image.id}`, { preserveScroll: true });
}
</script>

<template>
    <div class="rounded border border-dashed bg-gray-50 p-3">
        <div class="flex flex-wrap items-center gap-2">
            <input
                ref="fileInput"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                multiple
                class="text-xs"
                @change="onFilesSelected"
            />
            <button
                type="button"
                :disabled="form.processing || form.images.length === 0"
                class="rounded bg-emerald-600 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                @click="upload"
            >
                {{ form.processing ? 'Đang tải lên...' : 'Tải ảnh lên' }}
            </button>
            <p v-if="form.errors.images" class="text-xs text-red-600">{{ form.errors.images }}</p>
        </div>

        <p v-if="!variant.images?.length" class="mt-2 text-xs text-gray-400">Chưa có hình ảnh nào.</p>
        <div v-else class="mt-3 flex flex-wrap gap-3">
            <div
                v-for="image in variant.images"
                :key="image.id"
                class="w-24 rounded border bg-white p-1 text-center"
                :class="image.is_primary ? 'border-emerald-500' : 'border-gray-200'"
            >
                <div class="flex h-20 items-center justify-center overflow-hidden bg-gray-50">
                    <img :src="image.image_url" alt="" class="h-full w-full object-contain" />
                </div>
                <span
                    v-if="image.is_primary"
                    class="mt-1 block rounded-full bg-emerald-100 px-1 py-0.5 text-[10px] font-medium text-emerald-700"
                >
                    Đại diện
                </span>
                <button
                    v-else
                    type="button"
                    class="mt-1 block w-full text-[10px] text-emerald-600 hover:underline"
                    @click="setPrimary(image)"
                >
                    Đặt đại diện
                </button>
                <button type="button" class="block w-full text-[10px] text-red-600 hover:underline" @click="deleteImage(image)">
                    Xóa
                </button>
            </div>
        </div>
    </div>
</template>

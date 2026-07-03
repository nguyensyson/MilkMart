<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import VariantImages from './VariantImages.vue';

const props = defineProps({
    variant: {
        type: Object,
        required: true,
    },
});

const showImages = ref(false);

const form = useForm({
    price: props.variant.price,
    stock_quantity: props.variant.stock_quantity,
    status: props.variant.status ?? 'active',
});

function save() {
    form.put(`/admin/variants/${props.variant.id}`, { preserveScroll: true });
}

function destroyVariant() {
    if (!confirm(`Xóa biến thể "${props.variant.sku ?? '#' + props.variant.id}"?`)) {
        return;
    }

    router.delete(`/admin/variants/${props.variant.id}`, { preserveScroll: true });
}
</script>

<template>
    <tr class="border-b align-top last:border-0">
        <td class="p-3 text-gray-600">{{ variant.sku || '—' }}</td>
        <td class="p-3 text-gray-600">{{ variant.weight ? `${variant.weight} g` : '—' }}</td>
        <td class="p-3">
            <input
                v-model="form.price"
                type="number"
                min="0"
                step="0.01"
                class="w-28 rounded border border-gray-300 px-2 py-1 text-sm focus:border-emerald-500 focus:outline-none"
            />
            <p v-if="form.errors.price" class="mt-1 text-xs text-red-600">{{ form.errors.price }}</p>
        </td>
        <td class="p-3">
            <input
                v-model="form.stock_quantity"
                type="number"
                min="0"
                class="w-20 rounded border border-gray-300 px-2 py-1 text-sm focus:border-emerald-500 focus:outline-none"
            />
            <p v-if="form.errors.stock_quantity" class="mt-1 text-xs text-red-600">{{ form.errors.stock_quantity }}</p>
        </td>
        <td class="p-3">
            <select
                v-model="form.status"
                class="rounded border border-gray-300 px-2 py-1 text-sm focus:border-emerald-500 focus:outline-none"
            >
                <option value="active">Đang bán</option>
                <option value="inactive">Ngừng bán</option>
            </select>
        </td>
        <td class="p-3">
            <div class="flex flex-col gap-1">
                <button
                    type="button"
                    :disabled="form.processing"
                    class="rounded bg-emerald-600 px-2 py-1 text-xs font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                    @click="save"
                >
                    Lưu
                </button>
                <button type="button" class="text-xs text-gray-500 hover:underline" @click="showImages = !showImages">
                    {{ showImages ? 'Ẩn ảnh' : `Ảnh (${variant.images?.length ?? 0})` }}
                </button>
                <button type="button" class="text-xs text-red-600 hover:underline" @click="destroyVariant">Xóa</button>
            </div>
        </td>
    </tr>
    <tr v-if="showImages" class="border-b bg-gray-50/50 last:border-0">
        <td colspan="6" class="p-3">
            <VariantImages :variant="variant" />
        </td>
    </tr>
</template>

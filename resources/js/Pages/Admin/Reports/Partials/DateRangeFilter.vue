<script setup>
const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue', 'apply']);

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function toDateInput(date) {
    return date.toISOString().slice(0, 10);
}

function setPreset(days) {
    const to = new Date();
    const from = new Date();
    from.setDate(to.getDate() - (days - 1));
    emit('update:modelValue', { ...props.modelValue, from: toDateInput(from), to: toDateInput(to) });
    emit('apply');
}

function setThisMonth() {
    const now = new Date();
    const from = new Date(now.getFullYear(), now.getMonth(), 1);
    emit('update:modelValue', { ...props.modelValue, from: toDateInput(from), to: toDateInput(now) });
    emit('apply');
}
</script>

<template>
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700">Từ ngày</label>
            <input
                :value="modelValue.from"
                type="date"
                class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                @change="update('from', $event.target.value)"
            />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Đến ngày</label>
            <input
                :value="modelValue.to"
                type="date"
                class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                @change="update('to', $event.target.value)"
            />
        </div>
        <button
            type="button"
            class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
            @click="emit('apply')"
        >
            Lọc
        </button>
        <div class="flex gap-1">
            <button type="button" class="rounded border px-3 py-2 text-sm text-gray-600 hover:bg-gray-50" @click="setPreset(7)">
                7 ngày qua
            </button>
            <button type="button" class="rounded border px-3 py-2 text-sm text-gray-600 hover:bg-gray-50" @click="setPreset(30)">
                30 ngày qua
            </button>
            <button type="button" class="rounded border px-3 py-2 text-sm text-gray-600 hover:bg-gray-50" @click="setThisMonth">
                Tháng này
            </button>
        </div>
    </div>
</template>

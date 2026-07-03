<script setup>
import { computed } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    formatValue: {
        type: Function,
        default: (value) => value,
    },
});

const maxValue = computed(() => Math.max(1, ...props.items.map((item) => item.value)));
</script>

<template>
    <div v-if="items.length" class="flex items-end gap-2 overflow-x-auto pb-2">
        <div v-for="item in items" :key="item.label" class="flex min-w-[48px] flex-1 flex-col items-center gap-1">
            <span class="whitespace-nowrap text-xs font-medium text-gray-600">{{ formatValue(item.value) }}</span>
            <div
                class="w-full rounded-t bg-emerald-500"
                :style="{ height: `${Math.max(4, (item.value / maxValue) * 140)}px` }"
            ></div>
            <span class="whitespace-nowrap text-[11px] text-gray-400">{{ item.label }}</span>
        </div>
    </div>
    <div v-else class="py-10 text-center text-gray-400">Không có dữ liệu trong khoảng thời gian đã chọn.</div>
</template>

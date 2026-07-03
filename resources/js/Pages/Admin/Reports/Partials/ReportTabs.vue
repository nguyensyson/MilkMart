<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);

const tabs = [
    { label: 'Doanh thu', href: '/admin/reports/revenue' },
    { label: 'Tồn kho', href: '/admin/reports/inventory' },
    { label: 'Sản phẩm bán chạy', href: '/admin/reports/best-sellers' },
    { label: 'Hiệu quả voucher', href: '/admin/reports/vouchers' },
    { label: 'Giá trị nhập hàng', href: '/admin/reports/goods-receipts' },
];

function isActive(href) {
    return currentPath.value === href || (href === '/admin/reports/revenue' && currentPath.value === '/admin/reports');
}
</script>

<template>
    <div class="mb-6 flex flex-wrap gap-1 border-b">
        <Link
            v-for="tab in tabs"
            :key="tab.href"
            :href="tab.href"
            class="rounded-t px-4 py-2 text-sm font-medium"
            :class="isActive(tab.href) ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-gray-500 hover:text-gray-700'"
        >
            {{ tab.label }}
        </Link>
    </div>
</template>

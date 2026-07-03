<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);
const currentPath = computed(() => page.url.split('?')[0]);
const sidebarOpen = ref(false);

const menuItems = computed(() =>
    [
        { label: 'Dashboard', href: '/admin', adminOnly: false },
        { label: 'Quản lý người dùng', href: '/admin/users', adminOnly: true },
        { label: 'Quản lý sản phẩm', href: '/admin/products', adminOnly: true },
        { label: 'Quản lý danh mục', href: '/admin/categories', adminOnly: true },
        { label: 'Quản lý thương hiệu', href: '/admin/brands', adminOnly: true },
        { label: 'Quản lý đơn hàng', href: '/admin/orders', adminOnly: false },
        { label: 'Quản lý voucher', href: '/admin/vouchers', adminOnly: true },
        { label: 'Quản lý nhà cung cấp', href: '/admin/suppliers', adminOnly: false },
        { label: 'Quản lý nhập hàng', href: '/admin/goods-receipts', adminOnly: false },
        { label: 'Báo cáo & thống kê', href: '/admin/reports', adminOnly: true },
    ].filter((item) => !item.adminOnly || user.value?.role === 'Admin'),
);

function isActive(href) {
    return href === '/admin' ? currentPath.value === href : currentPath.value.startsWith(href);
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 md:flex">
        <div v-if="sidebarOpen" class="fixed inset-0 z-20 bg-black/30 md:hidden" @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 transform border-r bg-white transition-transform md:static md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="border-b px-4 py-4">
                <Link href="/admin" class="text-lg font-bold text-emerald-600">MilkMart Admin</Link>
            </div>
            <nav class="space-y-1 p-3 text-sm">
                <Link
                    v-for="item in menuItems"
                    :key="item.href"
                    :href="item.href"
                    class="block rounded px-3 py-2 font-medium"
                    :class="isActive(item.href) ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100'"
                    @click="sidebarOpen = false"
                >
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b bg-white px-4 py-4 md:px-6">
                <button type="button" class="text-xl text-gray-500 md:hidden" @click="sidebarOpen = true">☰</button>
                <span class="hidden text-sm text-gray-400 md:inline">Khu vực quản trị</span>
                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <Link href="/profile" class="hover:text-emerald-600">{{ user?.fullname || user?.email }}</Link>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">{{ user?.role }}</span>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="rounded bg-gray-100 px-3 py-1.5 text-gray-600 hover:bg-gray-200"
                    >
                        Đăng xuất
                    </Link>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 md:px-6">
                <div v-if="successMessage" class="mb-6 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                    {{ successMessage }}
                </div>
                <div v-if="errorMessage" class="mb-6 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ errorMessage }}
                </div>

                <h1 v-if="title" class="mb-6 text-2xl font-semibold text-gray-900">{{ title }}</h1>
                <slot />
            </main>
        </div>
    </div>
</template>

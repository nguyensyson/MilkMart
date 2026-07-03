<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    goodsReceipts: {
        type: Object,
        required: true,
    },
    suppliers: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const isStaff = computed(() => page.props.auth.user?.role === 'Staff');

const search = ref(props.filters.search ?? '');
const supplierId = ref(props.filters.supplier_id ?? '');
const fromDate = ref(props.filters.from_date ?? '');
const toDate = ref(props.filters.to_date ?? '');

function applyFilters() {
    router.get(
        '/admin/goods-receipts',
        {
            search: search.value || undefined,
            supplier_id: supplierId.value || undefined,
            from_date: fromDate.value || undefined,
            to_date: toDate.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('vi-VN');
}
</script>

<template>
    <AdminLayout title="Quản lý nhập hàng">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Mã phiếu nhập"
                        class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nhà cung cấp</label>
                    <select
                        v-model="supplierId"
                        class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        @change="applyFilters"
                    >
                        <option value="">Tất cả</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Từ ngày</label>
                    <input
                        v-model="fromDate"
                        type="date"
                        class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        @change="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Đến ngày</label>
                    <input
                        v-model="toDate"
                        type="date"
                        class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        @change="applyFilters"
                    />
                </div>
                <button
                    type="button"
                    class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                    @click="applyFilters"
                >
                    Lọc
                </button>
            </div>

            <Link
                v-if="isStaff"
                href="/admin/goods-receipts/create"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
            >
                + Tạo phiếu nhập
            </Link>
        </div>

        <div v-if="goodsReceipts.data.length === 0" class="text-gray-500">Không tìm thấy phiếu nhập hàng nào.</div>
        <table v-else class="w-full rounded-lg border bg-white text-left text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="p-3">Mã phiếu</th>
                    <th class="p-3">Nhà cung cấp</th>
                    <th class="p-3">Ngày nhập</th>
                    <th class="p-3">Tổng tiền</th>
                    <th class="p-3">Người tạo</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="receipt in goodsReceipts.data" :key="receipt.id" class="border-b last:border-0">
                    <td class="p-3 font-medium text-gray-900">{{ receipt.receipt_code }}</td>
                    <td class="p-3">{{ receipt.supplier?.name || '—' }}</td>
                    <td class="p-3">{{ formatDate(receipt.created_at) }}</td>
                    <td class="p-3">{{ formatCurrency(receipt.total_amount) }}</td>
                    <td class="p-3">{{ receipt.creator?.fullname || '—' }}</td>
                    <td class="p-3 text-right">
                        <Link :href="`/admin/goods-receipts/${receipt.id}`" class="text-emerald-600 hover:underline">Xem chi tiết</Link>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="goodsReceipts.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <template v-for="link in goodsReceipts.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-state
                    class="rounded border px-3 py-1 text-sm"
                    :class="link.active ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                    v-html="link.label"
                />
                <span v-else class="rounded border border-gray-200 px-3 py-1 text-sm text-gray-300" v-html="link.label" />
            </template>
        </div>
    </AdminLayout>
</template>

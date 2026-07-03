<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    goodsReceipt: {
        type: Object,
        required: true,
    },
});

function formatDateTime(value) {
    return new Date(value).toLocaleString('vi-VN');
}
</script>

<template>
    <AdminLayout :title="`Phiếu nhập ${goodsReceipt.receipt_code}`">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="overflow-x-auto rounded-lg border bg-white p-4">
                    <h2 class="mb-3 font-semibold text-gray-900">Sản phẩm đã nhập</h2>
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50">
                            <tr>
                                <th class="p-2">Sản phẩm</th>
                                <th class="p-2">Số lượng</th>
                                <th class="p-2">Đơn giá nhập</th>
                                <th class="p-2">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="detail in goodsReceipt.details" :key="detail.id" class="border-b last:border-0">
                                <td class="p-2">
                                    <p class="font-medium text-gray-900">{{ detail.variant?.product?.name ?? 'Sản phẩm đã ngừng bán' }}</p>
                                    <p class="text-gray-500">{{ detail.variant?.sku || (detail.variant?.weight ? `${detail.variant.weight}g` : '') }}</p>
                                </td>
                                <td class="p-2">{{ detail.quantity }}</td>
                                <td class="p-2">{{ formatCurrency(detail.import_price) }}</td>
                                <td class="p-2 font-medium text-gray-900">{{ formatCurrency(detail.total_price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-lg border bg-white p-4 text-sm">
                    <h2 class="mb-2 font-semibold text-gray-900">Nhà cung cấp</h2>
                    <p class="text-gray-900">{{ goodsReceipt.supplier?.name }}</p>
                    <p class="text-gray-500">{{ goodsReceipt.supplier?.phone }} · {{ goodsReceipt.supplier?.email }}</p>
                    <p v-if="goodsReceipt.supplier?.address" class="text-gray-500">{{ goodsReceipt.supplier.address }}</p>
                </div>
            </div>

            <div class="h-fit space-y-4 rounded-lg border bg-white p-6 text-sm">
                <div class="flex items-center justify-between border-b pb-3 text-base font-semibold text-gray-900">
                    <span>Tổng tiền</span>
                    <span>{{ formatCurrency(goodsReceipt.total_amount) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Ngày nhập</span>
                    <span>{{ formatDateTime(goodsReceipt.created_at) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Người tạo</span>
                    <span>{{ goodsReceipt.creator?.fullname }}</span>
                </div>
            </div>
        </div>

        <Link href="/admin/goods-receipts" class="mt-6 inline-block text-sm text-emerald-600 hover:underline">
            ← Quay lại danh sách phiếu nhập hàng
        </Link>
    </AdminLayout>
</template>

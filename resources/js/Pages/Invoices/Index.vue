<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    invoices: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <CustomerLayout title="Đơn hàng của tôi">
        <div v-if="invoices.length === 0" class="text-gray-500">
            Bạn chưa có đơn hàng nào.
        </div>
        <table v-else class="w-full rounded-lg border bg-white text-left text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="p-3">Mã đơn</th>
                    <th class="p-3">Trạng thái</th>
                    <th class="p-3">Tổng tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="invoice in invoices" :key="invoice.id" class="border-b last:border-0">
                    <td class="p-3">{{ invoice.invoice_code }}</td>
                    <td class="p-3">{{ invoice.order_status }}</td>
                    <td class="p-3">{{ invoice.total_amount }}</td>
                </tr>
            </tbody>
        </table>
    </CustomerLayout>
</template>

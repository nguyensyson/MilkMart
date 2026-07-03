<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    suppliers: {
        type: Array,
        default: () => [],
    },
    variants: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    supplier_id: '',
    items: [{ product_variant_id: '', quantity: 1, import_price: '' }],
});

function variantLabel(variant) {
    const variantName = variant.sku || (variant.weight ? `${variant.weight}g` : `#${variant.id}`);
    return `${variant.product?.name ?? 'Sản phẩm'} - ${variantName} (tồn: ${variant.stock_quantity})`;
}

function addItem() {
    form.items.push({ product_variant_id: '', quantity: 1, import_price: '' });
}

function removeItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function lineTotal(item) {
    const quantity = Number(item.quantity) || 0;
    const price = Number(item.import_price) || 0;
    return quantity * price;
}

const grandTotal = computed(() => form.items.reduce((sum, item) => sum + lineTotal(item), 0));

function submit() {
    form.post('/admin/goods-receipts');
}
</script>

<template>
    <AdminLayout title="Tạo phiếu nhập hàng">
        <form class="space-y-6" @submit.prevent="submit">
            <div class="rounded-lg border bg-white p-6">
                <label class="block text-sm font-medium text-gray-700">Nhà cung cấp</label>
                <select
                    v-model="form.supplier_id"
                    class="mt-1 w-full max-w-sm rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                >
                    <option value="" disabled>Chọn nhà cung cấp</option>
                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                </select>
                <p v-if="form.errors.supplier_id" class="mt-1 text-sm text-red-600">{{ form.errors.supplier_id }}</p>
            </div>

            <div class="overflow-x-auto rounded-lg border bg-white p-6">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Sản phẩm nhập</h2>
                    <button type="button" class="text-sm text-emerald-600 hover:underline" @click="addItem">+ Thêm dòng</button>
                </div>

                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-gray-50">
                        <tr>
                            <th class="p-2">Biến thể sản phẩm</th>
                            <th class="p-2">Số lượng</th>
                            <th class="p-2">Đơn giá nhập</th>
                            <th class="p-2">Thành tiền</th>
                            <th class="p-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.items" :key="index" class="border-b last:border-0">
                            <td class="p-2">
                                <select
                                    v-model="item.product_variant_id"
                                    class="w-64 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                                >
                                    <option value="" disabled>Chọn biến thể</option>
                                    <option v-for="variant in variants" :key="variant.id" :value="variant.id">
                                        {{ variantLabel(variant) }}
                                    </option>
                                </select>
                                <p v-if="form.errors[`items.${index}.product_variant_id`]" class="mt-1 text-sm text-red-600">
                                    {{ form.errors[`items.${index}.product_variant_id`] }}
                                </p>
                            </td>
                            <td class="p-2">
                                <input
                                    v-model="item.quantity"
                                    type="number"
                                    min="1"
                                    step="1"
                                    class="w-24 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                                />
                                <p v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                                    {{ form.errors[`items.${index}.quantity`] }}
                                </p>
                            </td>
                            <td class="p-2">
                                <input
                                    v-model="item.import_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-32 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                                />
                                <p v-if="form.errors[`items.${index}.import_price`]" class="mt-1 text-sm text-red-600">
                                    {{ form.errors[`items.${index}.import_price`] }}
                                </p>
                            </td>
                            <td class="p-2 font-medium text-gray-900">{{ formatCurrency(lineTotal(item)) }}</td>
                            <td class="p-2 text-right">
                                <button
                                    type="button"
                                    class="text-red-600 hover:underline disabled:cursor-not-allowed disabled:text-gray-300"
                                    :disabled="form.items.length === 1"
                                    @click="removeItem(index)"
                                >
                                    Xóa
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="form.errors.items" class="mt-2 text-sm text-red-600">{{ form.errors.items }}</p>

                <div class="mt-4 flex items-center justify-end gap-3 border-t pt-4 text-base font-semibold text-gray-900">
                    <span>Tổng tiền phiếu nhập</span>
                    <span>{{ formatCurrency(grandTotal) }}</span>
                </div>
            </div>

            <div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                >
                    Tạo phiếu nhập
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

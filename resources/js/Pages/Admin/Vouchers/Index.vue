<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { discountLabel, VOUCHER_STATUS_BADGE, voucherStatusKey, voucherStatusLabel } from '@/utils/voucherStatus';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    vouchers: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    discountTypes: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const editingId = ref(null);

const form = useForm({
    code: '',
    discount_type: 'percent',
    discount_value: '',
    max_discount: '',
    min_order_value: '',
    quantity: '',
    start_date: '',
    end_date: '',
    status: 'active',
});

function applyFilters() {
    router.get(
        '/admin/vouchers',
        { search: search.value || undefined, status: statusFilter.value || undefined },
        { preserveState: true, replace: true },
    );
}

function toInputDateTime(value) {
    if (!value) {
        return '';
    }
    const date = new Date(value);
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function startCreate() {
    editingId.value = null;
    form.reset();
    form.discount_type = 'percent';
    form.status = 'active';
    form.clearErrors();
}

function startEdit(voucher) {
    editingId.value = voucher.id;
    form.code = voucher.code;
    form.discount_type = voucher.discount_type;
    form.discount_value = voucher.discount_value;
    form.max_discount = voucher.max_discount ?? '';
    form.min_order_value = voucher.min_order_value ?? '';
    form.quantity = voucher.quantity ?? '';
    form.start_date = toInputDateTime(voucher.start_date);
    form.end_date = toInputDateTime(voucher.end_date);
    form.status = voucher.status;
    form.clearErrors();
}

function submit() {
    if (editingId.value) {
        form.put(`/admin/vouchers/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: startCreate,
        });
    } else {
        form.post('/admin/vouchers', {
            preserveScroll: true,
            onSuccess: startCreate,
        });
    }
}

function lockVoucher(voucher) {
    if (!confirm(`Khóa voucher "${voucher.code}"? Voucher sẽ không thể áp dụng cho đơn hàng mới.`)) {
        return;
    }

    router.delete(`/admin/vouchers/${voucher.id}`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout title="Quản lý voucher">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <div class="mb-4 flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Mã voucher"
                            class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                        <select
                            v-model="statusFilter"
                            class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                            @change="applyFilters"
                        >
                            <option value="">Tất cả</option>
                            <option value="active">Đang hoạt động</option>
                            <option value="expired">Hết hạn</option>
                            <option value="used_up">Hết lượt</option>
                            <option value="inactive">Đã khóa</option>
                        </select>
                    </div>
                    <button
                        type="button"
                        class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                        @click="applyFilters"
                    >
                        Lọc
                    </button>
                </div>

                <div v-if="vouchers.data.length === 0" class="text-gray-500">Không tìm thấy voucher nào.</div>
                <div v-else class="overflow-x-auto rounded-lg border bg-white">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50">
                            <tr>
                                <th class="p-3">Mã</th>
                                <th class="p-3">Giảm giá</th>
                                <th class="p-3">Hiệu lực</th>
                                <th class="p-3">Đã dùng</th>
                                <th class="p-3">Trạng thái</th>
                                <th class="p-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="voucher in vouchers.data" :key="voucher.id" class="border-b last:border-0">
                                <td class="p-3 font-medium text-gray-900">{{ voucher.code }}</td>
                                <td class="p-3 text-gray-600">
                                    <span v-if="discountLabel(voucher)">Giảm {{ discountLabel(voucher) }}</span>
                                    <span v-else>Giảm {{ formatCurrency(voucher.discount_value) }}</span>
                                    <div v-if="voucher.max_discount" class="text-xs text-gray-400">
                                        Tối đa {{ formatCurrency(voucher.max_discount) }}
                                    </div>
                                    <div v-if="voucher.min_order_value" class="text-xs text-gray-400">
                                        Đơn tối thiểu {{ formatCurrency(voucher.min_order_value) }}
                                    </div>
                                </td>
                                <td class="p-3 text-gray-600">
                                    <div>{{ new Date(voucher.start_date).toLocaleString('vi-VN') }}</div>
                                    <div>đến {{ new Date(voucher.end_date).toLocaleString('vi-VN') }}</div>
                                </td>
                                <td class="p-3 text-gray-600">{{ voucher.used_count }} / {{ voucher.quantity ?? '∞' }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="VOUCHER_STATUS_BADGE[voucherStatusKey(voucher)]"
                                    >
                                        {{ voucherStatusLabel(voucher) }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        <Link :href="`/admin/vouchers/${voucher.id}/usage`" class="text-gray-600 hover:underline">
                                            Lịch sử
                                        </Link>
                                        <button type="button" class="text-emerald-600 hover:underline" @click="startEdit(voucher)">
                                            Sửa
                                        </button>
                                        <button
                                            v-if="voucher.status === 'active'"
                                            type="button"
                                            class="text-red-600 hover:underline"
                                            @click="lockVoucher(voucher)"
                                        >
                                            Khóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="vouchers.links.length > 3" class="mt-4 flex flex-wrap gap-1">
                    <template v-for="link in vouchers.links" :key="link.label">
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
            </div>

            <div class="h-fit rounded-lg border bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ editingId ? 'Sửa voucher' : 'Thêm voucher' }}
                </h2>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mã voucher</label>
                        <input
                            v-model="form.code"
                            type="text"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none"
                            placeholder="VD: SALE10"
                        />
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loại giảm giá</label>
                            <select
                                v-model="form.discount_type"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                            >
                                <option value="percent">Theo %</option>
                                <option value="fixed">Số tiền cố định</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Giá trị {{ form.discount_type === 'percent' ? '(%)' : '(đ)' }}
                            </label>
                            <input
                                v-model="form.discount_value"
                                type="number"
                                min="0"
                                step="0.01"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                            />
                            <p v-if="form.errors.discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.discount_value }}</p>
                        </div>
                    </div>

                    <div v-if="form.discount_type === 'percent'">
                        <label class="block text-sm font-medium text-gray-700">Giảm tối đa (đ, để trống nếu không giới hạn)</label>
                        <input
                            v-model="form.max_discount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.max_discount" class="mt-1 text-sm text-red-600">{{ form.errors.max_discount }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Đơn tối thiểu (đ, để trống nếu không yêu cầu)</label>
                        <input
                            v-model="form.min_order_value"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.min_order_value" class="mt-1 text-sm text-red-600">{{ form.errors.min_order_value }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tổng lượt sử dụng (để trống nếu không giới hạn)</label>
                        <input
                            v-model="form.quantity"
                            type="number"
                            min="1"
                            step="1"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">{{ form.errors.quantity }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bắt đầu</label>
                            <input
                                v-model="form.start_date"
                                type="datetime-local"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                            />
                            <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kết thúc</label>
                            <input
                                v-model="form.end_date"
                                type="datetime-local"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                            />
                            <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                        <select
                            v-model="form.status"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Khóa</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ editingId ? 'Lưu thay đổi' : 'Tạo voucher' }}
                        </button>
                        <button v-if="editingId" type="button" class="text-sm text-gray-500 hover:underline" @click="startCreate">
                            Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

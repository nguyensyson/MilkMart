<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    cart: {
        type: Object,
        required: true,
    },
    shippingAddress: {
        type: String,
        default: '',
    },
});

const page = usePage();
const shippingAddress = ref(props.shippingAddress ?? '');
const voucherCode = ref('');
const submitting = ref(false);
const applyingVoucher = ref(false);
const appliedVoucher = ref(null);
const voucherError = ref('');

const displayTotal = computed(() => appliedVoucher.value?.total ?? props.cart.total);

function applyVoucher() {
    if (!voucherCode.value || applyingVoucher.value) {
        return;
    }

    applyingVoucher.value = true;
    voucherError.value = '';
    router.post(
        '/cart/apply-voucher',
        { voucher_code: voucherCode.value },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                appliedVoucher.value = page.props.flash?.voucher ?? null;
            },
            onError: (errors) => {
                appliedVoucher.value = null;
                voucherError.value = errors.voucher_code ?? 'Không thể áp dụng mã voucher.';
            },
            onFinish: () => {
                applyingVoucher.value = false;
            },
        },
    );
}

function removeVoucher() {
    appliedVoucher.value = null;
    voucherCode.value = '';
    voucherError.value = '';
}

function submit() {
    if (submitting.value || !shippingAddress.value) {
        return;
    }

    submitting.value = true;
    router.post(
        '/checkout',
        {
            shipping_address: shippingAddress.value,
            voucher_code: appliedVoucher.value?.code || voucherCode.value || undefined,
        },
        {
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
}
</script>

<template>
    <CustomerLayout title="Đặt hàng">
        <div v-if="!cart.items.length" class="rounded-lg border bg-white p-10 text-center">
            <p class="text-gray-500">Giỏ hàng của bạn đang trống.</p>
            <Link
                href="/products"
                class="mt-4 inline-block rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
            >
                Tiếp tục mua sắm
            </Link>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-lg border bg-white p-4">
                    <h2 class="mb-3 font-semibold text-gray-900">Sản phẩm</h2>
                    <ul class="divide-y">
                        <li v-for="item in cart.items" :key="item.id" class="flex items-center justify-between py-3 text-sm">
                            <div>
                                <p class="font-medium text-gray-900">{{ item.variant.product.name }}</p>
                                <p class="text-gray-500">SL: {{ item.quantity }} × {{ formatCurrency(item.unit_price) }}</p>
                            </div>
                            <span class="font-medium text-gray-900">{{ formatCurrency(item.subtotal) }}</span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-lg border bg-white p-4">
                    <label class="block text-sm font-medium text-gray-700">Địa chỉ giao hàng</label>
                    <textarea
                        v-model="shippingAddress"
                        rows="3"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        placeholder="Nhập địa chỉ nhận hàng"
                    ></textarea>
                    <p v-if="page.props.errors?.shipping_address" class="mt-1 text-sm text-red-600">
                        {{ page.props.errors.shipping_address }}
                    </p>
                </div>

                <div class="rounded-lg border bg-white p-4">
                    <label class="block text-sm font-medium text-gray-700">Mã voucher (nếu có)</label>

                    <div v-if="appliedVoucher" class="mt-1 flex items-center justify-between rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm">
                        <span class="text-emerald-700">
                            Đã áp dụng <strong>{{ appliedVoucher.code }}</strong> · giảm {{ formatCurrency(appliedVoucher.discount_amount) }}
                        </span>
                        <button type="button" class="text-emerald-700 underline hover:no-underline" @click="removeVoucher">Gỡ</button>
                    </div>
                    <div v-else class="mt-1 flex gap-2">
                        <input
                            v-model="voucherCode"
                            type="text"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none"
                            placeholder="VD: SALE10"
                            @keyup.enter="applyVoucher"
                        />
                        <button
                            type="button"
                            class="shrink-0 rounded bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 disabled:opacity-50"
                            :disabled="!voucherCode || applyingVoucher"
                            @click="applyVoucher"
                        >
                            {{ applyingVoucher ? 'Đang áp dụng...' : 'Áp dụng' }}
                        </button>
                    </div>
                    <p v-if="voucherError" class="mt-1 text-sm text-red-600">{{ voucherError }}</p>
                    <p v-else-if="page.props.errors?.voucher_code" class="mt-1 text-sm text-red-600">{{ page.props.errors.voucher_code }}</p>
                </div>

                <p v-if="page.props.errors?.cart" class="text-sm text-red-600">{{ page.props.errors.cart }}</p>
            </div>

            <div class="h-fit rounded-lg border bg-white p-6">
                <div class="space-y-1 text-sm">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Tạm tính</span>
                        <span>{{ formatCurrency(cart.total) }}</span>
                    </div>
                    <div v-if="appliedVoucher" class="flex items-center justify-between text-gray-600">
                        <span>Giảm giá ({{ appliedVoucher.code }})</span>
                        <span>-{{ formatCurrency(appliedVoucher.discount_amount) }}</span>
                    </div>
                </div>
                <div class="mt-2 flex items-center justify-between border-t pt-2 text-lg font-semibold text-gray-900">
                    <span>Tổng cộng</span>
                    <span>{{ formatCurrency(displayTotal) }}</span>
                </div>
                <p v-if="!appliedVoucher" class="mt-1 text-xs text-gray-400">Giảm giá voucher (nếu hợp lệ) sẽ được áp dụng khi đặt hàng.</p>
                <button
                    type="button"
                    class="mt-6 w-full rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                    :disabled="submitting || !shippingAddress"
                    @click="submit"
                >
                    {{ submitting ? 'Đang xử lý...' : 'Đặt hàng' }}
                </button>
            </div>
        </div>
    </CustomerLayout>
</template>

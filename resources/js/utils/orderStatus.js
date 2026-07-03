export const ORDER_STATUS_LABELS = {
    pending: 'Chờ xử lý',
    confirmed: 'Đã xác nhận',
    shipping: 'Đang giao',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy',
};

export const PAYMENT_STATUS_LABELS = {
    unpaid: 'Chưa thanh toán',
    paid: 'Đã thanh toán',
    refunded: 'Đã hoàn tiền',
};

export const ORDER_STATUS_BADGE = {
    pending: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-blue-100 text-blue-700',
    shipping: 'bg-indigo-100 text-indigo-700',
    completed: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-red-100 text-red-700',
};

export function orderStatusLabel(status) {
    return ORDER_STATUS_LABELS[status] ?? status;
}

export function paymentStatusLabel(status) {
    return PAYMENT_STATUS_LABELS[status] ?? status;
}

export const VOUCHER_STATUS_LABELS = {
    active: 'Đang hoạt động',
    upcoming: 'Chưa bắt đầu',
    expired: 'Hết hạn',
    used_up: 'Hết lượt',
    inactive: 'Đã khóa',
};

export const VOUCHER_STATUS_BADGE = {
    active: 'bg-emerald-100 text-emerald-700',
    upcoming: 'bg-blue-100 text-blue-700',
    expired: 'bg-gray-100 text-gray-600',
    used_up: 'bg-amber-100 text-amber-700',
    inactive: 'bg-red-100 text-red-700',
};

export function voucherStatusKey(voucher) {
    if (voucher.status !== 'active') {
        return 'inactive';
    }

    const now = new Date();
    if (voucher.end_date && now > new Date(voucher.end_date)) {
        return 'expired';
    }
    if (voucher.start_date && now < new Date(voucher.start_date)) {
        return 'upcoming';
    }
    if (voucher.quantity !== null && voucher.used_count >= voucher.quantity) {
        return 'used_up';
    }

    return 'active';
}

export function voucherStatusLabel(voucher) {
    return VOUCHER_STATUS_LABELS[voucherStatusKey(voucher)];
}

export function discountLabel(voucher) {
    return voucher.discount_type === 'percent' ? `${Number(voucher.discount_value)}%` : null;
}

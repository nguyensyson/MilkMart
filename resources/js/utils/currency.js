const formatter = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });

export function formatCurrency(value) {
    if (value === null || value === undefined) {
        return '—';
    }
    return formatter.format(value);
}

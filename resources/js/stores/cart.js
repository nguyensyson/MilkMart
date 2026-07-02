import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
    }),
    getters: {
        totalItems: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    },
    actions: {
        addItem(productVariantId, quantity = 1) {
            const existing = this.items.find((item) => item.productVariantId === productVariantId);
            if (existing) {
                existing.quantity += quantity;
                return;
            }
            this.items.push({ productVariantId, quantity });
        },
        removeItem(productVariantId) {
            this.items = this.items.filter((item) => item.productVariantId !== productVariantId);
        },
        clear() {
            this.items = [];
        },
    },
});

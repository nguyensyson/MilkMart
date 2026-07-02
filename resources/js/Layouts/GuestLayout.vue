<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <Link href="/" class="text-lg font-bold text-emerald-600">MilkMart</Link>
            </div>

            <div class="rounded-lg border bg-white p-6 shadow-sm">
                <h1 v-if="title" class="mb-6 text-xl font-semibold text-gray-900">{{ title }}</h1>

                <div v-if="successMessage" class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                    {{ successMessage }}
                </div>
                <div v-if="errorMessage" class="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ errorMessage }}
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>

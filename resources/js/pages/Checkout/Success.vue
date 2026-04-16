<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/money';

interface Order {
    id: number;
    created_at: string;
    tower: {
        name: string;
        city: { name: string };
        street: { name: string };
    };
}

const props = defineProps<{
    order: Order;
}>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-IL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Order Confirmed" />
    <AppLayout>
        <div class="max-w-2xl mx-auto py-12 px-4 text-center">
            <div class="mb-8">
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-slate-900 mb-2">Order Confirmed!</h1>
                <p class="text-slate-500">Thank you for shopping with Tower Power. Your order has been placed successfully.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 mb-8 text-left">
                <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-50 pb-4">Order Details</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Order Number</span>
                        <span class="font-bold text-slate-900">#{{ order.id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Date</span>
                        <span class="text-slate-900">{{ formatDate(order.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Delivery to</span>
                        <div class="text-right">
                            <p class="font-bold text-slate-900">{{ order.tower.name }}</p>
                            <p class="text-slate-500 text-xs">{{ order.tower.street.name }}, {{ order.tower.city.name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <Link 
                    href="/"
                    class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 transition"
                >
                    Continue Shopping
                </Link>
                <Link 
                    :href="route('user.dashboard', $page.props.auth.user.id)"
                    class="bg-slate-100 text-slate-700 px-8 py-3 rounded-lg font-bold hover:bg-slate-200 transition"
                >
                    View My Orders
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

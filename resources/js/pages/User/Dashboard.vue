<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/money';

interface Tower {
    id: number;
    name: string | null;
    house_number: string;
    zipcode: string;
    city: { name: string };
    street: { name: string };
    pivot: {
        apartment_number: string | null;
        floor: string | null;
    }
}

interface Order {
    id: number;
    total_price: number;
    status: string;
    created_at: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    towers: Tower[];
}

interface OrderPaginator {
    data: Order[];
    links: any[];
}

defineProps<{
    user: User;
    orders: OrderPaginator;
}>();
</script>

<template>
    <Head title="My Dashboard" />
    <AppLayout :breadcrumbs="[{ title: 'Home', href: '/' }, { title: 'Dashboard', href: '' }]">
        <div class="space-y-8">
            <h1 class="text-3xl font-bold text-slate-900">Account Dashboard</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Profile Section -->
                <div class="lg:col-span-1 space-y-6">
                    <section class="bg-white p-6 rounded-lg shadow-sm border border-slate-100">
                        <h2 class="text-xl font-bold text-slate-900 mb-4">Profile Info</h2>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-slate-500">Name</p>
                                <p class="font-medium text-slate-900">{{ user.name }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Email</p>
                                <p class="font-medium text-slate-900">{{ user.email }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="bg-blue-50 p-6 rounded-lg border border-blue-100">
                        <h2 class="text-lg font-bold text-blue-900 mb-4">Promotions</h2>
                        <div class="text-blue-700 text-sm">
                            <p>No active promotions.</p>
                        </div>
                    </section>
                </div>

                <!-- Towers and Orders -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- My Towers -->
                    <section>
                        <h2 class="text-xl font-bold text-slate-900 mb-4">My Residential Towers</h2>
                        <div v-if="user.towers.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div 
                                v-for="tower in user.towers" 
                                :key="tower.id"
                                class="bg-white p-4 rounded-lg shadow-sm border border-slate-100"
                            >
                                <p class="font-bold text-slate-900">{{ tower.name || 'Resident' }}</p>
                                <p class="text-sm text-slate-500">
                                    {{ tower.street.name }} {{ tower.house_number }}<br />
                                    {{ tower.city.name }}, {{ tower.zipcode }}
                                </p>
                                <p class="text-xs text-blue-600 font-medium mt-2 uppercase tracking-tighter">
                                    Apt {{ tower.pivot.apartment_number || '-' }} / Floor {{ tower.pivot.floor || '-' }}
                                </p>
                            </div>
                        </div>
                        <div v-else class="bg-slate-50 py-8 text-center rounded-lg border border-slate-100 text-slate-500">
                            You are not registered in any towers.
                        </div>
                    </section>

                    <!-- Order History -->
                    <section>
                        <h2 class="text-xl font-bold text-slate-900 mb-4">Order History</h2>
                        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3">Order ID</th>
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3 text-right">Total</th>
                                        <th class="px-6 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="order in orders.data" :key="order.id" class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 font-medium text-slate-900">#{{ order.id }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ new Date(order.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ formatPrice(order.total_price) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span 
                                                class="px-2 py-1 rounded text-[10px] font-bold uppercase"
                                                :class="{
                                                    'bg-blue-100 text-blue-700': order.status === 'pending',
                                                    'bg-green-100 text-green-700': order.status === 'completed',
                                                    'bg-red-100 text-red-700': order.status === 'cancelled'
                                                }"
                                            >
                                                {{ order.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="orders.data.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">No orders found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

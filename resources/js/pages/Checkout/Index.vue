<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/money';
import { computed } from 'vue';

interface Product {
    id: number;
    name: string;
    price: number;
    image_path: string | null;
}

interface CartItem {
    id: number;
    quantity: number | null;
    weight: number | null;
    volume: number | null;
    price_type: 'Unit' | 'Weight' | 'Volume';
    product: Product;
}

interface Tower {
    id: number;
    name: string;
    city: {
        name: string;
    };
    street: {
        name: string;
    };
}

interface Cart {
    id: number;
    tower_id: number;
}

const props = defineProps<{
    cart: Cart;
    items: CartItem[];
    tower: Tower | null;
}>();

const form = useForm({});

const total = computed(() => {
    return props.items.reduce((acc, item) => {
        let val: number | string | null = 0;
        if (item.price_type === 'Unit') val = item.quantity;
        else if (item.price_type === 'Weight') val = item.weight;
        else if (item.price_type === 'Volume') val = item.volume;

        const numVal = typeof val === 'string' ? parseFloat(val) : (val ?? 0);
        return acc + (item.product.price * numVal);
    }, 0);
});

const getItemMultiplier = (item: CartItem) => {
    let val: number | string | null = 0;
    if (item.price_type === 'Unit') val = item.quantity;
    else if (item.price_type === 'Weight') val = item.weight;
    else if (item.price_type === 'Volume') val = item.volume;
    
    return typeof val === 'string' ? parseFloat(val) : (val ?? 0);
};

const submit = () => {
    form.post(route('checkout.store'));
};
</script>

<template>
    <Head title="Checkout" />
    <AppLayout :breadcrumbs="[{ title: 'Home', href: '/' }, { title: 'Cart', href: route('cart.index') }, { title: 'Checkout', href: '' }]">
        <div class="max-w-4xl mx-auto space-y-8">
            <h1 class="text-3xl font-bold text-slate-900">Checkout</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Delivery Address Section -->
                    <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                        <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm">1</span>
                            Delivery Information
                        </h2>
                        <div class="pl-10">
                            <div v-if="tower" class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <p class="font-bold text-slate-900">{{ tower.name }}</p>
                                <p class="text-slate-600 text-sm">{{ tower.street?.name }}, {{ tower.city?.name }}</p>
                                <p class="text-slate-500 text-xs mt-2 italic">Products will be delivered to your building's reception/lobby.</p>
                            </div>
                            <div v-else class="p-4 bg-red-50 text-red-600 rounded-lg border border-red-100 text-sm">
                                No tower selected for delivery.
                            </div>
                        </div>
                    </section>

                    <!-- Order Items Section -->
                    <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                        <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm">2</span>
                            Review Items
                        </h2>
                        <div class="pl-10 space-y-4">
                            <div 
                                v-for="item in items" 
                                :key="item.id"
                                class="flex items-center gap-4 py-3 border-b border-slate-50 last:border-0"
                            >
                                <div class="w-12 h-12 bg-slate-100 rounded overflow-hidden flex-shrink-0">
                                    <img v-if="item.product.image_path" :src="`/storage/${item.product.image_path}`" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-slate-800 text-sm">{{ item.product.name }}</h4>
                                    <p class="text-slate-500 text-xs">
                                        {{ getItemMultiplier(item) }} × {{ formatPrice(item.product.price) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-slate-900 text-sm">
                                        {{ formatPrice(item.product.price * getItemMultiplier(item)) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 sticky top-24">
                        <h2 class="text-xl font-bold text-slate-900 mb-6">Payment Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span>{{ formatPrice(total) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Delivery Fee</span>
                                <span class="text-green-600">Free</span>
                            </div>
                            <div class="pt-3 border-t border-slate-100 flex justify-between font-bold text-lg text-slate-900">
                                <span>Total</span>
                                <span>{{ formatPrice(total) }}</span>
                            </div>
                        </div>

                        <button 
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Processing...' : 'Place Order' }}
                        </button>
                        
                        <p class="text-center text-slate-400 text-[10px] mt-4">
                            By placing your order, you agree to Tower Power's terms and conditions.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

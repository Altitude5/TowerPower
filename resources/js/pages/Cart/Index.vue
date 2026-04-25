<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/money';
import { computed } from 'vue';

interface CartItem {
    id: number;
    quantity: number | null;
    weight: number | null;
    volume: number | null;
    price_type: 'Unit' | 'Weight' | 'Volume';
    category_name: string | null;
    shop_name: string | null;
    product: {
        id: number;
        name: string;
        price: number;
        image_path: string | null;
    }
}

interface GroupedItems {
    shop_id: number;
    shop_name: string;
    category_name: string;
    expected_delivery_date: string | null;
    expected_delivery_day: string | null;
    items: CartItem[];
}

interface Cart {
    id: number;
    user_id: number;
}

const props = defineProps<{
    cart: Cart;
    items: CartItem[];
    groupedItems: GroupedItems[];
}>();

const total = computed(() => {
    return props.items.reduce((acc, item) => {
        let val: number | string | null = 0;
        if (item.price_type === 'Unit') val = item.quantity;
        else if (item.price_type === 'Weight') val = item.weight;
        else if (item.price_type === 'Volume') val = item.volume;

        const numVal = typeof val === 'string' ? parseFloat(val) : (val ?? 0);
        const price = item.product?.price ?? 0;
        return acc + (price * numVal);
    }, 0);
});

const getItemMultiplier = (item: CartItem) => {
    let val: number | string | null = 0;
    if (item.price_type === 'Unit') val = item.quantity;
    else if (item.price_type === 'Weight') val = item.weight;
    else if (item.price_type === 'Volume') val = item.volume;
    
    return typeof val === 'string' ? parseFloat(val) : (val ?? 0);
};

const checkout = () => {
    router.visit(route('checkout.index'));
};
</script>

<template>
    <Head title="My Cart" />
    <AppLayout :breadcrumbs="[{ title: 'Home', href: '/' }, { title: 'My Cart', href: '' }]">
        <div class="space-y-6">
            <h1 class="text-3xl font-bold text-slate-900">My Cart</h1>

            <div v-if="items.length > 0" class="flex flex-col lg:flex-row gap-8">
                <!-- Items Table -->
                <div class="flex-1 space-y-8">
                    <div v-for="group in groupedItems" :key="`${group.shop_id}-${group.category_name}`" class="space-y-4">
                        <h2 class="text-xl font-bold text-slate-800 border-b border-slate-200 pb-2">
                            {{ group.category_name }}
                        </h2>
                        
                        <div class="space-y-4">
                            <div 
                                v-for="item in group.items" 
                                :key="item.id"
                                class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 flex items-center gap-4 group"
                            >
                                <div class="w-20 h-20 bg-slate-50 rounded-md overflow-hidden border border-slate-100 flex-shrink-0">
                                    <img v-if="item.product.image_path" :src="`/storage/${item.product.image_path}`" class="w-full h-full object-cover" />
                                    <div v-else class="flex items-center justify-center h-full text-[10px] text-slate-400 font-bold uppercase">No Image</div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-slate-800">{{ item.product.name }}</h3>
                                    <p class="text-slate-500 text-sm">
                                        {{ item.price_type }}: 
                                        {{ getItemMultiplier(item) }} 
                                        × {{ formatPrice(item.product.price) }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 uppercase font-medium mt-1">
                                        Sold by: {{ group.shop_name }}
                                        <span v-if="group.expected_delivery_day" class="ml-2 text-blue-600">
                                            • Expected Delivery: {{ group.expected_delivery_day }}
                                        </span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-slate-900">
                                        {{ formatPrice(item.product.price * getItemMultiplier(item)) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-96">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-100 sticky top-24">
                        <h2 class="text-xl font-bold text-slate-900 mb-6">Order Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span>{{ formatPrice(total) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Delivery</span>
                                <span class="text-green-600 font-medium">Free</span>
                            </div>
                            <div class="pt-3 border-t border-slate-100 flex justify-between font-bold text-lg text-slate-900">
                                <span>Total</span>
                                <span>{{ formatPrice(total) }}</span>
                            </div>
                        </div>

                        <button 
                            @click="checkout"
                            class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold hover:bg-blue-700 transition"
                        >
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white py-16 text-center rounded-lg border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-lg">Your cart is currently empty.</p>
                <Link href="/" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-md inline-block hover:bg-blue-700 transition">Browse Products</Link>
            </div>
        </div>
    </AppLayout>
</template>

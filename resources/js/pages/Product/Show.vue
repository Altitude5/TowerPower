<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/money';
import { route } from 'ziggy-js';

const routeFn = route;

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    price_type: string;
    image_path: string | null;
    sku: string | null;
    shop: { name: string };
}

const props = defineProps<{
    category: Category;
    product: Product;
    showAddToCart: boolean;
}>();

// Simple Add to Cart Form (Placeholder for later implementation)
const form = useForm({
    product_id: props.product.id,
    quantity: 1,
});

const addToCart = () => {
    form.post(`/cart/add/${form.product_id}`, {
        onSuccess: () => {
            alert('Product added to cart!');
            form.reset();
        },
        onError: () => {
            alert('There was an error adding the product to your cart.');
        }
    });
};
</script>

<template>
    <Head :title="product.name" />
    <AppLayout 
    
    :breadcrumbs="[]
    ">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 bg-white p-8 rounded-xl shadow-sm border border-slate-100">
            <!-- Image Section -->
            <div class="aspect-square bg-slate-50 rounded-lg overflow-hidden border border-slate-200">
                <img v-if="product.image_path" :src="`/storage/${product.image_path}`" class="object-cover w-full h-full" />
                <div v-else class="flex items-center justify-center h-full text-slate-400 font-bold uppercase">No Product Image</div>
            </div>

            <!-- Details Section -->
            <div class="flex flex-col space-y-6">
                <div>
                    <p class="text-blue-600 font-medium mb-1 uppercase tracking-wider text-xs">{{ category.name }}</p>
                    <h1 class="text-4xl font-bold text-slate-900">{{ product.name }}</h1>
                    <p class="text-slate-500 mt-2">Sold by <span class="text-slate-900 font-medium">{{ product.shop.name }}</span></p>
                </div>

                <div class="py-6 border-y border-slate-100 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-blue-700">{{ formatPrice(product.price) }}</span>
                    <span class="text-slate-500 text-sm">/ {{ product.price_type }}</span>
                </div>

                <div v-if="showAddToCart" class="space-y-4">
                    <div class="flex items-center gap-4">
                        <label for="quantity" class="text-sm font-medium text-slate-700">Quantity</label>
                        <input 
                            v-model="form.quantity"
                            type="number" 
                            id="quantity" 
                            min="1" 
                            class="w-20 rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <button 
                        @click="addToCart"
                        class="w-full bg-blue-600 text-white px-8 py-4 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200"
                    >
                        Add to Cart
                    </button>
                </div>
                <div v-else class="bg-red-50 text-red-700 p-4 rounded-lg border border-red-100 font-medium">
                    Out of Stock
                </div>

                <div class="text-sm text-slate-500 pt-6">
                    <p v-if="product.sku">SKU: {{ product.sku }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

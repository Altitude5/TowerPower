<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/money';
import AddToCartButton from '@/components/AddToCartButton.vue';

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
                    <AddToCartButton :product-id="product.id" :price-type="product.price_type as 'Unit' | 'Weight' | 'Volume'" />
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

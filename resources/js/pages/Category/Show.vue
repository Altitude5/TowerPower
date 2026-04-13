<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/money';
import { route } from 'ziggy-js';

const routeFn = route;

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    image_path: string | null;
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Paginator {
    data: Product[];
    links: any[];
    current_page: number;
    last_page: number;
}

defineProps<{
    category: Category;
    products: Paginator;
}>();
</script>

<template>
    <Head :title="category.name" />
    <AppLayout :breadcrumbs="[{ title: 'Home', href: '/' }, { title: category.name, href: '' }]">
        <div class="space-y-6">
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-3xl font-bold text-slate-900">{{ category.name }}</h1>
                <div class="text-sm text-slate-500">
                    Showing {{ products.data.length }} products
                </div>
            </header>

            <!-- Product Grid -->
            <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div 
                    v-for="product in products.data" 
                    :key="product.id"
                    class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden group"
                >
                    <Link :href="routeFn('product.show', { category: category.slug, product: product.slug })">
                        <div class="aspect-square bg-slate-100 relative">
                            <img v-if="product.image_path" :src="`/storage/${product.image_path}`" class="object-cover w-full h-full" />
                            <div v-else class="flex items-center justify-center h-full text-slate-400 uppercase font-bold text-xs">No Image</div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-slate-800 group-hover:text-blue-600 transition">{{ product.name }}</h3>
                            <p class="text-blue-700 font-bold mt-1">{{ formatPrice(product.price) }}</p>
                        </div>
                    </Link>
                </div>
            </div>

            <div v-else class="bg-slate-50 py-12 text-center rounded-lg border border-slate-100">
                <p class="text-slate-500">No products found in this category.</p>
                <Link href="/" class="text-blue-600 mt-4 inline-block hover:underline">Return to Home</Link>
            </div>

            <!-- Simple Pagination (Placeholder) -->
            <div v-if="products.last_page > 1" class="flex justify-center mt-8 gap-2">
                <Link 
                    v-for="link in products.links" 
                    :key="link.label"
                    :href="link.url"
                    v-html="link.label"
                    class="px-4 py-2 rounded-md border text-sm"
                    :class="[link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/money';
import AddToCartButton from '@/components/AddToCartButton.vue';

// Use window.route if available, or a fallback object to avoid TypeError
const getRoute = (name: string, params: Record<string, any>) => {
    return (window as any).route ? (window as any).route(name, params) : '#';
};

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    price_type: 'Unit' | 'Weight' | 'Volume';
    image_path: string | null;
    category: Category | null;
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Roles {
    isCustomer: boolean;
    isSeller: boolean;
    isDeliveryPerson: boolean;
    isStaff: boolean;
    isSuperUser: boolean;
}

defineProps<{
    roles: Roles;
    categories: Category[];
    featuredProducts: Product[];
}>();
</script>

<template>
    <Head title="Home" />
    <AppLayout>
        <div class="space-y-8">
            <!-- Welcome Section -->
            <section class="bg-white p-6 rounded-lg shadow-sm border border-slate-100">
                <h1 class="text-2xl font-bold text-slate-900">Welcome to Tower Power</h1>
                <p class="text-slate-600 mt-2">Your neighborhood social marketplace.</p>
                
                <div class="mt-4 flex flex-wrap gap-2">
                    <span v-if="roles.isSuperUser" class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium">Super User</span>
                    <span v-if="roles.isStaff" class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-medium">Staff</span>
                    <span v-if="roles.isSeller" class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">Seller</span>
                    <span v-if="roles.isDeliveryPerson" class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-medium">Delivery</span>
                </div>
            </section>

            <!-- Role Specific Sections (Placeholders) -->
            <section v-if="roles.isSeller" class="bg-green-50 p-6 rounded-lg border border-green-100">
                <h2 class="text-lg font-semibold text-green-900">Seller Hub</h2>
                <div class="mt-3 flex gap-4">
                    <Link href="#" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">My Shop</Link>
                    <Link href="#" class="bg-white text-green-700 border border-green-200 px-4 py-2 rounded-md hover:bg-green-50 transition">Active Orders</Link>
                </div>
            </section>

            <section v-if="roles.isDeliveryPerson" class="bg-purple-50 p-6 rounded-lg border border-purple-100">
                <h2 class="text-lg font-semibold text-purple-900">Delivery Dashboard</h2>
                <div class="mt-3">
                    <Link href="#" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition">My Deliveries</Link>
                </div>
            </section>

            <!-- Categories Area -->
            <section v-if="categories && categories.length > 0">
                <h2 class="text-xl font-bold text-slate-900 mb-4">Categories</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <Link 
                        v-for="category in categories" 
                        :key="category.id"
                        :href="getRoute('category.show', { category: category.slug })"
                        class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 hover:border-blue-400 transition text-center"
                    >
                        <span class="font-medium text-slate-800">{{ category.name }}</span>
                    </Link>
                </div>
            </section>

            <!-- Featured Products -->
            <section v-if="featuredProducts">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-slate-900">Featured Products</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="product in featuredProducts" 
                        :key="product.id"
                        class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden group"
                    >
                        <!-- <Link :href="getRoute('product.show', { category: categories && categories.length > 0 ? categories[0].slug : 'misc', product: product.slug })">
                            <div class="aspect-square bg-slate-100 relative">
                                <img v-if="product.image_path" :src="`/${product.image_path}`" class="object-cover w-full h-full" />
                                <div v-else class="flex items-center justify-center h-full text-slate-400 uppercase font-bold text-xs">No Image</div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-slate-800 group-hover:text-blue-600 transition">{{ product.name }}</h3>
                                <p class="text-blue-700 font-bold mt-1">{{ formatPrice(product.price) }}</p>

                                <div class="mt-4">
                                    <AddToCartButton :product-id="product.id" :price-type="product.price_type" />
                                </div>
                            </div>
                        </Link> -->
                         <Link 
                         
                         :href="`/category/${product.category?.slug}/${product.slug}`">
                            <div class="aspect-square bg-slate-100 relative">
                                <img v-if="product.image_path" :src="`/${product.image_path}`" class="object-cover w-full h-full" />
                                <div v-else class="flex items-center justify-center h-full text-slate-400 uppercase font-bold text-xs">No Image</div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-slate-800 group-hover:text-blue-600 transition">{{ product.name }}</h3>
                                <p class="text-blue-700 font-bold mt-1">{{ formatPrice(product.price) }}</p>


                            </div>
                        </Link>
                                                        <div class="mt-4">
                                    <AddToCartButton :product-id="product.id" :price-type="product.price_type" />
                                </div>
               
                        <!-- <Link :href="getRoute('product.show', { category: categories && categories.length > 0 ? categories[0].slug : 'misc', product: product.slug })">
                            <div class="aspect-square bg-slate-100 relative">
                                <img v-if="product.image_path" :src="`/${product.image_path}`" class="object-cover w-full h-full" />
                                <div v-else class="flex items-center justify-center h-full text-slate-400 uppercase font-bold text-xs">No Image</div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-slate-800 group-hover:text-blue-600 transition">{{ product.name }}</h3>
                                <p class="text-blue-700 font-bold mt-1">{{ formatPrice(product.price) }}</p>

                                <div class="mt-4">
                                    <AddToCartButton :product-id="product.id" :price-type="product.price_type" />
                                </div>
                            </div>
                        </Link> -->
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

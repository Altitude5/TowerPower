<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import QuantityButton from './QuantityButton.vue';

const props = defineProps<{
    productId: number;
    priceType: 'Unit' | 'Weight' | 'Volume';
}>();

const page = usePage();
const quantityInCart = computed(() => {
    const items = (page.props as any).cart?.items || {};
    
    const val = items[props.productId];
    console.log(props.priceType);
    return val ? parseFloat(val) : 0;
});

const form = useForm({
    product_id: props.productId,
    quantity: null,
    weight: null,
    volume: null,
});

const addToCart = () => {
    const key = props.priceType.toLowerCase() as 'quantity' | 'weight' | 'volume';
    const initialValue = props.priceType === 'Unit' ? 1 : 0.5;

    form.transform((data) => ({
        ...data,
        [key]: initialValue,
    })).post(route('cart.store', { product: props.productId }), {
        preserveScroll: true,
    });
};
</script>

<template>

    <div v-if="quantityInCart > 0">
        <QuantityButton 
            :product-id="productId" 
            :quantity="quantityInCart" 
            :price-type="priceType" 
        />
    </div>
    <div v-else class="space-y-4">
        <button 
            @click="addToCart"
            :disabled="form.processing"
            class="w-full bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <span v-if="form.processing">Adding...</span>
            <span v-else>Add to Cart</span>
        </button>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    productId: number;
    quantity: number | string;
    priceType: 'Unit' | 'Weight' | 'Volume';
}>();

const form = useForm({
    product_id: props.productId,
    quantity: 0,
    weight: 0,
    volume: 0,
});

const incrementValue = computed(() => {
    switch (props.priceType) {
        case 'Unit': return 1;
        case 'Weight': return 0.25;
        case 'Volume': return 0.5; // Following user's specific instruction
        default: return 1;
    }
});

const formattedQuantity = computed(() => {
    const val = typeof props.quantity === 'string' ? parseFloat(props.quantity) : props.quantity;
    if (props.priceType === 'Unit') return val.toString();
    return val.toFixed(2);
});

const updateQuantity = (diff: number) => {
    const keyMap = {
        'Unit': 'quantity',
        'Weight': 'weight',
        'Volume': 'volume'
    } as const;
    const key = keyMap[props.priceType];
    const currentVal = typeof props.quantity === 'string' ? parseFloat(props.quantity) : props.quantity;
    const newVal = currentVal + diff;

    // Send the calculated new value directly as 'absolute' to the server.
    form.transform(() => ({
        absolute: true,
        [key]: newVal,
    })).post(route('cart.store', { product: props.productId }), {
        preserveScroll: true,
    });
};

const increment = () => updateQuantity(incrementValue.value);
const decrement = () => updateQuantity(-incrementValue.value);

</script>

<template>
    <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm h-12">
        <button 
            @click="decrement"
            class="w-12 h-full flex items-center justify-center hover:bg-slate-50 text-slate-600 transition border-r border-slate-100"
            :disabled="form.processing"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
            </svg>
        </button>
        
        <div class="flex-1 px-4 text-center font-bold text-slate-900 min-w-[3rem]">
            {{ formattedQuantity }}
            <span v-if="priceType !== 'Unit'" class="text-xs font-normal text-slate-500 ml-0.5">
                {{ priceType === 'Weight' ? 'kg' : 'L' }}
            </span>
        </div>

        <button 
            @click="increment"
            class="w-12 h-full flex items-center justify-center hover:bg-slate-50 text-slate-600 transition border-l border-slate-100"
            :disabled="form.processing"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>
    </div>
</template>

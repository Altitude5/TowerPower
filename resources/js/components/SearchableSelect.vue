<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    modelValue: string | number;
    options: Array<{ id: number; name: string; house_number?: string }>;
    placeholder?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const search = ref('');
const filteredOptions = computed(() => {
    return props.options.filter((option) => {
        const name = option.name.toLowerCase();
        const houseNumber = option.house_number?.toLowerCase() || '';
        const searchTerm = search.value.toLowerCase();
        return name.includes(searchTerm) || houseNumber.includes(searchTerm);
    });
});

const internalValue = computed({
    get: () => props.modelValue?.toString(),
    set: (val) => emit('update:modelValue', val),
});

// Reset search when options change
watch(() => props.options, () => {
    search.value = '';
});
</script>

<template>
    <Select v-model="internalValue" :disabled="disabled">
        <SelectTrigger class="w-full">
            <SelectValue :placeholder="placeholder" />
        </SelectTrigger>
        <SelectContent>
            <div class="p-2">
                <Input
                    v-model="search"
                    placeholder="Search..."
                    class="h-8"
                    @click.stop
                    @keydown.stop
                />
            </div>
            <div class="max-h-[200px] overflow-y-auto">
                <SelectItem
                    v-for="option in filteredOptions"
                    :key="option.id"
                    :value="option.id.toString()"
                >
                    {{ option.name }} {{ option.house_number ? `(${option.house_number})` : '' }}
                </SelectItem>
                <div v-if="filteredOptions.length === 0" class="p-2 text-center text-sm text-muted-foreground">
                    No results found.
                </div>
            </div>
        </SelectContent>
    </Select>
</template>

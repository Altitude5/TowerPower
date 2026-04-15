<script setup lang="ts">
import { Form, Head, useFormContext } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { ref, watch, computed } from 'vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import axios from 'axios';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

const props = defineProps<{
    cities: Array<{ id: number; name: string }>;
}>();

const selectedCityId = ref<string | number>('');
const selectedStreetId = ref<string | number>('');
const houseNumber = ref('');
const selectedTowerId = ref<string | number>('');

const streets = ref<Array<{ id: number; name: string }>>([]);
const towers = ref<Array<{ id: number; name: string; house_number?: string }>>([]);

const loadingStreets = ref(false);
const loadingTowers = ref(false);

const isAddingTower = ref(false);
const newTowerName = ref('');
const savingTower = ref(false);
const towerErrors = ref<Record<string, string[]>>({});

const matchingTowers = computed(() => {
    const hn = houseNumber.value?.trim();
    if (!hn || !towers.value.length) return [];
    return towers.value.filter(t => String(t.house_number || '').trim() === hn);
});

const showTowerSelect = computed(() => {
    return houseNumber.value && matchingTowers.value.length > 1;
});

const showConfirmTowerButton = computed(() => {
    return !!selectedTowerId.value && matchingTowers.value.length > 0;
});

const showAddTowerButton = computed(() => {
    return houseNumber.value && !loadingTowers.value && matchingTowers.value.length === 0;
});

// Watch houseNumber and towers to auto-select if only one tower matches
watch([houseNumber, towers], () => {
    const matches = matchingTowers.value;
    if (matches.length === 1) {
        selectedTowerId.value = String(matches[0].id);
    } else if (matches.length === 0) {
        selectedTowerId.value = '';
    }
}, { deep: true });

watch(selectedCityId, async (newCityId) => {
    selectedStreetId.value = '';
    streets.value = [];
    selectedTowerId.value = '';
    houseNumber.value = '';
    towers.value = [];
    
    if (newCityId) {
        loadingStreets.value = true;
        try {
            const response = await axios.get(route('geo.streets', { city: newCityId }));
            streets.value = response.data;
        } catch (error) {
            console.error('Error fetching streets:', error);
        } finally {
            loadingStreets.value = false;
        }
    }
});

watch(selectedStreetId, async (newStreetId) => {
    selectedTowerId.value = '';
    houseNumber.value = '';
    towers.value = [];
    
    if (newStreetId) {
        loadingTowers.value = true;
        try {
            const response = await axios.get(route('geo.towers', { street: newStreetId }));
            towers.value = response.data;
        } catch (error) {
            console.error('Error fetching towers:', error);
        } finally {
            loadingTowers.value = false;
        }
    }
});

// Removed the problematic watcher that caused TypeError


const handleAddTower = async () => {
    savingTower.value = true;
    towerErrors.value = {};
    try {
        const response = await axios.post(route('geo.towers.store'), {
            name: newTowerName.value,
            city_id: selectedCityId.value,
            street_id: selectedStreetId.value,
            house_number: houseNumber.value,
        });
        
        const newTower = response.data;
        towers.value.push(newTower);
        selectedTowerId.value = newTower.id;
        isAddingTower.value = false;
        newTowerName.value = '';
    } catch (error: any) {
        if (error.response?.status === 422) {
            towerErrors.value = error.response.data.errors;
        } else {
            console.error('Error saving tower:', error);
        }
    } finally {
        savingTower.value = false;
    }
};

const resetGeoSelection = () => {
    selectedCityId.value = '';
    selectedStreetId.value = '';
    houseNumber.value = '';
    selectedTowerId.value = '';
    streets.value = [];
    towers.value = [];
};
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing, form: slotForm }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <!-- Collapsible Geo Selection Container -->
                <div v-if="!selectedTowerId" class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="city_id">City</Label>
                        <SearchableSelect
                            v-model="selectedCityId"
                            :options="cities"
                            placeholder="Select City"
                        />
                    </div>

                    <div v-if="selectedCityId" class="grid gap-2">
                        <Label for="street_id">Street</Label>
                        <SearchableSelect
                            v-model="selectedStreetId"
                            :options="streets"
                            placeholder="Select Street"
                            :disabled="loadingStreets"
                        />
                        <div v-if="loadingStreets" class="text-xs text-muted-foreground italic">Loading streets...</div>
                    </div>

                    <div v-if="selectedStreetId" class="grid gap-2">
                        <Label for="house_number">House Number</Label>
                        <Input
                            id="house_number"
                            v-model="houseNumber"
                            placeholder="e.g. 42"
                        />
                    </div>
                </div>

                <!-- Tower Selection/Creation -->

                <!-- Tower Selection/Creation (Dependent on House Number) -->
                <div v-if="houseNumber" class="grid gap-2">
                    <div v-if="showTowerSelect">
                        <Label for="tower_id" class="text-amber-600 font-medium">Found multiple towers at #{{ houseNumber }}</Label>
                        <SearchableSelect
                            v-model="selectedTowerId"
                            :options="matchingTowers"
                            placeholder="Select Tower"
                        />
                    </div>

                    <div v-if="showConfirmTowerButton" class="pt-2">
                        <Button type="button" variant="outline" class="w-full border-green-600 text-green-600 bg-green-50/50 hover:bg-green-50 cursor-default font-semibold shadow-sm">
                            ✓ This is my Tower
                        </Button>
                        <button 
                            type="button" 
                            class="mt-2 w-full text-xs text-muted-foreground hover:text-primary underline underline-offset-2"
                            @click="resetGeoSelection"
                        >
                            start over tower search
                        </button>
                    </div>

                    <div v-if="showAddTowerButton" class="pt-2">
                        <Dialog v-model:open="isAddingTower">
                            <DialogTrigger as-child>
                                <Button type="button" variant="outline" class="w-full shadow-sm">
                                    Add Tower at #{{ houseNumber }}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Add New Tower</DialogTitle>
                                    <DialogDescription>
                                        We couldn't find a tower at this address. Please provide a name to create it.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-4 py-4">
                                    <div class="grid gap-2">
                                        <Label for="new_tower_name">Tower Name (e.g. Tower A)</Label>
                                        <Input
                                            id="new_tower_name"
                                            v-model="newTowerName"
                                            placeholder="Enter tower name"
                                        />
                                        <p v-if="towerErrors.name" class="text-sm text-destructive font-medium">{{ towerErrors.name[0] }}</p>
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button type="button" @click="async () => await handleAddTower()" :disabled="savingTower || !newTowerName">
                                        <Spinner v-if="savingTower" class="mr-2" />
                                        Save Tower
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </div>

                    <input type="hidden" name="tower_id" :value="selectedTowerId" />
                    <InputError :message="errors.tower_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="2"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="3"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="5"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="6"
                    :disabled="processing || !selectedTowerId"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="7"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>

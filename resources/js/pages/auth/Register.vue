<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSimpleLayout from '@/Layouts/auth/AuthSimpleLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { ref, watch, computed } from 'vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import axios from 'axios';
import { useDebounceFn } from '@vueuse/core';
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

// Phase 1 State
const selectedCityId = ref<string | number>('');
const selectedStreetId = ref<string | number>('');
const houseNumber = ref('');
const isTowerConfirmed = ref(false);
const tower = ref<any>(null);
const lookupNotFound = ref(false);
const streetName = ref('');

const streets = ref<Array<{ id: number; name: string }>>([]);
const loadingStreets = ref(false);
const loadingTowers = ref(false);

// Add Tower State
const isAddingTower = ref(false);
const newTowerName = ref('');
const towerImageFile = ref<File | null>(null);
const savingTower = ref(false);
const towerErrors = ref<Record<string, string[]>>({});

// Image handling
const currentImage = computed(() => {
    if (tower.value && tower.value.image_path) {
        return '/storage/' + tower.value.image_path;
    }
    return '/storage/powertower.png';
});

// Step 1c - Debounced Lookup
const fetchTower = useDebounceFn(async () => {
    if (!houseNumber.value.trim() || !selectedStreetId.value) {
        tower.value = null;
        lookupNotFound.value = false;
        return;
    }
    
    loadingTowers.value = true;
    try {
        const response = await axios.get(route('geo.towers', { 
            street: selectedStreetId.value, 
            house_number: houseNumber.value 
        }));
        
        streetName.value = response.data.street_name || '';
        
        if (response.data.found) {
            tower.value = response.data.tower;
            lookupNotFound.value = false;
        } else {
            tower.value = null;
            lookupNotFound.value = true;
        }
    } catch (error) {
        console.error('Error fetching towers:', error);
        tower.value = null;
        lookupNotFound.value = false;
    } finally {
        loadingTowers.value = false;
    }
}, 1000);

watch(houseNumber, () => {
    lookupNotFound.value = false; // Rule 6: Disappears on new keystroke
    fetchTower();
});

// Step 1b - Reset logic
watch(selectedCityId, async (newCityId) => {
    selectedStreetId.value = '';
    streets.value = [];
    houseNumber.value = '';
    tower.value = null;
    isTowerConfirmed.value = false;
    lookupNotFound.value = false;
    
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

watch(selectedStreetId, () => {
    houseNumber.value = '';
    tower.value = null;
    isTowerConfirmed.value = false;
    lookupNotFound.value = false;
});

const startOver = () => {
    selectedCityId.value = '';
    selectedStreetId.value = '';
    houseNumber.value = '';
    tower.value = null;
    isTowerConfirmed.value = false;
    lookupNotFound.value = false;
    streets.value = [];
};

const confirmTower = () => {
    isTowerConfirmed.value = true;
};

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        towerImageFile.value = target.files[0];
    }
};

const handleAddTower = async () => {
    savingTower.value = true;
    towerErrors.value = {};
    
    const formData = new FormData();
    formData.append('name', newTowerName.value);
    formData.append('city_id', String(selectedCityId.value));
    formData.append('street_id', String(selectedStreetId.value));
    formData.append('house_number', houseNumber.value);
    if (towerImageFile.value) {
        formData.append('image', towerImageFile.value);
    }

    try {
        const response = await axios.post(route('geo.towers.store'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        // Rule 7: Treat both 200 and 201 identically
        tower.value = response.data;
        lookupNotFound.value = false;
        isAddingTower.value = false;
        newTowerName.value = '';
        towerImageFile.value = null;
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
</script>

<template>
    <AuthSimpleLayout
        title="Create an account"
        description="Join your tower's community today"
        :image="currentImage"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing, form: slotForm }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <!-- Phase 1: Tower Search (Progressive Disclosure) -->
                <div v-if="!isTowerConfirmed" class="grid gap-6">
                    <!-- Step 1a: City Dropdown -->
                    <div v-if="!tower" class="grid gap-2">
                        <Label for="city_id">City</Label>
                        <SearchableSelect
                            v-model="selectedCityId"
                            :options="cities"
                            placeholder="Select City"
                        />
                    </div>

                    <!-- Step 1b: Street Dropdown -->
                    <div v-if="selectedCityId && !tower" class="grid gap-2">
                        <Label for="street_id">Street</Label>
                        <SearchableSelect
                            v-model="selectedStreetId"
                            :options="streets"
                            placeholder="Select Street"
                            :disabled="loadingStreets"
                        />
                        <div v-if="loadingStreets" class="text-xs text-muted-foreground italic">Loading streets...</div>
                    </div>

                    <!-- Step 1c: House Number Input -->
                    <div v-if="selectedStreetId && !tower" class="grid gap-2">
                        <Label for="house_number">House Number</Label>
                        <div class="relative">
                            <Input
                                id="house_number"
                                v-model="houseNumber"
                                placeholder="e.g. 42"
                                :disabled="loadingTowers"
                            />
                            <div v-if="loadingTowers" class="absolute right-3 top-2.5">
                                <Spinner class="size-4" />
                            </div>
                        </div>
                        <div v-if="loadingTowers" class="text-xs text-muted-foreground italic">Searching for tower...</div>
                    </div>

                    <!-- Step 1d: Tower Confirmation -->
                    <div v-if="tower" class="grid gap-4 p-4 border rounded-lg bg-accent/50 animate-in fade-in zoom-in duration-300">
                        <div class="space-y-1">
                            <h3 class="font-semibold text-lg">{{ tower.name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ tower.full_address }}</p>
                        </div>
                        
                        <Button type="button" @click="confirmTower" class="w-full">
                            Yes, this is my Tower!
                        </Button>
                        
                        <div class="text-center text-sm">
                            <span class="text-muted-foreground">No, this is not my Tower</span>
                            <button type="button" class="ml-1 text-primary hover:underline font-medium" @click="startOver">
                                Start over tower search
                            </button>
                        </div>
                    </div>

                    <!-- Step 1e: "Add Tower" prompt -->
                    <div v-if="lookupNotFound && !loadingTowers" class="pt-2 animate-in fade-in duration-300">
                        <Dialog v-model:open="isAddingTower">
                            <DialogTrigger as-child>
                                <Button type="button" variant="outline" class="w-full shadow-sm border-dashed">
                                    Add Tower at {{ streetName }} {{ houseNumber }}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Add New Tower</DialogTitle>
                                    <DialogDescription>
                                        We couldn't find a building at <strong>{{ streetName }} {{ houseNumber }}</strong>. 
                                        You can add it now.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-4 py-4">
                                    <div class="grid gap-2">
                                        <Label for="new_tower_name">Building/Tower Name (Optional)</Label>
                                        <Input
                                            id="new_tower_name"
                                            v-model="newTowerName"
                                            placeholder="e.g. Park Tower A"
                                        />
                                        <p v-if="towerErrors.name" class="text-sm text-destructive font-medium">{{ towerErrors.name[0] }}</p>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="tower_image">Building Image (Optional)</Label>
                                        <Input
                                            id="tower_image"
                                            type="file"
                                            accept="image/*"
                                            @change="handleImageUpload"
                                        />
                                        <p v-if="towerErrors.image" class="text-sm text-destructive font-medium">{{ towerErrors.image[0] }}</p>
                                        <p class="text-[10px] text-muted-foreground">JPG, GIF or PNG. Max 5MB.</p>
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button type="button" @click="async () => await handleAddTower()" :disabled="savingTower">
                                        <Spinner v-if="savingTower" class="mr-2" />
                                        Create Tower
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <!-- Phase 2: User Details (Visible only after Tower selection) -->
                <div v-if="isTowerConfirmed" class="grid gap-6 animate-in slide-in-from-bottom-4 duration-500">
                    <div class="flex items-center justify-between p-3 border rounded-lg bg-muted/30">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold uppercase text-muted-foreground tracking-wider">Building</span>
                            <span class="font-medium">{{ tower.name }}</span>
                        </div>
                        <button type="button" @click="isTowerConfirmed = false" class="text-xs text-primary hover:underline">
                            Change
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="floor">Floor</Label>
                            <Input
                                id="floor"
                                type="text"
                                name="floor"
                                placeholder="Floor"
                                required
                                :tabindex="2"
                            />
                            <InputError :message="errors.floor" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="apartment_number">Apartment #</Label>
                            <Input
                                id="apartment_number"
                                type="text"
                                name="apartment_number"
                                placeholder="Apt #"
                                required
                                :tabindex="3"
                            />
                            <InputError :message="errors.apartment_number" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="4"
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
                            :tabindex="5"
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
                            :tabindex="6"
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
                            :tabindex="7"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <!-- Tower ID hidden field -->
                    <input type="hidden" name="tower_id" :value="tower.id" />

                    <Button
                        type="submit"
                        class="mt-2 w-full"
                        tabindex="8"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" />
                        Create account
                    </Button>
                </div>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="9"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthSimpleLayout>
</template>

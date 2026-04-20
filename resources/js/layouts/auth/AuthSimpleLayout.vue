<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
    image?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh  flex-col items-center justify-center gap-6 bg-background "
    >
        <div class="flex flex-row w-full min-h-svh  ">

 
            <div class="flex justify-center  w-1/2  p-6 md:p-10">
            
            
            <div class="w-full max-w-sm ">
                <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex w-80 items-center justify-center rounded-md"
                        >
                        <img src="/storage/logo.png" class="w-full h-full" />
                            <!-- <AppLogoIcon
                                class="size-9 fill-current text-[var(--foreground)] dark:text-white"
                            /> -->
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
                </div>
                </div>
            </div>
                   
            <div class="w-1/2">
            <img :src="image || '/storage/powertower.png'" class="object-cover w-full h-full" />
            </div>
        </div>

  
    </div>
</template>

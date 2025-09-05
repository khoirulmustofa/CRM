<script setup>
import AppFooter from "@/Components/layout/AppFooter.vue";
import AppTopbar from "@/Components/layout/AppTopbar.vue";
</script>

<template>
    <div class="bg-surface-50 dark:bg-surface-950 min-h-screen flex flex-col gap-6">
        <AppTopbar />
        <div class="flex flex-col w-full max-w-7xl mx-auto gap-6 flex-1">
            <slot />
        </div>
        <AppFooter />
    </div>
</template>
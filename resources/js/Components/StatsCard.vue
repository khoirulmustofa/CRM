<template>
    <Card class="stats-card">
        <template #content>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ formatValue(value) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ title }}
                    </div>
                </div>
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" :class="iconBackground">
                    <i :class="[icon, iconColor, 'text-xl']"></i>
                </div>
            </div>
        </template>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from 'primevue/card';

const props = defineProps({
    title: String,
    value: [Number, String],
    icon: String,
    color: {
        type: String,
        default: 'blue'
    }
});

const formatValue = (val) => {
    if (typeof val === 'number' && val > 999) {
        return (val / 1000).toFixed(1) + 'k';
    }
    return val;
};

const iconBackground = computed(() => {
    const backgrounds = {
        blue: 'bg-blue-100 dark:bg-blue-900',
        red: 'bg-red-100 dark:bg-red-900',
        green: 'bg-green-100 dark:bg-green-900',
        yellow: 'bg-yellow-100 dark:bg-yellow-900'
    };
    return backgrounds[props.color] || backgrounds.blue;
});

const iconColor = computed(() => {
    const colors = {
        blue: 'text-blue-600 dark:text-blue-400',
        red: 'text-red-600 dark:text-red-400',
        green: 'text-green-600 dark:text-green-400',
        yellow: 'text-yellow-600 dark:text-yellow-400'
    };
    return colors[props.color] || colors.blue;
});
</script>
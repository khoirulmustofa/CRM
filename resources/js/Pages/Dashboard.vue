<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import NotificationPublic from '@/Pages/NotificationPublic.vue';
import NotificationPrivate from '@/Pages/NotificationPrivate.vue';

const value = ref('');

const auth = ref(usePage().props.auth);

const props = defineProps({
    users: Object
})

</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

       <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <NotificationPublic :auth="auth" />

                        <NotificationPrivate :auth="auth" :users="users" />
                    </div>
                </div>
            </div>
        </div>

        
    </AuthenticatedLayout>
</template>

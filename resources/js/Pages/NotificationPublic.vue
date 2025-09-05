<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';

// Membuat referensi reaktif untuk berbagai keadaan UI
const messageInput = ref(''); // Input untuk pesan
const isSending = ref(false); // Status apakah pesan sedang dikirim
const feedbackMessage = ref(''); // Pesan umpan balik untuk pengguna
const receivedMessages = ref([]); // Menyimpan daftar pesan yang diterima

let publicChannel = null; // Variabel untuk menyimpan langganan channel

// --- Mengirim Pesan ---
// Fungsi asinkron untuk mengirim pesan ke endpoint Laravel
const sendPublicMessage = async () => {
    // Validasi input: cek apakah input tidak kosong
    if (!messageInput.value.trim()) {
        feedbackMessage.value = 'Silakan masukkan pesan.';
        return;
    }

    // Set status pengiriman dan reset pesan umpan balik sebelumnya
    isSending.value = true;
    feedbackMessage.value = '';

    try {
        // Kirim permintaan POST ke endpoint '/send-public-message'
        const response = await axios.post('/send-public-message', {
            message: messageInput.value, // Kirim isi pesan
        });

        // Log respons dari server dan beri umpan balik sukses
        console.log('Respons Server:', response.data);
        feedbackMessage.value = response.data.status || 'Pesan berhasil dikirim!';
        messageInput.value = ''; // Kosongkan input setelah berhasil

    } catch (error) {
        // Tangani error yang terjadi selama pengiriman
        console.error('Error saat mengirim pesan:', error);

        // Cek jenis error dan berikan pesan yang sesuai
        if (error.response) {
            // Error dari server (misalnya 4xx, 5xx)
            console.error('Data Error Server:', error.response.data);
            console.error('Status Error Server:', error.response.status);
            feedbackMessage.value = `Error: ${error.response.data.message || 'Gagal mengirim pesan.'}`;
        } else if (error.request) {
            // Tidak ada respons dari server (masalah jaringan)
            console.error('Tidak ada respons diterima:', error.request);
            feedbackMessage.value = 'Error: Tidak ada respons dari server.';
        } else {
            // Error lainnya (masalah konfigurasi, dll)
            console.error('Pesan error:', error.message);
            feedbackMessage.value = `Error: ${error.message}`;
        }
    } finally {
        // Reset status pengiriman, terlepas dari sukses atau gagal
        isSending.value = false;
    }
};
// --- Akhir Pengiriman ---

// --- Menangani Notifikasi Publik ---
// Fungsi yang dipanggil ketika pesan diterima melalui channel publik
const handlePublicNotification = (data) => {
    console.log('Notifikasi publik diterima via Echo:', data);
    // Tambahkan pesan yang diterima ke daftar pesan
    receivedMessages.value.push(data.message);
};
// --- Akhir Penanganan ---

// Saat komponen dimuat (mounted)
onMounted(() => {
    // Periksa apakah Laravel Echo sudah diinisialisasi
    if (typeof window.Echo !== 'undefined') {
        console.log('Berlangganan ke channel publik: public-updates');
        // Berlangganan ke channel 'public-updates'
        publicChannel = window.Echo.channel('public-updates');
        // Dengarkan event '.public.notification' di channel tersebut
        publicChannel.listen('.public.notification', handlePublicNotification);
    } else {
        // Tampilkan error jika Echo belum diinisialisasi
        console.error('Laravel Echo belum diinisialisasi. Periksa konfigurasi bootstrap.js Anda.');
        feedbackMessage.value = 'Pembaruan real-time tidak tersedia.';
    }
});

// Saat komponen dihancurkan (unmounted)
onUnmounted(() => {
    console.log('Berhenti berlangganan dari channel publik: public-updates');
    // Hentikan mendengarkan event jika channel sudah dibuat
    if (publicChannel) {
        publicChannel.stopListening('.public.notification');
    }
});

</script>
<template>
    <div class="p-6">
        <h2 class="text-xl font-bold mb-4">Send Public Message</h2>

        <!-- Message Sending Form -->
        <form @submit.prevent="sendPublicMessage" class="mb-6">
            <div class="flex items-center space-x-2">
                <InputText id="message" v-model="messageInput" type="text" :disabled="isSending"
                    placeholder="Enter your message" class="flex-1 p-2 border rounded" />
                <Button type="submit" :disabled="isSending"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50">
                    {{ isSending ? 'Sending...' : 'Send Message' }}
                </Button>
            </div>
        </form>



        <!-- Feedback Message -->
        <div v-if="feedbackMessage" :class="{
            'text-red-500': feedbackMessage.startsWith('Error'),
            'text-green-500': !feedbackMessage.startsWith('Error')
        }" class="mb-4">
            {{ feedbackMessage }}
        </div>

        <!-- Display Received Messages -->
        <div v-if="receivedMessages.length > 0">
            <h3 class="text-lg font-semibold mb-2">Received Messages:</h3>
            <ul class="list-disc pl-5 space-y-1">
                <li v-for="(msg, index) in receivedMessages" :key="index" class="p-2 bg-gray-100 rounded">
                    {{ msg }}
                </li>
            </ul>
        </div>
        <div v-else class="text-gray-500">
            No messages received yet. Send one or wait for broadcasts.
        </div>
    </div>
</template>

<style scoped>
/* Add any component-specific styles here if needed */
</style>
<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3'; // Impor usePage untuk mendapatkan data pengguna

const messageInput = ref(''); // Input untuk pesan
const isSending = ref(false); // Status pengiriman pesan
const feedbackMessage = ref(''); // Pesan umpan balik untuk pengguna
const receivedMessages = ref([]); // Menyimpan daftar pesan yang diterima

let privateChannel = null; // Variabel untuk menyimpan langganan channel PRIVATE

// Dapatkan data halaman dan pengguna dari Inertia
const page = usePage();
const users = page.props.users; // Daftar pengguna (diasumsikan dikirim dari backend)

// Dapatkan ID pengguna yang sedang login
const userId = ref(page.props.auth.user.id);

// --- Mengirim Pesan Pribadi ---
// Fungsi ini mengirim pesan ke endpoint Laravel
const sendPrivateMessage = async () => {
  // Validasi input
  if (!messageInput.value.trim()) {
    feedbackMessage.value = 'Silakan masukkan pesan.';
    return;
  }

  // Set status pengiriman
  isSending.value = true;
  feedbackMessage.value = '';

  try {
    // Kirim permintaan POST ke endpoint '/send-private-message'
    const response = await axios.post('/send-private-message', {
      target_user_id: userId.value, // Gunakan .value untuk mendapatkan nilai aktual dari ref
      message: messageInput.value,
    });

    // Tampilkan respons dari server
    console.log('Respons Server:', response.data);
    feedbackMessage.value = response.data.status || 'Pesan pribadi terkirim!';
    messageInput.value = ''; // Kosongkan input setelah berhasil

  } catch (error) {
    // Tangani error saat pengiriman
    console.error('Error saat mengirim pesan pribadi:', error);
    if (error.response) {
      // Error dari server
      console.error('Data Error Server:', error.response.data);
      console.error('Status Error Server:', error.response.status);
      feedbackMessage.value = `Error: ${error.response.data.message || 'Gagal mengirim pesan pribadi.'}`;
    } else if (error.request) {
      // Tidak ada respons dari server
      console.error('Tidak ada respons:', error.request);
      feedbackMessage.value = 'Error: Tidak ada respons dari server.';
    } else {
      // Error lainnya
      console.error('Pesan error:', error.message);
      feedbackMessage.value = `Error: ${error.message}`;
    }
  } finally {
    // Reset status pengiriman
    isSending.value = false;
  }
};
// --- Akhir Pengiriman ---

// --- Mendengarkan Siaran Pribadi ---
// Fungsi ini menangani pesan yang diterima melalui Echo
const handlePrivateNotification = (data) => {
  console.log('Notifikasi pribadi diterima via Echo:', data);
  // Tambahkan pesan ke daftar pesan yang diterima
  receivedMessages.value.push(`${data.from}: ${data.message}`);
};

// Saat komponen dimuat
onMounted(() => {
  // Pastikan Echo sudah diinisialisasi dan ID pengguna tersedia
  if (typeof window.Echo !== 'undefined' && userId) {
    console.log(`Berlangganan ke channel pribadi: App.Models.User.${userId.value}`);

    // --- Cara yang Benar untuk Mendengarkan Event Pribadi ---
    // 1. Gunakan window.Echo.private() untuk channel PRIVATE
    // 2. Gunakan nama channel yang benar berdasarkan PrivateEvent
    privateChannel = window.Echo.private(`App.Models.User.${userId.value}`);

    // 3. Gunakan .listen() untuk event
    // 4. Karena broadcastAs() mengembalikan 'user.notification',
    //    Anda HARUS menambahkan titik (.) di depan nama event di Echo
    privateChannel.listen('.user.notification', handlePrivateNotification);

  } else {
    // Tampilkan error jika Echo tidak diinisialisasi atau ID pengguna tidak tersedia
    const errorMsg = !window.Echo ? 'Laravel Echo belum diinisialisasi.' : 'ID Pengguna tidak tersedia.';
    console.error(errorMsg + ' Periksa kembali pengaturan Anda.');
    feedbackMessage.value = 'Pembaruan pesan pribadi secara real-time tidak tersedia.';
  }
});

// --- Bersihkan Listener ---
// Saat komponen dihancurkan
onUnmounted(() => {
  if (privateChannel && userId) {
    console.log(`Berhenti berlangganan dari channel pribadi: App.Models.User.${userId.value}`);
    // Hentikan mendengarkan event tertentu saat komponen dihancurkan
    privateChannel.stopListening('.user.notification');
    // Opsional: tinggalkan channel:
    // window.Echo.leave(`App.Models.User.${userId.value}`);
  }
});
// --- Akhir Mendengarkan ---
</script>

<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">Send Private Message</h2>

    <!-- Message Sending Form -->
    <!-- Changed @submit.prevent to call sendPrivateMessage -->
    <form @submit.prevent="sendPrivateMessage" class="mb-6">
      <div class="card flex justify-center">
        <Select v-model="userId" :options="users" optionLabel="name" optionValue="id" placeholder="Select a City"
          class="w-full md:w-56" />
      </div>
      <div class="flex items-center space-x-2">
        <InputText id="message" v-model="messageInput" type="text" :disabled="isSending"
          placeholder="Enter your private message" class="flex-1 p-2 border rounded" />
        <Button type="submit" :disabled="isSending"
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50">
          {{ isSending ? 'Sending...' : 'Send Private Message' }}
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
      <h3 class="text-lg font-semibold mb-2">Your Private Messages:</h3>
      <ul class="list-disc pl-5 space-y-1">
        <li v-for="(msg, index) in receivedMessages" :key="index" class="p-2 bg-gray-100 rounded">
          {{ msg }}
        </li>
      </ul>
    </div>
    <div v-else class="text-gray-500">
      No private messages received yet.
    </div>
  </div>
</template>

<style scoped>
/* Add any component-specific styles here if needed */
</style>
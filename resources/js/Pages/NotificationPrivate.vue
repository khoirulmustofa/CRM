<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3'; // Import usePage to get user data

const messageInput = ref('');
const isSending = ref(false);
const feedbackMessage = ref('');
const receivedMessages = ref([]); // State to hold received messages

let privateChannel = null; // Variable to store the PRIVATE channel subscription


const page = usePage();
const users = page.props.users;

const userId = ref(page.props.auth.user.id);

// --- Sending the Private Message ---
// This assumes you have a corresponding Laravel controller endpoint
// e.g., POST /send-private-message-to-me
const sendPrivateMessage = async () => {
  if (!messageInput.value.trim()) {
    feedbackMessage.value = 'Please enter a message.';
    return;
  }

  isSending.value = true;
  feedbackMessage.value = '';

  try {
    // Adjust the URL to your actual private message endpoint
    const response = await axios.post('/send-private-message', {
      target_user_id: userId.value, // Use .value to get the actual value
      message: messageInput.value,
    });

    console.log('Server Response:', response.data);
    feedbackMessage.value = response.data.status || 'Private message sent!';
    messageInput.value = ''; // Clear input on success

  } catch (error) {
    console.error('Error sending private message:', error);
    if (error.response) {
      console.error('Server Error Data:', error.response.data);
      console.error('Server Error Status:', error.response.status);
      feedbackMessage.value = `Error: ${error.response.data.message || 'Failed to send private message.'}`;
    } else if (error.request) {
      console.error('No response received:', error.request);
      feedbackMessage.value = 'Error: No response from server.';
    } else {
      console.error('Error message:', error.message);
      feedbackMessage.value = `Error: ${error.message}`;
    }
  } finally {
    isSending.value = false;
  }
};
// --- End Sending ---

// --- Listening for Private Broadcasts ---
const handlePrivateNotification = (data) => {
    console.log('Private notification received via Echo:', data);
    // You can display either or both parts
    receivedMessages.value.push(`${data.from}: ${data.message}`);
    // Or just the message: receivedMessages.value.push(data.message);
};

onMounted(() => {
    // Ensure Echo is initialized and user ID is available
    if (typeof window.Echo !== 'undefined' && userId) {
        console.log(`Subscribing to private channel: App.Models.User.${userId.value}`);

        // --- Correct Way to Listen for Private Events ---
        // 1. Use window.Echo.private() for PRIVATE channels
        // 2. Use the correct channel name based on your PrivateEvent
        privateChannel = window.Echo.private(`App.Models.User.${userId.value}`);

        // 3. Use .listen() for the event
        // 4. Because broadcastAs() returns 'user.notification',
        //    you MUST prefix the event name with a DOT (.) in Echo
        privateChannel.listen('.user.notification', handlePrivateNotification);

    } else {
        const errorMsg = !window.Echo ? 'Laravel Echo is not initialized.' : 'User ID not available.';
        console.error(errorMsg + ' Check your setup.');
        feedbackMessage.value = 'Real-time private updates are unavailable.';
    }
});

// --- Cleanup Listener ---
onUnmounted(() => {
    if (privateChannel && userId) {
        console.log(`Unsubscribing from private channel: App.Models.User.${userId.value}`);
        // Stop listening to the specific event when component is destroyed
        privateChannel.stopListening('.user.notification');
        // Optionally leave the channel:
        // window.Echo.leave(`App.Models.User.${userId.value}`);
    }
});
// --- End Listening ---
</script>

<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">Send Private Message</h2>

    <!-- Message Sending Form -->
    <!-- Changed @submit.prevent to call sendPrivateMessage -->
    <form @submit.prevent="sendPrivateMessage" class="mb-6">
       <div class="card flex justify-center">
        <Select v-model="userId" :options="users" optionLabel="name" optionValue="id" placeholder="Select a City" class="w-full md:w-56" />
    </div>
      <div class="flex items-center space-x-2">
        <InputText
          id="message"
          v-model="messageInput"
          type="text"
          :disabled="isSending"
          placeholder="Enter your private message"
          class="flex-1 p-2 border rounded"
        />
        <Button
          type="submit"
          :disabled="isSending"
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
        >
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
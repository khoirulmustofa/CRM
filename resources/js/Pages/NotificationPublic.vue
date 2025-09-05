<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';

const messageInput = ref('');
const isSending = ref(false);
const feedbackMessage = ref('');
const receivedMessages = ref([]); // State to hold received messages

let publicChannel = null; // Variable to store the channel subscription

// --- Sending the Message ---
const sendPublicMessage = async () => {
  if (!messageInput.value.trim()) {
    feedbackMessage.value = 'Please enter a message.';
    return;
  }

  isSending.value = true;
  feedbackMessage.value = '';

  try {
    const response = await axios.post('/send-public-message', {
      message: messageInput.value,
    });

    console.log('Server Response:', response.data);
    feedbackMessage.value = response.data.status || 'Message sent successfully!';
    messageInput.value = ''; // Clear input on success

  } catch (error) {
    console.error('Error sending message:', error);
    if (error.response) {
      console.error('Server Error Data:', error.response.data);
      console.error('Server Error Status:', error.response.status);
      feedbackMessage.value = `Error: ${error.response.data.message || 'Failed to send message.'}`;
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

const handlePublicNotification = (data) => {
    console.log('Public notification received via Echo:', data);
    receivedMessages.value.push(data.message);
};

onMounted(() => {
    // This should now work as window.Echo is properly initialized
    if (typeof window.Echo !== 'undefined') {
        console.log('Subscribing to public channel: public-updates');
        publicChannel = window.Echo.channel('public-updates');
        publicChannel.listen('.public.notification', handlePublicNotification);
    } else {
        console.error('Laravel Echo is not initialized. Check your bootstrap.js configuration.');
        feedbackMessage.value = 'Real-time updates are unavailable.';
    }
});

onUnmounted(() => {
    console.log('Unsubscribing from public channel: public-updates');
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
        <InputText
          id="message"
          v-model="messageInput"
          type="text"
          :disabled="isSending"
          placeholder="Enter your message"
          class="flex-1 p-2 border rounded"
        />
        <Button
          type="submit"
          :disabled="isSending"
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
        >
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
<template>
  <div class="chat-bubble" @click="$emit('open')" title="Abrir chat de soporte">
    <div class="bubble-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="32" height="32">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
      </svg>
    </div>

    <div v-if="unreadCount > 0" class="bubble-badge">
      {{ unreadCount > 99 ? '99+' : unreadCount }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChatBubble',
  props: {
    unreadCount: {
      type: Number,
      default: 0,
    },
  },
  emits: ['open'],
};
</script>

<style scoped>
.chat-bubble {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #c92a2a 0%, #a61e1e 50%, #2f9e44 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(201, 42, 42, 0.35);
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
}

.chat-bubble:hover {
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 6px 20px rgba(201, 42, 42, 0.45);
}

.bubble-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.bubble-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ffd43b;
  color: #1a1a1a;
  border-radius: 50%;
  min-width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
  padding: 0 6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  border: 2px solid white;
}
</style>

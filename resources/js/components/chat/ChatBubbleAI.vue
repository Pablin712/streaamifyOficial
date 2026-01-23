<template>
  <div class="chat-bubble-ai" @click="$emit('open')" title="Asistente IA - Haz click para chatear">
    <div class="bubble-icon-ai">
      <!-- Icono de robot SVG -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="32" height="32">
        <path d="M12 2c-1.1 0-2 .9-2 2v1H7c-1.1 0-2 .9-2 2v1H3v2h2v3c0 2.2 1.8 4 4 4h1v2h4v-2h1c2.2 0 4-1.8 4-4v-3h2V8h-2V7c0-1.1-.9-2-2-2h-3V4c0-1.1-.9-2-2-2zm-2 6h4v5c0 1.1-.9 2-2 2s-2-.9-2-2V8zm-1 2H8v3h1v-3zm6 0v3h1v-3h-1z"/>
        <circle cx="9.5" cy="10.5" r="1"/>
        <circle cx="14.5" cy="10.5" r="1"/>
      </svg>
    </div>

    <!-- Animación de pulso -->
    <div class="bubble-pulse"></div>
  </div>
</template>

<script>
export default {
  name: 'ChatBubbleAI',
  emits: ['open'],
};
</script>

<style scoped>
.chat-bubble-ai {
  width: 65px;
  height: 65px;
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
  transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55), box-shadow 0.3s;
  position: relative;
  overflow: visible;
}

/* Adaptación al Dark Mode */
[data-dark-mode="true"] .chat-bubble-ai {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  box-shadow: 0 4px 20px rgba(255, 226, 38, 0.4);
}

.chat-bubble-ai:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.35);
}

[data-dark-mode="true"] .chat-bubble-ai:hover {
  box-shadow: 0 6px 30px rgba(255, 226, 38, 0.5);
}

.bubble-icon-ai {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 2;
}

/* Animación de pulso */
.bubble-pulse {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: var(--primary-color);
  opacity: 0.5;
  animation: pulse-ai 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  z-index: 1;
}

@keyframes pulse-ai {
  0%, 100% {
    transform: scale(1);
    opacity: 0.5;
  }
  50% {
    transform: scale(1.15);
    opacity: 0;
  }
}

/* Efecto de resplandor en hover */
.chat-bubble-ai::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
  opacity: 0;
  transition: opacity 0.3s;
  z-index: 0;
}

.chat-bubble-ai:hover::before {
  opacity: 0.6;
  animation: rotate-glow 3s linear infinite;
}

@keyframes rotate-glow {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .chat-bubble-ai {
    width: 55px;
    height: 55px;
  }

  .bubble-icon-ai svg {
    width: 28px;
    height: 28px;
  }
}
</style>

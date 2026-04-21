// ============================================
// NODO: ENVIAR A SISTEMA CHAT
// Ubicación: Después del nodo "Normalizar"
// ============================================

// Obtener datos normalizados
const normalized = $input.first().json;

// Obtener mensaje original para extraer URLs de media
const originalItem = $('Normalizar').item;
const originalBody = originalItem.json.body;
const originalData = originalBody?.data || {};
const originalMessage = originalData.message || {};

// Preparar payload para sistema de chat
const chatPayload = {
  canal_user_id: normalized.message.chat_id,
  telefono: normalized.contact.numero,
  nombre: normalized.contact.tipo === 'empleado' ? 'Empleado' : 'Cliente',
  mensaje: normalized.message.content || '',
  tipo: normalized.message.type,
  external_message_id: normalized.message.id,
  instance: normalized.instance.name,
  origen: 'n8n',
  payload: {
    source: 'evolution-api',
    color: normalized.instance.color,
    es_empleado: normalized.contact.esEmpleado,
    instance_name: normalized.instance.name,
    timestamp: new Date().toISOString()
  }
};

// Extraer URLs de media según tipo de mensaje
switch (normalized.message.type) {
  case 'imagen':
    if (originalMessage.imageMessage?.url) {
      chatPayload.media_url = originalMessage.imageMessage.url;
      chatPayload.mime_type = originalMessage.imageMessage.mimetype || 'image/jpeg';
      
      // Agregar dimensiones si están disponibles
      if (originalMessage.imageMessage.height && originalMessage.imageMessage.width) {
        chatPayload.payload.image_dimensions = {
          height: originalMessage.imageMessage.height,
          width: originalMessage.imageMessage.width
        };
      }
    }
    break;
    
  case 'audio':
    if (originalMessage.audioMessage?.url) {
      chatPayload.media_url = originalMessage.audioMessage.url;
      chatPayload.mime_type = originalMessage.audioMessage.mimetype || 'audio/ogg';
      
      // Duración del audio si está disponible
      if (originalMessage.audioMessage.seconds) {
        chatPayload.payload.audio_duration = originalMessage.audioMessage.seconds;
      }
    }
    break;
    
  case 'video':
    if (originalMessage.videoMessage?.url) {
      chatPayload.media_url = originalMessage.videoMessage.url;
      chatPayload.mime_type = originalMessage.videoMessage.mimetype || 'video/mp4';
    }
    break;
    
  case 'documento':
    if (originalMessage.documentMessage?.url) {
      chatPayload.media_url = originalMessage.documentMessage.url;
      chatPayload.mime_type = originalMessage.documentMessage.mimetype || 'application/pdf';
      chatPayload.payload.file_name = originalMessage.documentMessage.fileName || 'documento';
    }
    break;
}

// Enviar al sistema de chat de forma asíncrona (no bloqueante)
(async () => {
  try {
    const response = await $http.post(
      $env.APP_URL + '/api/chat/whatsapp/inbound',
      chatPayload,
      {
        headers: {
          'X-Chat-Webhook-Token': $env.CHAT_WEBHOOK_TOKEN,
          'Content-Type': 'application/json',
          'X-Webhook-Source': 'n8n-integration'
        },
        timeout: 3000, // 3 segundos máximo
        retry: {
          attempts: 1, // Un reintento
          delay: 1000
        }
      }
    );
    
    console.log(`✅ Chat: Mensaje de ${chatPayload.telefono} enviado al sistema`);
    
  } catch (error) {
    // Solo log warning, no fallar el flujo principal
    console.warn(`⚠️ Chat: Error enviando mensaje de ${chatPayload.telefono}:`, error.message);
    
    // Opcional: Guardar en una variable para procesamiento posterior
    // $vars.set(`chat_error_${Date.now()}`, {
    //   payload: chatPayload,
    //   error: error.message,
    //   timestamp: new Date().toISOString()
    // });
  }
})();

// IMPORTANTE: Pasar los datos normalizados al siguiente nodo
// El sistema de chat no debe bloquear el flujo principal
return [$input.first()];
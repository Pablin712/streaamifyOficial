<?php

namespace App\Services;

/**
 * Ayuda a que los mensajes masivos/automatizados (cobro diario, invitación al
 * sitio web, etc.) no manden texto idéntico a muchos destinatarios seguidos --
 * eso es otra señal de spam que WhatsApp detecta, además del volumen/ritmo de
 * envíos que ya maneja WhatsAppRateLimiter.
 */
class MessageVariationService
{
    /**
     * Elige una de varias versiones completas ya escritas (mismo tono e
     * información, redactadas distinto de punta a punta). Pensado para
     * plantillas por defecto, no para texto que escribió una persona.
     */
    public function pickVariant(array $variants): string
    {
        return trim($variants[array_rand($variants)]);
    }

    /**
     * Agrega algo chico y variable al final sin tocar el texto -- para
     * mensajes que escribió una persona (plantilla personal de un empleado,
     * o un mensaje masivo editado a mano), donde no corresponde reescribir
     * su redacción pero igual conviene romper la repetición exacta letra
     * por letra en cada envío.
     */
    public function lightlyVary(string $text): string
    {
        $suffixes = ['', ' 🙏', ' 😊', ' ✅', ' 🙌', ' 🙂'];

        return rtrim($text).$suffixes[array_rand($suffixes)];
    }
}

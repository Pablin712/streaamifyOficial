<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChatMediaController extends Controller
{
    public function show(Mensaje $mensaje): BinaryFileResponse
    {
        $raw = $mensaje->media_url ?: $mensaje->archivo_url;

        abort_if(! $raw, 404, 'Media no encontrada.');

        $relativePath = $this->resolveRelativeStoragePath((string) $raw);

        abort_if(! $relativePath, 404, 'Ruta de media inválida.');
        abort_if(! Storage::disk('public')->exists($relativePath), 404, 'Archivo de media no existe.');

        $absolutePath = Storage::disk('public')->path($relativePath);
        $mimeType = $mensaje->mime_type ?: (function (string $path): string {
            $detected = @mime_content_type($path);

            return $detected ?: 'application/octet-stream';
        })($absolutePath);

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    private function resolveRelativeStoragePath(string $raw): ?string
    {
        $value = trim($raw);

        if ($value === '' || Str::startsWith($value, ['data:', 'blob:'])) {
            return null;
        }

        // URL absoluta: extraer el path
        if (Str::startsWith($value, ['http://', 'https://'])) {
            $path = (string) parse_url($value, PHP_URL_PATH);
            $value = $path !== '' ? $path : $value;
        }

        $value = ltrim($value, '/');

        // Formatos esperados: storage/chat/... o public/storage/chat/...
        if (Str::startsWith($value, 'public/storage/')) {
            return ltrim(Str::after($value, 'public/storage/'), '/');
        }

        if (Str::startsWith($value, 'storage/')) {
            return ltrim(Str::after($value, 'storage/'), '/');
        }

        return $value !== '' ? $value : null;
    }
}

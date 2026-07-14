<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChatMediaController extends Controller
{
    public function show(Mensaje $mensaje): BinaryFileResponse
    {
        $relativePath = $mensaje->resolveRelativeMediaPath();

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
}

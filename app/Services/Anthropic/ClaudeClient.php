<?php

namespace App\Services\Anthropic;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Wrapper HTTP minimo para la Anthropic Messages API (no hay SDK oficial de PHP).
 * Fuerza tool-use para obtener JSON estructurado en vez de parsear texto libre.
 */
class ClaudeClient
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private string $apiKey;
    private string $version;
    private string $defaultModel;

    public function __construct()
    {
        $this->apiKey = (string) config('services.anthropic.api_key');
        $this->version = (string) config('services.anthropic.version', '2023-06-01');
        $this->defaultModel = (string) config('services.anthropic.model', 'claude-sonnet-5');
    }

    /**
     * Pide a Claude que llame exactamente una herramienta y devuelve su `input`
     * (el objeto JSON ya validado contra el input_schema de la herramienta).
     *
     * @param string $system Instrucciones/rubrica del sistema.
     * @param string $userMessage Contenido a analizar (ej. transcript de la conversacion).
     * @param array $tool Definicion de la herramienta: ['name' => ..., 'description' => ..., 'input_schema' => [...]]
     * @return array El `input` devuelto por el tool_use block.
     */
    public function callTool(string $system, string $userMessage, array $tool, ?string $model = null, int $maxTokens = 1024): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('ANTHROPIC_API_KEY no esta configurada.');
        }

        $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->version,
                'content-type' => 'application/json',
            ])
            ->timeout(60)
            ->post(self::ENDPOINT, [
                'model' => $model ?? $this->defaultModel,
                'max_tokens' => $maxTokens,
                'system' => $system,
                'messages' => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'tools' => [$tool],
                'tool_choice' => ['type' => 'tool', 'name' => $tool['name']],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Anthropic API error (' . $response->status() . '): ' . $response->body()
            );
        }

        $body = $response->json();

        foreach (($body['content'] ?? []) as $block) {
            if (($block['type'] ?? null) === 'tool_use' && ($block['name'] ?? null) === $tool['name']) {
                return [
                    'input' => $block['input'] ?? [],
                    'raw' => $body,
                ];
            }
        }

        throw new RuntimeException('Anthropic no devolvio un tool_use block esperado: ' . json_encode($body));
    }
}

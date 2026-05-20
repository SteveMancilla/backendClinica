<?php

namespace App\Services\Ollama;

use App\Exceptions\OllamaException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaClient
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, ?string $model = null): string
    {
        if (! config('ollama.enabled')) {
            throw new OllamaException('Ollama está deshabilitado en la configuración del servidor.');
        }

        $model = $model ?? (string) config('ollama.model');
        $url = config('ollama.base_url').'/api/chat';

        try {
            $response = Http::timeout((int) config('ollama.timeout_seconds'))
                ->acceptJson()
                ->post($url, [
                    'model' => $model,
                    'messages' => $messages,
                    'stream' => false,
                    'options' => [
                        'temperature' => (float) config('ollama.temperature'),
                        'num_predict' => (int) config('ollama.max_tokens'),
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Ollama connection failed', [
                'url' => $url,
                'model' => $model,
                'error' => $exception->getMessage(),
            ]);

            throw new OllamaException(
                'No se pudo conectar con Ollama. Verifique que `ollama serve` esté en ejecución.',
                $model,
            );
        }

        if (! $response->successful()) {
            $error = $response->json('error') ?? $response->body();

            throw new OllamaException(
                is_string($error) ? $error : 'Error al consultar Ollama.',
                $model,
                $response->status(),
            );
        }

        $content = trim((string) data_get($response->json(), 'message.content', ''));

        if ($content === '') {
            throw new OllamaException('Ollama devolvió una respuesta vacía.', $model);
        }

        return $content;
    }

    public function isReachable(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get(config('ollama.base_url').'/api/tags');

            return $response->successful();
        } catch (ConnectionException) {
            return false;
        }
    }

    /**
     * @return list<string>
     */
    public function listModelNames(): array
    {
        try {
            $response = Http::timeout(5)
                ->get(config('ollama.base_url').'/api/tags');

            if (! $response->successful()) {
                return [];
            }

            $models = $response->json('models', []);

            return collect($models)
                ->pluck('name')
                ->filter()
                ->values()
                ->all();
        } catch (ConnectionException) {
            return [];
        }
    }

    public function modelIsAvailable(string $model): bool
    {
        $installed = $this->listModelNames();

        if ($installed === []) {
            return false;
        }

        if (in_array($model, $installed, true)) {
            return true;
        }

        // Ollama a veces reporta "model:tag" o solo "model"
        $base = explode(':', $model)[0];

        return collect($installed)->contains(
            fn (string $name) => $name === $model || str_starts_with($name, $base.':'),
        );
    }
}

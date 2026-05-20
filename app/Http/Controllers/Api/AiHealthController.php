<?php

namespace App\Http\Controllers\Api;

use App\Services\Ollama\OllamaClient;
use Illuminate\Http\JsonResponse;

class AiHealthController extends Controller
{
    public function __invoke(OllamaClient $ollama): JsonResponse
    {
        $primary = (string) config('ollama.model');
        $fallback = (string) config('ollama.fallback_model');
        $installed = $ollama->listModelNames();

        return response()->json([
            'ollama' => [
                'enabled' => (bool) config('ollama.enabled'),
                'reachable' => $ollama->isReachable(),
                'base_url' => config('ollama.base_url'),
                'primary_model' => $primary,
                'primary_available' => $ollama->modelIsAvailable($primary),
                'fallback_model' => $fallback,
                'fallback_available' => $fallback !== '' ? $ollama->modelIsAvailable($fallback) : false,
                'installed_models' => $installed,
            ],
        ]);
    }
}

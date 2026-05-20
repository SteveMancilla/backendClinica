<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ollama — impresión diagnóstica (IA local)
    |--------------------------------------------------------------------------
    |
    | Modelo principal: deepseek-v4-pro:cloud (requiere `ollama pull deepseek-v4-pro:cloud`)
    | Fallback: modelo local instalado (ej. llama3:8b) si el principal no está disponible.
    |
    */

    'enabled' => env('OLLAMA_ENABLED', true),

    'base_url' => rtrim(env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'), '/'),

    'model' => env('OLLAMA_MODEL', 'deepseek-v4-pro:cloud'),

    'fallback_model' => env('OLLAMA_FALLBACK_MODEL', 'llama3:8b'),

    'timeout_seconds' => (int) env('OLLAMA_TIMEOUT', 120),

    'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.15),

    'max_tokens' => (int) env('OLLAMA_MAX_TOKENS', 600),

    /** Si Ollama falla, usar motor de reglas clínicas (sin inventar hallazgos). */
    'rules_fallback' => env('OLLAMA_RULES_FALLBACK', true),

];

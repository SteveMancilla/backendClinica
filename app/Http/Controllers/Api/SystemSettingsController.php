<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\SystemSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SystemSettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $this->ensureCanView($request);

        return response()->json([
            'data' => SystemSettingsService::get(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->attributes->get('acting_user');

        if ($actor->isAssistant()) {
            throw new AccessDeniedHttpException('No tienes permisos para modificar la configuración.');
        }

        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $merged = SystemSettingsService::mergeForRole(
            $actor->role,
            $validated['data'],
        );

        SystemSettingsService::save($merged);

        return response()->json([
            'message' => 'Configuración guardada correctamente.',
            'data' => $merged,
        ]);
    }

    private function ensureCanView(Request $request): void
    {
        /** @var User|null $actor */
        $actor = $request->attributes->get('acting_user');

        if (! $actor || $actor->isAssistant()) {
            throw new AccessDeniedHttpException('No tienes permisos para ver la configuración.');
        }
    }
}

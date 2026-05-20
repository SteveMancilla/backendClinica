<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateOwnProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', strtolower(trim($credentials['email'])))
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas. Verifica tu correo y contraseña.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Tu usuario está inactivo. Contacta al administrador del sistema.',
            ], 403);
        }

        return response()->json([
            'data' => $this->formatUser($user),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('acting_user');

        $user->update([
            'password' => $request->validated('password'),
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    public function updateProfile(UpdateOwnProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('acting_user');

        $user->update($request->validated());

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'data' => self::formatUser($user->fresh('associatedDoctor')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function formatUser(User $user): array
    {
        $user->loadMissing('associatedDoctor:id,full_name');

        return [
            'id' => $user->id,
            'dni' => $user->dni,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'role' => $user->role,
            'status' => $user->status,
            'specialty' => $user->specialty,
            'cmp' => $user->cmp,
            'rne' => $user->rne,
            'position' => $user->position,
            'origin_city' => $user->origin_city,
            'support_area' => $user->support_area,
            'notes' => $user->notes,
            'associated_doctor_id' => $user->associated_doctor_id,
            'associated_doctor_name' => $user->associatedDoctor?->full_name,
            'created_at' => $user->created_at,
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\AuthController as AuthFormatter;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User|null $actor */
        $actor = $request->attributes->get('acting_user');

        $query = User::query()->with('associatedDoctor:id,full_name')->orderBy('full_name');

        if ($role = $request->string('role')->toString()) {
            if ($role !== 'all') {
                $query->where('role', $role);
            }
        }

        if ($doctorId = $request->integer('associated_doctor_id')) {
            $query->where('associated_doctor_id', $doctorId);
        }

        if ($actor?->isDoctor()) {
            $query->where(function ($q) use ($actor) {
                $q->where('id', $actor->id)
                    ->orWhere(function ($inner) use ($actor) {
                        $inner->where('role', 'assistant')
                            ->where('associated_doctor_id', $actor->id);
                    });
            });
        } elseif ($actor?->isAssistant()) {
            $query->where('id', $actor->id);
        }

        return response()->json([
            'data' => $query->get()->map(fn (User $user) => AuthFormatter::formatUser($user)),
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->attributes->get('acting_user');

        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'active';

        if ($actor->isDoctor()) {
            $data['role'] = 'assistant';
            $data['associated_doctor_id'] = $actor->id;
        }

        if (($data['role'] ?? '') === 'assistant' && empty($data['associated_doctor_id'])) {
            throw new AccessDeniedHttpException('El asistente requiere médico asociado.');
        }

        $user = User::create($data);

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'data' => AuthFormatter::formatUser($user->fresh('associatedDoctor')),
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        /** @var User $actor */
        $actor = $request->attributes->get('acting_user');

        if ($actor->id === $user->id) {
            unset(
                $data['password'],
                $data['role'],
                $data['status'],
                $data['associated_doctor_id'],
            );
        }

        if ($actor->isDoctor()) {
            unset($data['associated_doctor_id'], $data['role']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => AuthFormatter::formatUser($user->fresh('associatedDoctor')),
        ]);
    }
}

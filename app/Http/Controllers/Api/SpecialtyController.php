<?php

namespace App\Http\Controllers\Api;

use App\Models\Specialty;
use Illuminate\Http\JsonResponse;

class SpecialtyController extends Controller
{
    public function index(): JsonResponse
    {
        $specialties = Specialty::query()
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'status']);

        return response()->json(['data' => $specialties]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ApartmentResource;
use App\Http\Requests\IndexApartmentRequest;
use App\Models\Apartment;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function index(IndexApartmentRequest $request)
    {
        $data = $request->validated();
        $query = Apartment::query();

        if (!empty($data['city_id'] ?? null)) {
            $query->where('city_id', $data['city_id']);
        }

        if (!empty($data['governorate_id'] ?? null)) {
            $query->where('governorate_id', $data['governorate_id']);
        }

        if (!empty($data['min_price'] ?? null)) {
            $query->where('price', '>=', $data['min_price']);
        }
        if (!empty($data['max_price'] ?? null)) {
            $query->where('price', '<=', $data['max_price']);
        }

        $sortBy = $data['sort_by'] ?? 'created_at';
        $sortDir = $data['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = min(max((int)($data['per_page'] ?? 10), 1), 50);
        $apartments = $query->paginate($perPage);

        return ResponseHelper::success(ApartmentResource::collection($apartments), 'Apartments retrieved successfully.');
    }

    public function show($id)
    {
        $apartment = Apartment::findOrFail($id);
        return ResponseHelper::success(ApartmentResource::make($apartment), 'Apartment retrieved successfully.');
    }
    public function getFavoriteApartments(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);
        $favoriteApartments = Apartment::where('is_favorite', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ApartmentResource::collection($favoriteApartments);
    }   
}

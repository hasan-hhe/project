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
            $query->orderBy('price', 'asc');
        }

        if (!empty($data['governorate_id'] ?? null)) {
            $query->where('governorate_id', $data['governorate_id']);
            $query->orderBy('price', 'asc');
        }

        // Price filtering
        // If both present, use a between
        if (array_key_exists('min_price', $data) && $data['min_price'] !== null
            && array_key_exists('max_price', $data) && $data['max_price'] !== null) {
            $query->whereBetween('price', [(float)$data['min_price'], (float)$data['max_price']]);
            $query->orderBy('price', 'asc');
        } else {
            if (array_key_exists('min_price', $data) && $data['min_price'] !== null) {
                $query->where('price', '>=', (float)$data['min_price']);
                $query->orderBy('price', 'asc');

            }
            if (array_key_exists('max_price', $data) && $data['max_price'] !== null) {
                $query->where('price', '<=', (float)$data['max_price']);
                $query->orderBy('price', 'asc');
            }
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

        return ResponseHelper::success(ApartmentResource::collection($favoriteApartments), 'Favorite apartments retrieved successfully.');
    }   
}

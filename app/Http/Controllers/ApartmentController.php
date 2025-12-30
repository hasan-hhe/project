<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Apartment::query();

        if ($request->has('city_id') && !empty($request->city_id)) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->has('governorate_id') && !empty($request->governorate_id)) {
            $query->where('governorate_id', $request->governorate_id);
        }

        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        $Apartments = $query->paginate(10);
        $Apartments = ApartmentResource::collection($Apartments);
        return response()->json($Apartments);
    }

    public function show($id)
    {
        $apartment = Apartment::findOrFail($id);
        $apartment = new ApartmentResource($apartment);
        return response()->json($apartment);
    }
}

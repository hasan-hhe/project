<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LocationController extends Controller
{
    #[OA\Get(path: "/governorates", tags: ["Locations"])]
    public function getGovernorates(Request $request)
    {
        $governorates = Governorate::with('cities')
            ->orderBy('name', 'asc')
            ->get();

        return ResponseHelper::success([
            'governorates' => $governorates
        ], 'تم جلب المحافظات بنجاح.');
    }

    #[OA\Get(path: "/cities", tags: ["Locations"])]
    public function getCities(Request $request)
    {
        $query = City::with('governorate');

        if ($request->has('governorate_id') && $request->governorate_id) {
            $query->where('governorate_id', $request->governorate_id);
        }

        $cities = $query->orderBy('name', 'asc')->get();

        return ResponseHelper::success([
            'cities' => $cities
        ], 'تم جلب المدن بنجاح.');
    }
}

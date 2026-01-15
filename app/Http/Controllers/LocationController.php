<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LocationController extends Controller
{
    #[OA\Get(
        path: "/governorates",
        summary: "Get all governorates",
        description: "Retrieve all governorates with their cities (Public endpoint - no authentication required)",
        tags: ["Locations"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Governorates retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "body", type: "string", example: "Governorates retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getGovernorates(Request $request)
    {
        $governorates = Governorate::with('cities')
            ->orderBy('name', 'asc')
            ->get();

        return ResponseHelper::success([
            'governorates' => $governorates
        ], 'تم جلب المحافظات بنجاح.');
    }

    #[OA\Get(
        path: "/cities",
        summary: "Get all cities",
        description: "Retrieve all cities with optional governorate filter (Public endpoint - no authentication required)",
        tags: ["Locations"],
        parameters: [
            new OA\Parameter(name: "governorate_id", in: "query", description: "Filter by governorate ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cities retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "body", type: "string", example: "Cities retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getCities(Request $request)
    {
        $query = City::with('governorate');

        // Filter by governorate if provided
        if ($request->has('governorate_id') && $request->governorate_id) {
            $query->where('governorate_id', $request->governorate_id);
        }

        $cities = $query->orderBy('name', 'asc')->get();

        return ResponseHelper::success([
            'cities' => $cities
        ], 'تم جلب المدن بنجاح.');
    }

    // #[OA\Get(
    //     path: "/governorates/{id}/cities",
    //     summary: "Get cities by governorate",
    //     description: "Retrieve all cities for a specific governorate (Public endpoint - no authentication required)",
    //     tags: ["Locations"],
    //     parameters: [
    //         new OA\Parameter(name: "id", in: "path", required: true, description: "Governorate ID", schema: new OA\Schema(type: "integer")),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: "Cities retrieved successfully",
    //             content: new OA\JsonContent(
    //                 properties: [
    //                     new OA\Property(property: "message", type: "string", example: "success"),
    //                     new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
    //                     new OA\Property(property: "body", type: "string", example: "Cities retrieved successfully.")
    //                 ]
    //             )
    //         ),
    //     ]
    // )]
    // public function getCitiesByGovernorate(Request $request, $governorateId)
    // {
    //     $cities = City::where('governorate_id', $governorateId)
    //         ->with('governorate')
    //         ->orderBy('name', 'asc')
    //         ->get();

    //     return ResponseHelper::success([
    //         'cities' => $cities
    //     ], 'Cities retrieved successfully.');
    // }

    // #[OA\Get(
    //     path: "/governorates/{id}",
    //     summary: "Get governorate by ID",
    //     description: "Retrieve a specific governorate with its cities (Public endpoint - no authentication required)",
    //     tags: ["Locations"],
    //     parameters: [
    //         new OA\Parameter(name: "id", in: "path", required: true, description: "Governorate ID", schema: new OA\Schema(type: "integer")),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: "Governorate retrieved successfully",
    //             content: new OA\JsonContent(
    //                 properties: [
    //                     new OA\Property(property: "message", type: "string", example: "success"),
    //                     new OA\Property(property: "data", type: "object"),
    //                     new OA\Property(property: "body", type: "string", example: "Governorate retrieved successfully.")
    //                 ]
    //             )
    //         ),
    //         new OA\Response(response: 404, description: "Governorate not found"),
    //     ]
    // )]
    // public function getGovernorate(Request $request, $id)
    // {
    //     $governorate = Governorate::with('cities')
    //         ->findOrFail($id);

    //     return ResponseHelper::success([
    //         'governorate' => $governorate
    //     ], 'Governorate retrieved successfully.');
    // }

    // #[OA\Get(
    //     path: "/cities/{id}",
    //     summary: "Get city by ID",
    //     description: "Retrieve a specific city with its governorate (Public endpoint - no authentication required)",
    //     tags: ["Locations"],
    //     parameters: [
    //         new OA\Parameter(name: "id", in: "path", required: true, description: "City ID", schema: new OA\Schema(type: "integer")),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: "City retrieved successfully",
    //             content: new OA\JsonContent(
    //                 properties: [
    //                     new OA\Property(property: "message", type: "string", example: "success"),
    //                     new OA\Property(property: "data", type: "object"),
    //                     new OA\Property(property: "body", type: "string", example: "City retrieved successfully.")
    //                 ]
    //             )
    //         ),
    //         new OA\Response(response: 404, description: "City not found"),
    //     ]
    // )]
    // public function getCity(Request $request, $id)
    // {
    //     $city = City::with('governorate')
    //         ->findOrFail($id);

    //     return ResponseHelper::success($city, 'City retrieved successfully.');
    // }
}

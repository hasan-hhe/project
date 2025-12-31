<?php

namespace App\Http\Controllers\Admin;

use App\Models\Apartment;
use App\Models\User;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class ApartmentController extends Controller
{
    /**
     * Display a listing of apartments.
     */
    public function index(Request $request)
    {
        $query = Apartment::with('owner')->orderBy('id', 'DESC');

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by owner
        if ($request->has('owner_id') && !empty($request->owner_id)) {
            $query->where('owner_id', $request->owner_id);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('address_line', 'like', "%{$search}%");
            });
        }

        $apartments = $query->paginate(defined('paginateNumber') ? constant('paginateNumber') : 10)
            ->withQueryString();

        $owners = User::where('account_type', 'OWNER')->get();

        return view('admin.apartments.index', compact('apartments', 'owners'));
    }

    /**
     * Show the form for creating a new apartment.
     */
    public function create()
    {
        $owners = User::where('account_type', 'OWNER')
            ->where('status', 'APPROVED')
            ->get();
        $governorates = Governorate::all();
        $cities = City::all();

        return view('admin.apartments.create', compact('owners', 'governorates', 'cities'));
    }

    /**
     * Store a newly created apartment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'rooms_count' => 'required|integer|min:1',
            'address_line' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $apartment = Apartment::create([
                'owner_id' => $request->owner_id,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'rooms_count' => $request->rooms_count,
                'address_line' => $request->address_line,
                'rating_avg' => 5.0,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'is_favorite' => false,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.apartments.index')
                ->with('success', __('تمت إضافة الشقة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء إضافة الشقة: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified apartment.
     */
    public function show(Apartment $apartment)
    {
        $apartment->load(['owner', 'bookings.renter', 'reviews.user', 'photos']);

        return view('admin.apartments.show', compact('apartment'));
    }

    /**
     * Show the form for editing the specified apartment.
     */
    public function edit(Apartment $apartment)
    {
        $owners = User::where('account_type', 'OWNER')
            ->where('status', 'APPROVED')
            ->get();
        $governorates = Governorate::all();
        $cities = City::all();

        return view('admin.apartments.edit', compact('apartment', 'owners', 'governorates', 'cities'));
    }

    /**
     * Update the specified apartment in storage.
     */
    public function update(Request $request, Apartment $apartment)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'rooms_count' => 'required|integer|min:1',
            'address_line' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $apartment->update([
                'owner_id' => $request->owner_id,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'rooms_count' => $request->rooms_count,
                'address_line' => $request->address_line,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم تحديث الشقة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء تحديث الشقة: ') . $e->getMessage());
        }
    }

    /**
     * Remove the specified apartment from storage.
     */
    public function destroy(Apartment $apartment)
    {
        try {
            // التحقق من وجود حجوزات
            $bookingsCount = $apartment->bookings()->count();
            if ($bookingsCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', __('لا يمكن حذف الشقة لأنها لديها ' . $bookingsCount . ' حجز'));
            }

            DB::beginTransaction();
            $apartment->delete();
            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف الشقة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف الشقة: ') . $e->getMessage());
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Apartment $apartment)
    {
        try {
            $apartment->is_active = !$apartment->is_active;
            $apartment->save();

            return redirect()
                ->back()
                ->with('success', __('تم تغيير حالة الشقة'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء تغيير الحالة'));
        }
    }

    /**
     * Delete multiple apartments.
     */
    public function destroyCheck(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:apartments,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->input('items') as $apartmentId) {
                $apartment = Apartment::find($apartmentId);

                if (!$apartment) {
                    continue;
                }

                // التحقق من وجود حجوزات
                $bookingsCount = $apartment->bookings()->count();
                if ($bookingsCount > 0) {
                    DB::rollBack();
                    return redirect()
                        ->back()
                        ->with('error', __('لا يمكن حذف الشقة "' . $apartment->title . '" لأنها لديها ' . $bookingsCount . ' حجز'));
                }

                $apartment->delete();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف الشقق المحددة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف الشقق: ') . $e->getMessage());
        }
    }
}

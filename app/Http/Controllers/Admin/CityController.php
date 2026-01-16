<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CityController extends Controller
{
    /**
     * Display a listing of cities.
     */
    public function index(Request $request)
    {
        $paginateNumber = defined('paginateNumber') ? constant('paginateNumber') : 10;

        $query = City::with('governorate')->orderBy('id', 'DESC');

        // Filter by governorate
        if ($request->has('governorate_id') && !empty($request->governorate_id)) {
            $query->where('governorate_id', $request->governorate_id);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('governorate', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $cities = $query->paginate($paginateNumber)->withQueryString();
        $governorates = Governorate::orderBy('name', 'ASC')->get();

        return view('admin.cities.index', compact('cities', 'governorates'));
    }

    /**
     * Show the form for creating a new city.
     */
    public function create()
    {
        $governorates = Governorate::orderBy('name', 'ASC')->get();
        return view('admin.cities.create', compact('governorates'));
    }

    /**
     * Store a newly created city in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id',
        ], [
            'name.required' => 'اسم المدينة مطلوب',
            'governorate_id.required' => 'المحافظة مطلوبة',
            'governorate_id.exists' => 'المحافظة المحددة غير موجودة',
        ]);

        try {
            DB::beginTransaction();

            City::create([
                'name' => $request->name,
                'governorate_id' => $request->governorate_id,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.cities.index')
                ->with('success', __('تمت إضافة المدينة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء إضافة المدينة: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified city.
     */
    public function show(City $city)
    {
        $city->load('governorate', 'apartments');
        return view('admin.cities.show', compact('city'));
    }

    /**
     * Show the form for editing the specified city.
     */
    public function edit(City $city)
    {
        $governorates = Governorate::orderBy('name', 'ASC')->get();
        return view('admin.cities.edit', compact('city', 'governorates'));
    }

    /**
     * Update the specified city in storage.
     */
    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id',
        ], [
            'name.required' => 'اسم المدينة مطلوب',
            'governorate_id.required' => 'المحافظة مطلوبة',
            'governorate_id.exists' => 'المحافظة المحددة غير موجودة',
        ]);

        try {
            DB::beginTransaction();

            $city->update([
                'name' => $request->name,
                'governorate_id' => $request->governorate_id,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.cities.index')
                ->with('success', __('تم تحديث المدينة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء تحديث المدينة: ') . $e->getMessage());
        }
    }

    /**
     * Remove the specified city from storage.
     */
    public function destroy(City $city)
    {
        try {
            $apartmentsCount = $city->apartments()->count();
            if ($apartmentsCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', __('لا يمكن حذف المدينة لأنها تحتوي على ' . $apartmentsCount . ' شقة'));
            }

            DB::beginTransaction();
            $city->delete();
            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف المدينة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف المدينة: ') . $e->getMessage());
        }
    }

    /**
     * Delete multiple cities.
     */
    public function destroyCheck(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:cities,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->input('items') as $cityId) {
                $city = City::find($cityId);

                if (!$city) {
                    continue;
                }

                if ($city->apartments()->count() > 0) {
                    DB::rollBack();
                    return redirect()
                        ->back()
                        ->with('error', __('لا يمكن حذف ' . $city->name . ' لأنها تحتوي على شقق'));
                }

                $city->delete();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف المدن المحددة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف المدن: ') . $e->getMessage());
        }
    }
}


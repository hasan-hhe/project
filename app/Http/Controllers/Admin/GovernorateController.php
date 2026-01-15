<?php

namespace App\Http\Controllers\Admin;

use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class GovernorateController extends Controller
{
    /**
     * Display a listing of governorates.
     */
    public function index(Request $request)
    {
        $paginateNumber = defined('paginateNumber') ? constant('paginateNumber') : 10;

        $query = Governorate::withCount('cities')->orderBy('id', 'DESC');

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $governorates = $query->paginate($paginateNumber)->withQueryString();

        return view('admin.governorates.index', compact('governorates'));
    }

    /**
     * Show the form for creating a new governorate.
     */
    public function create()
    {
        return view('admin.governorates.create');
    }

    /**
     * Store a newly created governorate in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:governorates,name',
        ], [
            'name.required' => 'اسم المحافظة مطلوب',
            'name.unique' => 'هذه المحافظة موجودة بالفعل',
        ]);

        try {
            DB::beginTransaction();

            Governorate::create([
                'name' => $request->name,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.governorates.index')
                ->with('success', __('تمت إضافة المحافظة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء إضافة المحافظة: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified governorate.
     */
    public function show(Governorate $governorate)
    {
        $governorate->load('cities');
        return view('admin.governorates.show', compact('governorate'));
    }

    /**
     * Show the form for editing the specified governorate.
     */
    public function edit(Governorate $governorate)
    {
        return view('admin.governorates.edit', compact('governorate'));
    }

    /**
     * Update the specified governorate in storage.
     */
    public function update(Request $request, Governorate $governorate)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:governorates,name,' . $governorate->id,
        ], [
            'name.required' => 'اسم المحافظة مطلوب',
            'name.unique' => 'هذه المحافظة موجودة بالفعل',
        ]);

        try {
            DB::beginTransaction();

            $governorate->update([
                'name' => $request->name,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.governorates.index')
                ->with('success', __('تم تحديث المحافظة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء تحديث المحافظة: ') . $e->getMessage());
        }
    }

    /**
     * Remove the specified governorate from storage.
     */
    public function destroy(Governorate $governorate)
    {
        try {
            // التحقق من وجود مدن مرتبطة
            $citiesCount = $governorate->cities()->count();
            if ($citiesCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', __('لا يمكن حذف المحافظة لأنها تحتوي على ' . $citiesCount . ' مدينة'));
            }

            DB::beginTransaction();
            $governorate->delete();
            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف المحافظة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف المحافظة: ') . $e->getMessage());
        }
    }

    /**
     * Delete multiple governorates.
     */
    public function destroyCheck(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:governorates,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->input('items') as $governorateId) {
                $governorate = Governorate::find($governorateId);

                if (!$governorate) {
                    continue;
                }

                // التحقق من وجود مدن مرتبطة
                if ($governorate->cities()->count() > 0) {
                    DB::rollBack();
                    return redirect()
                        ->back()
                        ->with('error', __('لا يمكن حذف ' . $governorate->name . ' لأنها تحتوي على مدن'));
                }

                $governorate->delete();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف المحافظات المحددة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف المحافظات: ') . $e->getMessage());
        }
    }
}


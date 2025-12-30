<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ApartmentOwnerController extends Controller
{
    /**
     * Display a listing of apartment owners.
     */
    public function index(Request $request)
    {
        $query = User::where('account_type', 'OWNER')
            ->withCount('appartments');

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Search by name, phone, or email
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $owners = $query->orderBy('id', 'DESC')
            ->paginate(defined('paginateNumber') ? paginateNumber : 10)
            ->withQueryString();
        
        return view('admin.apartment-owners.index', compact('owners'));
    }

    /**
     * Display the specified apartment owner.
     */
    public function show(User $owner)
    {
        if ($owner->account_type != 'OWNER') {
            return redirect()->back()->with('error', __('هذا المستخدم ليس صاحب شقة'));
        }

        $apartments = $owner->appartments()->get();
        
        return view('admin.apartment-owners.show', compact('owner', 'apartments'));
    }

    /**
     * Update the owner status.
     */
    public function updateStatus(Request $request, User $owner)
    {
        $request->validate([
            'status' => 'required|in:PENDING,APPROVED,REJECTED',
        ]);

        try {
            $owner->update([
                'status' => $request->status,
            ]);

            $statusLabels = [
                'PENDING' => 'قيد الانتظار',
                'APPROVED' => 'موافق عليه',
                'REJECTED' => 'مرفوض',
            ];

            return redirect()->back()->with('success', __('تم تغيير حالة الحساب إلى: ' . $statusLabels[$request->status]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('حدث خطأ أثناء تحديث الحالة'));
        }
    }
}

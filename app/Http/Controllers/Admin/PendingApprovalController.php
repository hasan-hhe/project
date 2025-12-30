<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendingApprovalController extends Controller
{
    /**
     * Display a listing of users pending approval.
     */
    public function index(Request $request)
    {
        $query = User::where('status', 'PENDING')
            ->where('account_type', 'OWNER')
            ->orderBy('created_at', 'DESC');

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(defined('paginateNumber') ? paginateNumber : 10)
            ->withQueryString();

        return view('admin.pending-approvals.index', compact('users'));
    }

    /**
     * Display the specified user pending approval.
     */
    public function show(User $user)
    {
        if ($user->status != 'PENDING' || $user->account_type != 'OWNER') {
            return redirect()->back()->with('error', __('هذا المستخدم لا يحتاج إلى موافقة'));
        }

        return view('admin.pending-approvals.show', compact('user'));
    }

    /**
     * Approve a user.
     */
    public function approve(Request $request, User $user)
    {
        if ($user->status != 'PENDING') {
            return redirect()->back()->with('error', __('هذا المستخدم لا يحتاج إلى موافقة'));
        }

        try {
            $user->update([
                'status' => 'APPROVED',
            ]);

            return redirect()
                ->route('admin.pending-approvals.index')
                ->with('success', __('تم الموافقة على حساب المستخدم بنجاح'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء الموافقة: ') . $e->getMessage());
        }
    }

    /**
     * Reject a user.
     */
    public function reject(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($user->status != 'PENDING') {
            return redirect()->back()->with('error', __('هذا المستخدم لا يحتاج إلى موافقة'));
        }

        try {
            $user->update([
                'status' => 'REJECTED',
            ]);

            return redirect()
                ->route('admin.pending-approvals.index')
                ->with('success', __('تم رفض حساب المستخدم'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء الرفض: ') . $e->getMessage());
        }
    }

    /**
     * Approve multiple users.
     */
    public function approveMultiple(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($request->input('users') as $userId) {
                $user = User::find($userId);
                if ($user && $user->status == 'PENDING' && $user->account_type == 'OWNER') {
                    $user->update(['status' => 'APPROVED']);
                    $count++;
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم الموافقة على ' . $count . ' حساب بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء الموافقة: ') . $e->getMessage());
        }
    }
}

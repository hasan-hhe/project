<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Display wallet recharge page.
     */
    public function index(Request $request)
    {
        $query = User::where('account_type', 'RENTER')
            ->orderBy('id', 'DESC');

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

        return view('admin.wallet.index', compact('users'));
    }

    /**
     * Show wallet recharge form for a specific user.
     */
    public function show(User $user)
    {
        if ($user->account_type != 'RENTER') {
            return redirect()->back()->with('error', __('هذا المستخدم ليس مستأجر'));
        }

        return view('admin.wallet.show', compact('user'));
    }

    /**
     * Recharge user wallet.
     */
    public function recharge(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ]);

        if ($user->account_type != 'RENTER') {
            return redirect()->back()->with('error', __('هذا المستخدم ليس مستأجر'));
        }

        try {
            DB::beginTransaction();

            $oldBalance = $user->wallet_balance ?? 0;
            $newBalance = $oldBalance + $request->amount;

            $user->update([
                'wallet_balance' => $newBalance,
            ]);

            // يمكنك إضافة سجل للمعاملة هنا إذا كان لديك جدول transactions

            DB::commit();

            return redirect()
                ->route('admin.wallet.index')
                ->with('success', __('تم شحن محفظة المستأجر "' . $user->first_name . ' ' . $user->last_name . '" بمبلغ ' . number_format($request->amount, 2) . ' SYP. الرصيد الجديد: ' . number_format($newBalance, 2) . ' SYP'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء شحن المحفظة: ') . $e->getMessage());
        }
    }
}

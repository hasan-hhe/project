<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $type = request('type', 'renter');
        $paginateNumber = defined('paginateNumber') ? paginateNumber : 10;

        $query = User::orderBy('id', 'DESC');

        if ($type == 'owner') {
            $users = $query->where('account_type', 'OWNER')
                ->paginate($paginateNumber);
            return view('admin.users.owners', compact('users'));
        } else if ($type == 'admin') {
            $users = $query->where('account_type', 'ADMIN')
                ->paginate($paginateNumber);
            return view('admin.users.admins', compact('users'));
        }

        // Default: RENTER
        $users = $query->where('account_type', 'RENTER')
            ->paginate($paginateNumber);
        return view('admin.users.renters', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $accountTypes = [
            'RENTER' => 'مستأجر',
            'OWNER' => 'صاحب شقة',
            'ADMIN' => 'مدير',
        ];

        $ownerStatuses = [
            'PENDING' => 'قيد الانتظار',
            'APPROVED' => 'موافق عليه',
            'REJECTED' => 'مرفوض',
        ];

        return view('admin.users.create', compact('accountTypes', 'ownerStatuses'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $avatarUrl = null;
            if ($request->hasFile('avatar_image')) {
                $avatarUrl = $request->file('avatar_image')->store('images/users/avatars', 'public');
            }

            $identityDocumentUrl = null;
            if ($request->hasFile('identity_document_image')) {
                $identityDocumentUrl = $request->file('identity_document_image')->store('images/users/identity', 'public');
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'date_of_birth' => $request->date_of_birth,
                'account_type' => $request->account_type,
                'password' => Hash::make($request->password),
                'avatar_url' => $avatarUrl,
                'identity_docomunt_url' => $identityDocumentUrl,
                'owner_status' => $request->account_type == 'OWNER'
                    ? ($request->owner_status ?? 'PENDING')
                    : null,
            ]);

            DB::commit();

            $typeMap = [
                'RENTER' => 'renter',
                'OWNER' => 'owner',
                'ADMIN' => 'admin',
            ];

            return redirect()
                ->route('admin.users.index', ['type' => $typeMap[$user->account_type]])
                ->with('success', __('تمت إضافة المستخدم بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء إضافة المستخدم: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $apartments = [];
        $bookings = [];

        if ($user->account_type == 'OWNER') {
            $apartments = $user->appartments()->with('bookings')->get();
        } elseif ($user->account_type == 'RENTER') {
            $bookings = $user->bookings()->with('apartment')->get();
        }

        return view('admin.users.show', compact('user', 'apartments', 'bookings'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $accountTypes = [
            'RENTER' => 'مستأجر',
            'OWNER' => 'صاحب شقة',
            'ADMIN' => 'مدير',
        ];

        $ownerStatuses = [
            'PENDING' => 'قيد الانتظار',
            'APPROVED' => 'موافق عليه',
            'REJECTED' => 'مرفوض',
        ];

        return view('admin.users.edit', compact('user', 'accountTypes', 'ownerStatuses'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'date_of_birth' => $request->date_of_birth,
                'account_type' => $request->account_type,
            ];

            if ($request->hasFile('avatar_image')) {
                // حذف الصورة القديمة إن وجدت
                if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                    Storage::disk('public')->delete($user->avatar_url);
                }
                $updateData['avatar_url'] = $request->file('avatar_image')->store('images/users/avatars', 'public');
            }

            if ($request->hasFile('identity_document_image')) {
                // حذف الصورة القديمة إن وجدت
                if ($user->identity_docomunt_url && Storage::disk('public')->exists($user->identity_docomunt_url)) {
                    Storage::disk('public')->delete($user->identity_docomunt_url);
                }
                $updateData['identity_docomunt_url'] = $request->file('identity_document_image')->store('images/users/identity', 'public');
            }

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            if ($request->account_type == 'OWNER') {
                $updateData['owner_status'] = $request->owner_status ?? 'PENDING';
            } else {
                $updateData['owner_status'] = null;
            }

            $user->update($updateData);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم تحديث المستخدم بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء تحديث المستخدم: ') . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            // التحقق من وجود حجوزات أو شقق مرتبطة
            if ($user->account_type == 'OWNER') {
                $apartmentsCount = $user->appartments()->count();
                if ($apartmentsCount > 0) {
                    return redirect()
                        ->back()
                        ->with('error', __('لا يمكن حذف صاحب الشقة لأنه يملك ' . $apartmentsCount . ' شقة'));
                }
            }

            if ($user->account_type == 'RENTER') {
                $bookingsCount = $user->bookings()->count();
                if ($bookingsCount > 0) {
                    return redirect()
                        ->back()
                        ->with('error', __('لا يمكن حذف المستأجر لأنه لديه ' . $bookingsCount . ' حجز'));
                }
            }

            DB::beginTransaction();
            $user->delete();
            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف المستخدم بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف المستخدم: ') . $e->getMessage());
        }
    }

    /**
     * Delete multiple users.
     */
    public function destroyCheck(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->input('items') as $userId) {
                $user = User::find($userId);

                if (!$user) {
                    continue;
                }

                // التحقق من وجود حجوزات أو شقق مرتبطة
                if ($user->account_type == 'OWNER' && $user->appartments()->count() > 0) {
                    DB::rollBack();
                    return redirect()
                        ->back()
                        ->with('error', __('لا يمكن حذف ' . $user->first_name . ' لأنه يملك شقق'));
                }

                if ($user->account_type == 'RENTER' && $user->bookings()->count() > 0) {
                    DB::rollBack();
                    return redirect()
                        ->back()
                        ->with('error', __('لا يمكن حذف ' . $user->first_name . ' لأنه لديه حجوزات'));
                }

                $user->delete();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف المستخدمين المحددين بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف المستخدمين: ') . $e->getMessage());
        }
    }

    /**
     * Search users.
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');
        $accountType = $request->get('account_type', '');

        $users = User::query()
            ->when($accountType, function ($q) use ($accountType) {
                $q->where('account_type', $accountType);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->limit(20)
            ->get();

        return response()->json(
            $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'text' => "{$user->first_name} {$user->last_name} ({$user->phone_number})",
                ];
            })
        );
    }
}

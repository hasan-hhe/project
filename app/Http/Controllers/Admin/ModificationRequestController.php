<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingChange;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModificationRequestController extends Controller
{
    /**
     * Display a listing of modification requests.
     */
    public function index(Request $request)
    {
        $query = BookingChange::with(['requestedBy', 'reviewer', 'booking'])
            ->orderBy('created_at', 'DESC');

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }


        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('requestedBy', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(defined('paginateNumber') ? paginateNumber : 10)
            ->withQueryString();

        return view('admin.modification-requests.index', compact('requests'));
    }

    /**
     * Display the specified modification request.
     */
    public function show(BookingChange $modificationRequest)
    {
        $modificationRequest->load(['requestedBy', 'reviewer', 'booking']);
        
        return view('admin.modification-requests.show', compact('modificationRequest'));
    }

    /**
     * Approve a modification request.
     */
    public function approve(Request $request, BookingChange $modificationRequest)
    {
        $request->validate([
            'admin_comment' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Apply the modification based on request type
            $this->applyModification($modificationRequest);

            $modificationRequest->update([
                'status' => 'APPROVED',
                'admin_comment' => $request->admin_comment,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم الموافقة على طلب التعديل بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء الموافقة على الطلب: ') . $e->getMessage());
        }
    }

    /**
     * Reject a modification request.
     */
    public function reject(Request $request, BookingChange $modificationRequest)
    {
        $request->validate([
            'admin_comment' => 'required|string|max:1000',
        ]);

        try {
            $modificationRequest->update([
                'status' => 'REJECTED',
                'admin_comment' => $request->admin_comment,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return redirect()
                ->back()
                ->with('success', __('تم رفض طلب التعديل'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء رفض الطلب: ') . $e->getMessage());
        }
    }

    /**
     * Apply the modification to the booking.
     */
    private function applyModification(BookingChange $modificationRequest)
    {
        if ($modificationRequest->booking_id) {
            $booking = \App\Models\Booking::find($modificationRequest->booking_id);
            if ($booking) {
                if ($modificationRequest->new_start_date) {
                    $booking->start_date = $modificationRequest->new_start_date;
                }
                if ($modificationRequest->new_end_date) {
                    $booking->end_date = $modificationRequest->new_end_date;
                }
                $booking->save();
            }
        }
    }

    /**
     * Remove the specified modification request.
     */
    public function destroy(BookingChange $modificationRequest)
    {
        try {
            $modificationRequest->delete();

            return redirect()
                ->route('admin.modification-requests.index')
                ->with('success', __('تم حذف طلب التعديل بنجاح'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف الطلب: ') . $e->getMessage());
        }
    }
}

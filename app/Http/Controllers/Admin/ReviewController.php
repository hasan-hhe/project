<?php

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'apartment'])->orderBy('id', 'DESC');

        // Filter by apartment
        if ($request->has('apartment_id') && !empty($request->apartment_id)) {
            $query->where('apartment_id', $request->apartment_id);
        }

        // Filter by user
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by rating
        if ($request->has('rating') && !empty($request->rating)) {
            $query->where('rating', $request->rating);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('apartment', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $reviews = $query->paginate(defined('paginateNumber') ? paginateNumber : 10)
            ->withQueryString();

        $apartments = Apartment::all();
        $users = User::where('account_type', 'RENTER')->get();

        return view('admin.reviews.index', compact('reviews', 'apartments', 'users'));
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        $review->load(['user', 'apartment', 'booking']);

        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        try {
            DB::beginTransaction();
            
            // Update apartment rating average
            $apartment = $review->apartment;
            $reviewsCount = $apartment->reviews()->count();
            
            if ($reviewsCount > 1) {
                $avgRating = $apartment->reviews()
                    ->where('id', '!=', $review->id)
                    ->avg('rating');
                $apartment->rating_avg = round($avgRating, 2);
                $apartment->save();
            } else {
                $apartment->rating_avg = null;
                $apartment->save();
            }
            
            $review->delete();
            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف التقييم بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف التقييم: ') . $e->getMessage());
        }
    }

    /**
     * Delete multiple reviews.
     */
    public function destroyCheck(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:reviews,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->input('items') as $reviewId) {
                $review = Review::find($reviewId);

                if (!$review) {
                    continue;
                }

                // Update apartment rating average
                $apartment = $review->apartment;
                $reviewsCount = $apartment->reviews()->count();
                
                if ($reviewsCount > 1) {
                    $avgRating = $apartment->reviews()
                        ->where('id', '!=', $review->id)
                        ->avg('rating');
                    $apartment->rating_avg = round($avgRating, 2);
                    $apartment->save();
                } else {
                    $apartment->rating_avg = null;
                    $apartment->save();
                }

                $review->delete();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف التقييمات المحددة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف التقييمات: ') . $e->getMessage());
        }
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Models\Apartment;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

use function App\Helpers\uploadImage;

class ApartmentPhotoController extends Controller
{
    /**
     * Display photos for a specific apartment.
     */
    public function index(Apartment $apartment)
    {
        $apartment->load('photos');
        $photos = $apartment->photos()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.apartments.photos.index', compact('apartment', 'photos'));
    }

    /**
     * Store a newly uploaded photo.
     */
    public function store(Request $request, Apartment $apartment)
    {
        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            // 'is_cover' => 'nullable|exists:photos,id',
        ]);


        try {
            DB::beginTransaction();

            $uploadedPhotos = [];
            $maxSortOrder = $apartment->photos()->max('sort_order') ?? 0;

            foreach ($request->file('photos') as $index => $photo) {
                // Upload photo
                $url = uploadImage($photo, 'apartments/photos', 'public');

                // Check if this should be cover photo
                $isCover = false;
                if ($request->has('is_cover') && $request->is_cover == 'new_' . $index) {
                    // Remove old cover
                    $apartment->photos()->update(['is_cover' => false]);
                    $isCover = true;
                } elseif ($apartment->photos()->where('is_cover', true)->count() == 0 && $index == 0) {
                    // If no cover exists, make first photo cover
                    $isCover = true;
                }

                $uploadedPhoto = Photo::create([
                    'apartment_id' => $apartment->id,
                    'url' => $url,
                    'is_cover' => $isCover,
                    'sort_order' => $maxSortOrder + $index + 1,
                ]);

                $uploadedPhotos[] = $uploadedPhoto;
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم رفع الصور بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('حدث خطأ أثناء رفع الصور: ') . $e->getMessage());
        }
    }

    /**
     * Update photo (set as cover or update sort order).
     */
    public function update(Request $request, Apartment $apartment, Photo $photo)
    {
        $request->validate([
            'is_cover' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            if ($request->has('is_cover') && $request->is_cover) {
                // Remove old cover
                $apartment->photos()->where('id', '!=', $photo->id)->update(['is_cover' => false]);
                $photo->is_cover = true;
            }

            if ($request->has('sort_order')) {
                $photo->sort_order = $request->sort_order;
            }

            $photo->save();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم تحديث الصورة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء تحديث الصورة: ') . $e->getMessage());
        }
    }

    /**
     * Set photo as cover.
     */
    public function setCover(Apartment $apartment, Photo $photo)
    {
        try {
            DB::beginTransaction();

            // Remove old cover
            $apartment->photos()->where('id', '!=', $photo->id)->update(['is_cover' => false]);

            // Set new cover
            $photo->is_cover = true;
            $photo->save();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم تعيين الصورة كصورة غلاف بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء تعيين صورة الغلاف'));
        }
    }

    /**
     * Remove the specified photo.
     */
    public function destroy(Apartment $apartment, Photo $photo)
    {
        try {
            DB::beginTransaction();

            // Delete file from storage
            $path = str_replace('/storage/', '', $photo->url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            $wasCover = $photo->is_cover;
            $photo->delete();

            // If deleted photo was cover, set first photo as cover
            if ($wasCover) {
                $firstPhoto = $apartment->photos()->first();
                if ($firstPhoto) {
                    $firstPhoto->is_cover = true;
                    $firstPhoto->save();
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف الصورة بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف الصورة: ') . $e->getMessage());
        }
    }
}

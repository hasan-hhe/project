<?php

namespace App\Helpers;

use Ably\AblyRest;
use App\Mail\VerificationEmail;
use App\Models\CardRecharge;
use App\Models\Favorite;
use App\Models\Material;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PriceLog;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserNotification;
use App\Models\Wishlist;
use App\Services\FirebaseService;
use Exception;
use Google\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\Return_;

function uploadImage($image, $nameFolder, $nameDisk)
{
    try {
        // $photoName = uniqid() . "." . $image->extension();
        // $path = $image->storeAs($nameFolder, $photoName, $nameDisk);
        // return '/' . 'images/' . $nameFolder . '/' . $photoName;

        $path = $image->store($nameFolder, $nameDisk);
        $url = Storage::url($path);
        return $url;
    } catch (\Exception $e) {
    }
}

function uploadFile($file, $nameFolder, $nameDisk)
{
    try {
        $fileName = uniqid() . "." . $file->extension();
        $path = $file->storeAs($nameFolder, $fileName, $nameDisk);
        return 'images/' . $path;
    } catch (\Exception $e) {
    }
}

function uploadFilesChunking($file, $nameFolder, $nameDisk, $name, $i)
{
    try {
        $fileName = $name . "-part$i";
        $path = $file->storeAs($nameFolder, $fileName, $nameDisk);
    } catch (\Exception $e) {
    }
}

function uploadImageCrop($image, $nameFolder, $nameDisk)
{
    $image_parts = explode(";base64,", $image);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);
    $imageName = uniqid() . '.png';

    $imageFullPath = 'images/' . $nameFolder . '/' . $imageName;

    file_put_contents($imageFullPath, $image_base64);
    return '/' . $imageFullPath;
}

function inArray($element, $array)
{
    if ($array == null) return false;
    foreach ($array as $e) {
        if ($e == $element) {
            return true;
        }
    }
    return false;
}


function deleteFile($link)
{
    try {
        // unlink(asset($link)); //https
        unlink(public_path($link)); //http
    } catch (Exception $e) {
    }
}

function errorType($type, $isMale = true)
{
    if ($isMale) {
        return 'هذا ' . $type  . ' غير موجود';
    } else {
        return 'هذه ' . $type . ' غير موجودة';
    }
}

function addProperties($items, $title, $properties)
{
    foreach ($items as $item) {
        $arr = [];
        foreach ($properties as $property) {
            $arr += [$property => $item->$property];
            unset($item->$property);
        }
        $item->{$title} = $arr;
    }
    return $items;
}

function toUnique($items)
{
    $items = collect($items)->unique();
    return $items;
}

function toArray($items)
{
    $array = [];
    foreach ($items as $item) {
        array_push($array, $item);
    }
    return $array;
}

function dateDiffInDays($date1, $date2)
{
    $diff = strtotime($date2) - strtotime($date1);
    return abs(round($diff / 86400));
}

function addPagination($items)
{
    $collection = collect($items);
    $page = Request::get('page', 1);
    $perPage = paginateNumber;

    $items = $collection->forPage($page, $perPage);

    $paginated = new LengthAwarePaginator(
        $items,
        $collection->count(),
        $perPage,
        $page,
        ['path' => Request::url(), 'query' => Request::query()]
    );

    return $paginated;
}

function sendNotification($notification, $userId)
{
    try {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        // إرسال الإشعار عبر Laravel Reverb
        broadcast(new \App\Events\NotificationSent($notification, $userId))->toOthers();

        return true;
    } catch (\Exception $e) {
        Log::error('Error sending notification: ' . $e->getMessage());
        return false;
    }
}

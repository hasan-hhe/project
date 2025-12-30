<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = [
        'is_cover',
        'sort_order',
        'url'
    ];
    
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}

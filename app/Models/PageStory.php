<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PageStory extends Model
{
    protected $fillable = [
        'page_id',
        'title',
        'date_or_year',
        'description',
        'image_path',
        'sort_order',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    protected static function booted()
    {
        static::deleting(function (PageStory $story) {
            if ($story->image_path) {
                Storage::disk('public')->delete($story->image_path);
            }
        });
    }
}

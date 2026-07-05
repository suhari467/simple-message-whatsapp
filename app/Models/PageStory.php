<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}

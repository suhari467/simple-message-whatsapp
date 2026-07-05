<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'content',
        'wedding_date',
        'bride_name',
        'bride_parents',
        'bride_image',
        'groom_name',
        'groom_parents',
        'groom_image',
        'akad_time',
        'akad_location',
        'resepsi_time',
        'resepsi_location',
        'google_maps_url',
        'background_music',
    ];

    protected $casts = [
        'wedding_date' => 'date',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function galleries()
    {
        return $this->hasMany(PageGallery::class);
    }

    public function stories()
    {
        return $this->hasMany(PageStory::class)->orderBy('sort_order');
    }

    public function donations()
    {
        return $this->hasMany(PageDonation::class);
    }
}

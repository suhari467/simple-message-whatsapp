<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::deleting(function (Page $page) {
            // Delete direct files
            $filesToDelete = array_filter([
                $page->logo,
                $page->bride_image,
                $page->groom_image,
                $page->background_music,
            ]);

            foreach ($filesToDelete as $file) {
                Storage::disk('public')->delete($file);
            }

            // Delete relations one by one to trigger their deleting events (deleting their files)
            $page->galleries->each->delete();
            $page->stories->each->delete();

            // Delete other relations without files
            $page->donations()->delete();
            $page->messages()->delete();
            $page->recipients()->delete();
        });
    }

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

    public function recipients()
    {
        return $this->hasMany(Recipient::class);
    }
}

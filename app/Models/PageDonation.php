<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageDonation extends Model
{
    protected $fillable = [
        'page_id',
        'bank_name',
        'account_name',
        'account_number',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipient extends Model
{
    protected $fillable = [
        'page_id',
        'name',
        'address',
        'phone_number',
    ];

    /**
     * Get the page that owns the recipient.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
